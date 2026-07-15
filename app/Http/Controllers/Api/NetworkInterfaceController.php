<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NetworkInterfaceController extends Controller
{
    /**
     * List all available network interfaces on the server.
     * Used by the Settings UI to let admins pick the IPTV streaming NIC.
     */
    public function index(): JsonResponse
    {
        try {
            $interfaces = $this->getNetworkInterfaces();
            $selected = Setting::where('key', 'iptv_network_interface')->value('value') ?? '';

            return response()->json([
                'success' => true,
                'interfaces' => $interfaces,
                'selected' => $selected,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to list network interfaces', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to detect network interfaces: ' . $e->getMessage(),
                'interfaces' => [],
            ], 500);
        }
    }

    /**
     * Validate that a given interface exists and has an IPv4 address.
     * Called when the admin selects an interface in settings.
     */
    public function validateInterface(Request $request): JsonResponse
    {
        $request->validate([
            'interface' => 'required|string|max:32',
        ]);

        $interfaces = $this->getNetworkInterfaces();
        $match = collect($interfaces)->firstWhere('name', $request->interface);

        if (!$match) {
            return response()->json([
                'success' => false,
                'message' => "Interface '{$request->interface}' not found on this server.",
            ], 422);
        }

        if (empty($match['ipv4'])) {
            return response()->json([
                'success' => false,
                'message' => "Interface '{$request->interface}' has no IPv4 address configured.",
            ], 422);
        }

        return response()->json([
            'success' => true,
            'interface' => $match,
            'message' => "Interface '{$request->interface}' is valid (IP: {$match['ipv4']}).",
        ]);
    }

    /**
     * Discover network interfaces using PHP's net_get_interfaces() with
     * fallback to parsing `ip -4 -o addr show` for systems where the
     * PHP function is unavailable.
     */
    private function getNetworkInterfaces(): array
    {
        $interfaces = [];

        // Primary method: PHP built-in
        if (function_exists('net_get_interfaces')) {
            $raw = net_get_interfaces();
            foreach ($raw as $name => $details) {
                // Skip loopback
                if ($name === 'lo') {
                    continue;
                }

                $ipv4 = '';
                if (isset($details['ipv4'][0]['address'])) {
                    $ipv4 = $details['ipv4'][0]['address'];
                }

                $mac = $details['hardware_address'] ?? $details['mac_address'] ?? '';
                $isUp = ($details['flags'] & 1) === 1; // IFF_UP
                $speed = $this->getInterfaceSpeed($name);

                $interfaces[] = [
                    'name' => $name,
                    'ipv4' => $ipv4,
                    'netmask' => $details['ipv4'][0]['netmask'] ?? '',
                    'broadcast' => $details['ipv4'][0]['broadcast'] ?? '',
                    'mac' => $mac,
                    'is_up' => $isUp,
                    'speed' => $speed,
                    'label' => $this->buildLabel($name, $ipv4, $isUp, $speed),
                ];
            }
        } else {
            // Fallback: parse ip command output
            $output = [];
            exec('ip -4 -o addr show 2>/dev/null', $output, $exitCode);
            if ($exitCode === 0) {
                $parsed = [];
                foreach ($output as $line) {
                    // Format: <index> <ifname> <inet> <ip>/<cidr> brd <broadcast>
                    if (preg_match('/^\d+:\s+(\S+)\s+inet\s+(\d+\.\d+\.\d+\.\d+)\/(\d+)\s+brd\s+(\S+)/', $line, $m)) {
                        $name = $m[1];
                        if (!isset($parsed[$name])) {
                            $parsed[$name] = [
                                'name' => $name,
                                'ipv4' => $m[2],
                                'cidr' => (int) $m[3],
                                'netmask' => $this->cidrToNetmask((int) $m[3]),
                                'broadcast' => $m[4],
                            ];
                        }
                    }
                }

                foreach ($parsed as $name => $details) {
                    if ($name === 'lo') {
                        continue;
                    }
                    $mac = $this->getMacAddress($name);
                    $isUp = $this->isInterfaceUp($name);
                    $speed = $this->getInterfaceSpeed($name);

                    $interfaces[] = [
                        'name' => $name,
                        'ipv4' => $details['ipv4'],
                        'netmask' => $details['netmask'],
                        'broadcast' => $details['broadcast'],
                        'mac' => $mac,
                        'is_up' => $isUp,
                        'speed' => $speed,
                        'label' => $this->buildLabel($name, $details['ipv4'], $isUp, $speed),
                    ];
                }
            }
        }

        // Sort: active interfaces first, then by name
        usort($interfaces, function ($a, $b) {
            if ($a['is_up'] !== $b['is_up']) {
                return $b['is_up'] ? 1 : -1;
            }
            return strcasecmp($a['name'], $b['name']);
        });

        return $interfaces;
    }

    private function buildLabel(string $name, string $ipv4, bool $isUp, string $speed): string
    {
        $status = $isUp ? 'Active' : 'Down';
        $ipPart = $ipv4 ? " — {$ipv4}" : ' — No IP';
        $speedPart = $speed ? " ({$speed})" : '';
        return "{$name}{$ipPart} [{$status}]{$speedPart}";
    }

    private function getInterfaceSpeed(string $name): string
    {
        $path = "/sys/class/net/{$name}/speed";
        if (file_exists($path)) {
            $speed = trim(file_get_contents($path));
            if ($speed && $speed !== '-1') {
                return number_format((int) $speed) . ' Mbps';
            }
        }
        return '';
    }

    private function getMacAddress(string $name): string
    {
        $path = "/sys/class/net/{$name}/address";
        if (file_exists($path)) {
            return strtoupper(trim(file_get_contents($path)));
        }
        return '';
    }

    private function isInterfaceUp(string $name): bool
    {
        $path = "/sys/class/net/{$name}/operstate";
        if (file_exists($path)) {
            return trim(file_get_contents($path)) === 'up';
        }
        return false;
    }

    private function cidrToNetmask(int $cidr): string
    {
        $mask = str_repeat('1', $cidr) . str_repeat('0', 32 - $cidr);
        return implode('.', array_map(function ($octet) {
            return bindec($octet);
        }, str_split($mask, 8)));
    }
}
