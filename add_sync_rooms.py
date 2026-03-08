"""
Adds the syncRooms() method to kewirdev LicenseController and route to api.php,
adds syncRooms() to LicenseService, and adds room_count to /info response.
"""
import os

# ──────────────────────────────────────────────────────────────────────────────
# Paths
# ──────────────────────────────────────────────────────────────────────────────
BASE = r'C:\Users\FT_Basil\Documents\kewirdev-website'
CONTROLLER = os.path.join(BASE, 'app', 'Http', 'Controllers', 'Api', 'LicenseController.php')
SERVICE     = os.path.join(BASE, 'app', 'Services', 'LicenseService.php')
ROUTES      = os.path.join(BASE, 'routes', 'api.php')

# ──────────────────────────────────────────────────────────────────────────────
# 1. LicenseController — add syncRooms() method
# ──────────────────────────────────────────────────────────────────────────────
with open(CONTROLLER, 'r', encoding='utf-8') as f:
    ctrl = f.read()

if 'syncRooms' in ctrl:
    print('[CONTROLLER] syncRooms already present — skipping')
else:
    sync_method = (
        '\n\n'
        '    /**\n'
        '     * Sync room count from HMS.\n'
        '     * HMS calls this after every room create/delete to report its live count.\n'
        '     * We validate against the license room limit (max_users = rooms, -1 = unlimited).\n'
        '     */\n'
        '    public function syncRooms(Request $request): JsonResponse\n'
        '    {\n'
        "        $key = 'license-sync-rooms:' . $request->ip();\n"
        '        if (RateLimiter::tooManyAttempts($key, 60)) {\n'
        '            return response()->json([\n'
        "                'success'     => false,\n"
        "                'error'       => 'Too many requests.',\n"
        "                'retry_after' => RateLimiter::availableIn($key)\n"
        '            ], 429);\n'
        '        }\n'
        '        RateLimiter::hit($key, 60);\n'
        '\n'
        '        $validator = Validator::make($request->all(), [\n'
        "            'token'      => 'required|string',\n"
        "            'device_id'  => 'required|string',\n"
        "            'room_count' => 'required|integer|min:0',\n"
        '        ]);\n'
        '\n'
        '        if ($validator->fails()) {\n'
        '            return response()->json([\n'
        "                'success' => false,\n"
        "                'error'   => 'Invalid request data',\n"
        "                'details' => $validator->errors()\n"
        '            ], 400);\n'
        '        }\n'
        '\n'
        '        $tokenValidation = $this->licenseService->validateToken($request->token);\n'
        '\n'
        "        if (!$tokenValidation['valid']) {\n"
        "            return response()->json(['success' => false, 'error' => $tokenValidation['error']], 401);\n"
        '        }\n'
        '\n'
        "        $license = $tokenValidation['license'];\n"
        '        $result  = $this->licenseService->syncRooms(\n'
        '            $license->license_key,\n'
        '            $request->device_id,\n'
        '            (int) $request->room_count\n'
        '        );\n'
        '\n'
        "        $statusCode = $result['success'] ? 200\n"
        "            : (isset($result['allowed']) && $result['allowed'] === false ? 403 : 422);\n"
        '        return response()->json($result, $statusCode);\n'
        '    }\n'
        '}'
    )

    # Strip the final closing brace of the class and append new method
    ctrl = ctrl.rstrip()
    if ctrl.endswith('}'):
        ctrl = ctrl[:-1].rstrip()
    ctrl = ctrl + sync_method + '\n'

    with open(CONTROLLER, 'w', encoding='utf-8') as f:
        f.write(ctrl)
    print('[CONTROLLER] syncRooms() added')

# ──────────────────────────────────────────────────────────────────────────────
# 2. LicenseService — add syncRooms() method
# ──────────────────────────────────────────────────────────────────────────────
with open(SERVICE, 'r', encoding='utf-8') as f:
    svc = f.read()

if 'syncRooms' in svc:
    print('[SERVICE] syncRooms already present — skipping')
