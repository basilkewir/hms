<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceCommand;
use App\Models\IptvDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * DeviceEventsController
 *
 * Implements Server-Sent Events (SSE) for instant command delivery to Android TV devices.
 *
 * The Android app opens a long-lived GET /api/android/events connection.
 * When the admin dispatches a command, it's stored in a cache slot keyed by device_id.
 * The SSE loop picks it up within ~1 second and streams it to the device immediately.
 *
 * This works over LAN with NO extra services — just PHP + Laravel's cache (file/database).
 *
 * Flow:
 *   1. Admin dispatches command → DeviceCommand row created + Cache::put("device_cmds:{id}")
 *   2. Android SSE stream loop detects the cache slot → sends event → clears slot
 *   3. Android receives event immediately and executes the command
 *   4. Android still acks via POST /api/android/command-ack (existing flow)
 */
class DeviceEventsController extends Controller
{
    // How long to keep the SSE connection alive (seconds).
    // Client reconnects automatically after this.
    private const STREAM_TTL = 55;

    // Polling interval inside the SSE loop (seconds). Lower = more responsive.
    private const POLL_INTERVAL = 1;

    // Cache key pattern for pending command signals
    public static function cacheKey(string $deviceId): string
    {
        return "device_sse_signal:{$deviceId}";
    }

    /**
     * GET /api/android/events
     *
     * Opens a Server-Sent Events stream for the device.
     * The Android app should reconnect automatically on disconnect.
     */
    public function stream(Request $request)
    {
        $deviceId = $request->query('device_id');
        $token    = $request->query('registration_token');

        if (!$deviceId || !$token) {
            return response()->json(['error' => 'device_id and registration_token required'], 400);
        }

        // Validate device
        $device = IptvDevice::where('device_id', $deviceId)
            ->where('registration_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$device) {
            return response()->json(['error' => 'Unauthorised'], 401);
        }

        $deviceDbId = $device->id;
        $startTime  = time();

        return response()->stream(function () use ($deviceId, $deviceDbId, $startTime) {

            // ── Critical: release session lock immediately so other requests
            //    from the same browser/device aren't blocked while this stream runs.
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }

            // Disable output buffering for immediate streaming
            if (ob_get_level()) ob_end_clean();
            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', false);
            set_time_limit(0); // prevent PHP max_execution_time from killing the stream

            // Send initial connection event
            $this->sendEvent('connected', [
                'message'     => 'HMS event stream connected',
                'device_id'   => $deviceId,
                'server_time' => now()->toIso8601String(),
            ]);

            $heartbeatAt = time() + 20; // send SSE keep-alive every 20s

            while (true) {
                // Break if client disconnected
                if (connection_aborted()) break;

                // Break after STREAM_TTL seconds (client will auto-reconnect)
                if (time() - $startTime >= self::STREAM_TTL) {
                    $this->sendEvent('reconnect', ['message' => 'Refreshing connection']);
                    break;
                }

                // ── Check cache signal ───────────────────────────────────
                $cacheKey = self::cacheKey($deviceId);
                $signal   = Cache::get($cacheKey);

                if ($signal) {
                    Cache::forget($cacheKey);

                    // Fetch all pending commands from DB
                    $cmds = DeviceCommand::where('iptv_device_id', $deviceDbId)
                        ->where('status', 'pending')
                        ->orderBy('created_at')
                        ->get();

                    foreach ($cmds as $cmd) {
                        $cmd->markDelivered();
                        $this->sendEvent('command', [
                            'id'      => $cmd->id,
                            'type'    => $cmd->type,
                            'payload' => $cmd->payload ?? [],
                        ]);
                    }
                }

                // ── SSE keep-alive ping (prevents proxy timeout) ─────────
                if (time() >= $heartbeatAt) {
                    echo ": ping\n\n";
                    flush();
                    $heartbeatAt = time() + 20;
                }

                sleep(self::POLL_INTERVAL);
            }

        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache, no-store',
            'X-Accel-Buffering' => 'no',     // disable nginx buffering
            'Connection'        => 'keep-alive',
        ]);
    }

    /**
     * Called from DeviceCommand creation / dispatchCommand() to signal the SSE stream.
     * Stores a lightweight cache entry — the SSE loop picks it up within 1 second.
     */
    public static function signal(string $deviceId): void
    {
        Cache::put(self::cacheKey($deviceId), true, now()->addMinutes(5));
        Log::debug("SSE signal sent for device {$deviceId}");
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function sendEvent(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: ' . json_encode($data) . "\n\n";
        flush();
    }
}
