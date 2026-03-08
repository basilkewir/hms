"""
Clean and rewrite the kewirdev LicenseController syncRooms method correctly.
Also ensure /info has room_count, and LicenseService has a clean syncRooms.
"""
import os, re

BASE = r'C:\Users\FT_Basil\Documents\kewirdev-website'
CONTROLLER = os.path.join(BASE, 'app', 'Http', 'Controllers', 'Api', 'LicenseController.php')
SERVICE     = os.path.join(BASE, 'app', 'Services', 'LicenseService.php')
ROUTES      = os.path.join(BASE, 'routes', 'api.php')

# ─── CLEAN AND REWRITE LicenseController ────────────────────────────────────

with open(CONTROLLER, 'r', encoding='utf-8') as f:
    ctrl = f.read()

# Remove everything from the start of any syncRooms block to the end of file,
# then we'll re-add it cleanly.
# Find the docblock for syncRooms (could be corrupted)
cut_marker = None
for marker in [
    '    /**\n     * Sync room count from HMS',
    '    /**\n     * Sync room count from an HMS',
]:
    pos = ctrl.find(marker)
    if pos != -1:
        cut_marker = pos
        break

if cut_marker:
    ctrl = ctrl[:cut_marker].rstrip()
    print(f'Stripped from position {cut_marker}')
else:
    # Also try to find via corrupted pattern
    pos = ctrl.find('Sync room count from HMS')
    if pos != -1:
        cut_marker = ctrl.rfind('    /**', 0, pos)
        ctrl = ctrl[:cut_marker].rstrip()
        print(f'Stripped corrupted block from position {cut_marker}')
    else:
        ctrl = ctrl.rstrip().rstrip('}').rstrip()
        print('No syncRooms found, stripped final brace')

# Also ensure /info has room_count
old_info = (
    "                'device_usage'    => [\n"
    "                    'current' => $stats['active_devices'],\n"
    "                    'maximum' => $stats['license']->max_devices,\n"
    "                ],\n"
    "                'last_validation' => $stats['last_validation'],"
)
new_info = (
    "                'device_usage'    => [\n"
    "                    'current' => $stats['active_devices'],\n"
    "                    'maximum' => $stats['license']->max_devices,\n"
    "                ],\n"
    "                'room_count'      => $stats['license']->assigned_rooms ?? 0,\n"
    "                'room_limit'      => (int) ($stats['features']['max_users'] ?? -1),\n"
    "                'last_validation' => $stats['last_validation'],"
)
if 'room_count' not in ctrl and old_info in ctrl:
    ctrl = ctrl.replace(old_info, new_info)
    print('[/info] room_count added')
elif 'room_count' in ctrl:
    print('[/info] room_count already present')

# Append the clean syncRooms method + closing brace
sync_method = '\n\n    /**\n     * Sync room count from HMS.\n     * HMS calls this after every room create/delete to report its live count.\n     * We validate against the license room limit (max_users = rooms, -1 = unlimited).\n     */\n    public function syncRooms(Request $request): JsonResponse\n    {\n        $key = \'license-sync-rooms:\' . $request->ip();\n        if (RateLimiter::tooManyAttempts($key, 60)) {\n            return response()->json([\n                \'success\'     => false,\n                \'error\'       => \'Too many requests.\',\n                \'retry_after\' => RateLimiter::availableIn($key)\n            ], 429);\n        }\n        RateLimiter::hit($key, 60);\n\n        $validator = Validator::make($request->all(), [\n            \'token\'      => \'required|string\',\n            \'device_id\'  => \'required|string\',\n            \'room_count\' => \'required|integer|min:0\',\n        ]);\n\n        if ($validator->fails()) {\n            return response()->json([\n                \'success\' => false,\n                \'error\'   => \'Invalid request data\',\n                \'details\' => $validator->errors()\n            ], 400);\n        }\n\n        $tokenValidation = $this->licenseService->validateToken($request->token);\n\n        if (!$tokenValidation[\'valid\']) {\n            return response()->json([\'success\' => false, \'error\' => $tokenValidation[\'error\']], 401);\n        }\n\n        $license = $tokenValidation[\'license\'];\n        $result  = $this->licenseService->syncRooms(\n            $license->license_key,\n            $request->device_id,\n            (int) $request->room_count\n        );\n\n        $statusCode = $result[\'success\'] ? 200\n            : (isset($result[\'allowed\']) && $result[\'allowed\'] === false ? 403 : 422);\n        return response()->json($result, $statusCode);\n    }\n}\n'

ctrl = ctrl + sync_method

with open(CONTROLLER, 'w', encoding='utf-8') as f:
    f.write(ctrl)
print('[CONTROLLER] syncRooms written. Lines:', ctrl.count('\n'))

# ─── CLEAN AND REWRITE LicenseService syncRooms ─────────────────────────────

with open(SERVICE, 'r', encoding='utf-8') as f:
    svc = f.read()

# Remove any existing syncRooms block
for marker in [
    '    /**\n     * Sync room count from an HMS',
    '    /**\n     * Sync room count from HMS',
]:
    pos = svc.find(marker)
    if pos != -1:
        svc = svc[:pos].rstrip()
        print(f'[SERVICE] Stripped from {pos}')
        break

svc = svc.rstrip().rstrip('}').rstrip()

sync_svc = '\n\n    /**\n     * Sync room count from an HMS installation.\n     * HMS reports how many rooms it currently has; we store it in assigned_rooms\n     * and validate against the license limit (max_users = room limit, -1 = unlimited).\n     */\n    public function syncRooms(string $licenseKey, string $deviceId, int $roomCount): array\n    {\n        $license = License::where(\'license_key\', $licenseKey)->first();\n\n        if (!$license) {\n            return [\'success\' => false, \'error\' => \'License not found\'];\n        }\n\n        if (!$license->isValid()) {\n            return [\'success\' => false, \'error\' => \'License is not active\'];\n        }\n\n        // Room limit comes from features.max_users (-1 = unlimited)\n        $features  = $license->getAvailableFeatures();\n        $roomLimit = (int) ($features[\'max_users\'] ?? -1);\n\n        // Enforce limit\n        if ($roomLimit !== -1 && $roomCount > $roomLimit) {\n            return [\n                \'success\'    => false,\n                \'error\'      => "Room limit exceeded: license allows {$roomLimit} rooms, HMS reports {$roomCount}.",\n                \'room_count\' => $roomCount,\n                \'room_limit\' => $roomLimit,\n                \'allowed\'    => false,\n            ];\n        }\n\n        // Persist the live count\n        $license->update([\'assigned_rooms\' => $roomCount]);\n\n        return [\n            \'success\'    => true,\n            \'room_count\' => $roomCount,\n            \'room_limit\' => $roomLimit,\n            \'allowed\'    => true,\n        ];\n    }\n}\n'

svc = svc + sync_svc

with open(SERVICE, 'w', encoding='utf-8') as f:
    f.write(svc)
print('[SERVICE] syncRooms written. Lines:', svc.count('\n'))

# ─── Verify routes ───────────────────────────────────────────────────────────
with open(ROUTES, 'r', encoding='utf-8') as f:
    routes = f.read()
print('[ROUTES] sync-rooms present:', 'sync-rooms' in routes)

print('\nDone.')