else:
    sync_svc = (
        '\n\n'
        '    /**\n'
        '     * Sync room count from an HMS installation.\n'
        '     * HMS reports how many rooms it currently has; we store it in assigned_rooms\n'
        '     * and validate against the license limit (max_users = room limit, -1 = unlimited).\n'
        '     */\n'
        '    public function syncRooms(string $licenseKey, string $deviceId, int $roomCount): array\n'
        '    {\n'
        "        $license = License::where('license_key', $licenseKey)->first();\n"
        '\n'
        '        if (!$license) {\n'
        "            return ['success' => false, 'error' => 'License not found'];\n"
        '        }\n'
        '\n'
        '        if (!$license->isValid()) {\n'
        "            return ['success' => false, 'error' => 'License is not active'];\n"
        '        }\n'
        '\n'
        '        // Room limit is stored in features.max_users (-1 = unlimited)\n'
        '        $features  = $license->getAvailableFeatures();\n'
        "        $roomLimit = (int) ($features['max_users'] ?? -1);\n"
        '\n'
        '        // Enforce limit\n'
        '        if ($roomLimit !== -1 && $roomCount > $roomLimit) {\n'
        '            return [\n'
        "                'success'    => false,\n"
        '                \'error\'      => "Room limit exceeded: license allows {$roomLimit} rooms, HMS reports {$roomCount}.",\n'
        "                'room_count' => $roomCount,\n"
        "                'room_limit' => $roomLimit,\n"
        "                'allowed'    => false,\n"
        '            ];\n'
        '        }\n'
        '\n'
        '        // Persist the live count\n'
        "        $license->update(['assigned_rooms' => $roomCount]);\n"
        '\n'
        '        return [\n'
        "            'success'    => true,\n"
        "            'room_count' => $roomCount,\n"
        "            'room_limit' => $roomLimit,\n"
        "            'allowed'    => true,\n"
        '        ];\n'
        '    }\n'
        '}'
    )

    svc = svc.rstrip()
    if svc.endswith('}'):
        svc = svc[:-1].rstrip()
    svc = svc + sync_svc + '\n'

    with open(SERVICE, 'w', encoding='utf-8') as f:
        f.write(svc)
    print('[SERVICE] syncRooms() added')

# ──────────────────────────────────────────────────────────────────────────────
# 3. routes/api.php — add POST /license/sync-rooms route
# ──────────────────────────────────────────────────────────────────────────────
with open(ROUTES, 'r', encoding='utf-8') as f:
    routes = f.read()

if 'sync-rooms' in routes:
    print('[ROUTES] sync-rooms route already present — skipping')
else:
    old = "    Route::get('/room-assignments', [LicenseController::class, 'getRoomAssignments']);"
    new = (
        "    Route::get('/room-assignments', [LicenseController::class, 'getRoomAssignments']);\n"
        "    Route::post('/sync-rooms', [LicenseController::class, 'syncRooms']);"
    )
    if old in routes:
        routes = routes.replace(old, new)
        with open(ROUTES, 'w', encoding='utf-8') as f:
            f.write(routes)
        print('[ROUTES] sync-rooms route added')
    else:
        print('[ROUTES] WARNING: anchor not found, add manually:')
        print("    Route::post('/sync-rooms', [LicenseController::class, 'syncRooms']);")

# ──────────────────────────────────────────────────────────────────────────────
# 4. LicenseController info() — add room_count and room_limit to response
# ──────────────────────────────────────────────────────────────────────────────
with open(CONTROLLER, 'r', encoding='utf-8') as f:
    ctrl = f.read()

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

if 'room_count' in ctrl:
    print('[CONTROLLER/info] room_count already in /info response — skipping')
elif old_info in ctrl:
    ctrl = ctrl.replace(old_info, new_info)
    with open(CONTROLLER, 'w', encoding='utf-8') as f:
        f.write(ctrl)
    print('[CONTROLLER/info] room_count + room_limit added to /info response')
else:
    print('[CONTROLLER/info] WARNING: anchor not found for /info response patch')

print('\nAll done.')
