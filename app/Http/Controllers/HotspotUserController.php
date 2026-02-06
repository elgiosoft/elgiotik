<?php

namespace App\Http\Controllers;

use App\Models\HotspotUser;
use App\Models\Router;
use App\Models\BandwidthPlan;
use App\Models\Customer;
use App\Models\UserSession;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Exception;

class HotspotUserController extends Controller
{
    /**
     * Display a listing of hotspot users with filters
     */
    public function index(Request $request)
    {
        $query = HotspotUser::with(['router', 'bandwidthPlan', 'customer', 'createdBy']);

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Status filter
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'active':
                    $query->active();
                    break;
                case 'disabled':
                    $query->disabled();
                    break;
                case 'expired':
                    $query->expired();
                    break;
            }
        }

        // Router filter
        if ($request->filled('router_id')) {
            $query->forRouter($request->router_id);
        }

        // Online status filter
        if ($request->filled('online_status')) {
            if ($request->online_status === 'online') {
                $query->online();
            } elseif ($request->online_status === 'offline') {
                $query->offline();
            }
        }

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Show the form for creating a new hotspot user
     */
    public function create()
    {
        $routers = Router::active()->get();
        $bandwidthPlans = BandwidthPlan::active()->get();
        $customers = Customer::active()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'routers' => $routers,
                'bandwidth_plans' => $bandwidthPlans,
                'customers' => $customers,
            ],
        ]);
    }

    /**
     * Store a newly created hotspot user in storage and on MikroTik router
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:hotspot_users,username',
            'password' => 'required|string|min:4|max:255',
            'router_id' => 'required|exists:routers,id',
            'bandwidth_plan_id' => 'required|exists:bandwidth_plans,id',
            'customer_id' => 'nullable|exists:customers,id',
            'mac_address' => 'nullable|string|max:17',
            'ip_address' => 'nullable|ip',
            'expires_at' => 'nullable|date|after:now',
            'status' => 'nullable|in:active,disabled,expired',
        ]);

        DB::beginTransaction();

        try {
            // Get router and bandwidth plan
            $router = Router::findOrFail($validated['router_id']);
            $bandwidthPlan = BandwidthPlan::findOrFail($validated['bandwidth_plan_id']);

            // Calculate expiration date if not provided
            if (!isset($validated['expires_at']) && $bandwidthPlan->hasTimeLimit()) {
                $validityDuration = $bandwidthPlan->getValidityDuration();
                if ($validityDuration) {
                    $validated['expires_at'] = now()->addSeconds($validityDuration);
                }
            }

            // Set default status
            $validated['status'] = $validated['status'] ?? 'active';
            $validated['created_by'] = Auth::id();

            // Create user in database
            $hotspotUser = HotspotUser::create($validated);

            // Create user on MikroTik router
            if ($validated['status'] === 'active') {
                $mikrotik = new MikroTikService($router);
                $created = $mikrotik->createHotspotUser(
                    $validated['username'],
                    $validated['password'],
                    $bandwidthPlan->name
                );

                if (!$created) {
                    throw new Exception('Failed to create user on MikroTik router');
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hotspot user created successfully',
                'data' => $hotspotUser->load(['router', 'bandwidthPlan', 'customer', 'createdBy']),
            ], 201);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create hotspot user: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create hotspot user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified hotspot user with details
     */
    public function show($id)
    {
        try {
            $hotspotUser = HotspotUser::with([
                'router',
                'bandwidthPlan',
                'customer',
                'createdBy',
                'sessions' => function ($query) {
                    $query->latest()->limit(10);
                }
            ])->findOrFail($id);

            // Get online status from router
            if ($hotspotUser->router && $hotspotUser->router->isOnline()) {
                $mikrotik = new MikroTikService($hotspotUser->router);
                $activeConnection = $mikrotik->getUserTraffic($hotspotUser->username);

                if ($activeConnection) {
                    $hotspotUser->current_session = $activeConnection;
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $hotspotUser,
                    'statistics' => [
                        'total_sessions' => $hotspotUser->getTotalSessionsCount(),
                        'total_bytes' => $hotspotUser->getTotalBytes(),
                        'formatted_total_bytes' => $hotspotUser->getFormattedTotalBytes(),
                        'formatted_bytes_in' => $hotspotUser->getFormattedBytesIn(),
                        'formatted_bytes_out' => $hotspotUser->getFormattedBytesOut(),
                        'formatted_session_time' => $hotspotUser->getFormattedSessionTime(),
                        'days_until_expiration' => $hotspotUser->getDaysUntilExpiration(),
                    ],
                ],
            ]);

        } catch (Exception $e) {
            Log::error('Failed to get hotspot user details: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to get hotspot user details: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified hotspot user
     */
    public function edit($id)
    {
        try {
            $hotspotUser = HotspotUser::with(['router', 'bandwidthPlan', 'customer'])->findOrFail($id);
            $routers = Router::active()->get();
            $bandwidthPlans = BandwidthPlan::active()->get();
            $customers = Customer::active()->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $hotspotUser,
                    'routers' => $routers,
                    'bandwidth_plans' => $bandwidthPlans,
                    'customers' => $customers,
                ],
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load user data: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified hotspot user in storage and on router
     */
    public function update(Request $request, $id)
    {
        try {
            $hotspotUser = HotspotUser::findOrFail($id);

            $validated = $request->validate([
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('hotspot_users')->ignore($hotspotUser->id),
                ],
                'password' => 'nullable|string|min:4|max:255',
                'router_id' => 'required|exists:routers,id',
                'bandwidth_plan_id' => 'required|exists:bandwidth_plans,id',
                'customer_id' => 'nullable|exists:customers,id',
                'mac_address' => 'nullable|string|max:17',
                'ip_address' => 'nullable|ip',
                'expires_at' => 'nullable|date',
                'status' => 'nullable|in:active,disabled,expired',
            ]);

            DB::beginTransaction();

            $oldUsername = $hotspotUser->username;
            $oldRouterId = $hotspotUser->router_id;
            $oldStatus = $hotspotUser->status;

            // Get router and bandwidth plan
            $router = Router::findOrFail($validated['router_id']);
            $bandwidthPlan = BandwidthPlan::findOrFail($validated['bandwidth_plan_id']);

            // Update user on old router if username changed or router changed
            if ($oldUsername !== $validated['username'] || $oldRouterId !== $validated['router_id']) {
                $oldRouter = Router::find($oldRouterId);
                if ($oldRouter && $oldRouter->isOnline()) {
                    $mikrotik = new MikroTikService($oldRouter);
                    $mikrotik->removeHotspotUser($oldUsername);
                }
            }

            // Update user in database
            $updateData = array_filter($validated, function ($value) {
                return !is_null($value);
            });

            if (isset($validated['password'])) {
                $updateData['password'] = $validated['password'];
            }

            $hotspotUser->update($updateData);

            // Update or create user on MikroTik router
            if ($router->isOnline()) {
                $mikrotik = new MikroTikService($router);

                if ($validated['status'] === 'active' || $oldStatus === 'active') {
                    // Remove old user if exists
                    $mikrotik->removeHotspotUser($validated['username']);

                    // Create new user if active
                    if ($validated['status'] === 'active') {
                        $password = $validated['password'] ?? $hotspotUser->password;
                        $created = $mikrotik->createHotspotUser(
                            $validated['username'],
                            $password,
                            $bandwidthPlan->name
                        );

                        if (!$created) {
                            throw new Exception('Failed to update user on MikroTik router');
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hotspot user updated successfully',
                'data' => $hotspotUser->fresh(['router', 'bandwidthPlan', 'customer', 'createdBy']),
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update hotspot user: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update hotspot user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified hotspot user from storage and router
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $hotspotUser = HotspotUser::findOrFail($id);
            $router = $hotspotUser->router;

            // Disconnect if online
            if ($hotspotUser->is_online && $router && $router->isOnline()) {
                $mikrotik = new MikroTikService($router);
                $mikrotik->disconnectUser($hotspotUser->username);
            }

            // Remove from router
            if ($router && $router->isOnline()) {
                $mikrotik = new MikroTikService($router);
                $mikrotik->removeHotspotUser($hotspotUser->username);
            }

            // Delete from database
            $hotspotUser->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Hotspot user deleted successfully',
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete hotspot user: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete hotspot user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Disconnect an online user
     */
    public function disconnect($id)
    {
        try {
            $hotspotUser = HotspotUser::findOrFail($id);

            if (!$hotspotUser->is_online) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is not online',
                ], 400);
            }

            $router = $hotspotUser->router;

            if (!$router || !$router->isOnline()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router is not accessible',
                ], 400);
            }

            // Disconnect from router
            $mikrotik = new MikroTikService($router);
            $disconnected = $mikrotik->disconnectUser($hotspotUser->username);

            if (!$disconnected) {
                throw new Exception('Failed to disconnect user from router');
            }

            // Update user status
            $hotspotUser->markAsOffline();

            return response()->json([
                'success' => true,
                'message' => 'User disconnected successfully',
                'data' => $hotspotUser->fresh(),
            ]);

        } catch (Exception $e) {
            Log::error('Failed to disconnect user: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to disconnect user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Enable a disabled user
     */
    public function enable($id)
    {
        DB::beginTransaction();

        try {
            $hotspotUser = HotspotUser::findOrFail($id);

            if ($hotspotUser->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot enable expired user',
                ], 400);
            }

            if ($hotspotUser->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already active',
                ], 400);
            }

            $router = $hotspotUser->router;

            if (!$router || !$router->isOnline()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router is not accessible',
                ], 400);
            }

            // Enable on router
            $mikrotik = new MikroTikService($router);
            $enabled = $mikrotik->enableHotspotUser($hotspotUser->username);

            if (!$enabled) {
                // If user doesn't exist on router, create it
                $enabled = $mikrotik->createHotspotUser(
                    $hotspotUser->username,
                    $hotspotUser->password,
                    $hotspotUser->bandwidthPlan->name
                );

                if (!$enabled) {
                    throw new Exception('Failed to enable user on router');
                }
            }

            // Enable in database
            $hotspotUser->enable();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User enabled successfully',
                'data' => $hotspotUser->fresh(),
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to enable user: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to enable user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Disable an active user
     */
    public function disable($id)
    {
        DB::beginTransaction();

        try {
            $hotspotUser = HotspotUser::findOrFail($id);

            if ($hotspotUser->isDisabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already disabled',
                ], 400);
            }

            $router = $hotspotUser->router;

            if (!$router || !$router->isOnline()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router is not accessible',
                ], 400);
            }

            // Disconnect if online
            if ($hotspotUser->is_online) {
                $mikrotik = new MikroTikService($router);
                $mikrotik->disconnectUser($hotspotUser->username);
            }

            // Disable on router
            $mikrotik = new MikroTikService($router);
            $disabled = $mikrotik->disableHotspotUser($hotspotUser->username);

            if (!$disabled) {
                throw new Exception('Failed to disable user on router');
            }

            // Disable in database
            $hotspotUser->disable();
            $hotspotUser->markAsOffline();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'User disabled successfully',
                'data' => $hotspotUser->fresh(),
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to disable user: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to disable user: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get only online users
     */
    public function onlineUsers(Request $request)
    {
        $query = HotspotUser::with(['router', 'bandwidthPlan', 'customer'])
            ->online();

        // Router filter
        if ($request->filled('router_id')) {
            $query->forRouter($request->router_id);
        }

        // Search filter
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $users = $query->paginate($perPage);

        // Get real-time data from routers
        foreach ($users as $user) {
            if ($user->router && $user->router->isOnline()) {
                try {
                    $mikrotik = new MikroTikService($user->router);
                    $activeConnection = $mikrotik->getUserTraffic($user->username);

                    if ($activeConnection) {
                        $user->current_session = $activeConnection;
                    }
                } catch (Exception $e) {
                    Log::warning('Failed to get real-time data for user ' . $user->username . ': ' . $e->getMessage());
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    /**
     * Update usage statistics from router
     */
    public function updateUsage($id)
    {
        try {
            $hotspotUser = HotspotUser::findOrFail($id);
            $router = $hotspotUser->router;

            if (!$router || !$router->isOnline()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Router is not accessible',
                ], 400);
            }

            // Get user data from router
            $mikrotik = new MikroTikService($router);
            $routerUser = $mikrotik->getHotspotUser($hotspotUser->username);

            if (!$routerUser) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found on router',
                ], 404);
            }

            // Get active session data
            $activeConnection = $mikrotik->getUserTraffic($hotspotUser->username);

            if ($activeConnection) {
                // User is online, update with active session data
                $bytesIn = (int) ($activeConnection['bytes-in'] ?? 0);
                $bytesOut = (int) ($activeConnection['bytes-out'] ?? 0);
                $uptime = $this->parseUptime($activeConnection['uptime'] ?? '0s');

                $hotspotUser->update([
                    'is_online' => true,
                    'bytes_in' => $bytesIn,
                    'bytes_out' => $bytesOut,
                    'session_time' => $uptime,
                    'ip_address' => $activeConnection['address'] ?? $hotspotUser->ip_address,
                    'mac_address' => $activeConnection['mac-address'] ?? $hotspotUser->mac_address,
                    'last_login_at' => $hotspotUser->last_login_at ?? now(),
                ]);
            } else {
                // User is offline
                if ($hotspotUser->is_online) {
                    $hotspotUser->markAsOffline();
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Usage statistics updated successfully',
                'data' => $hotspotUser->fresh(),
            ]);

        } catch (Exception $e) {
            Log::error('Failed to update usage statistics: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update usage statistics: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse MikroTik uptime string to seconds
     */
    private function parseUptime(string $uptime): int
    {
        $seconds = 0;

        // Parse days (e.g., "1d")
        if (preg_match('/(\d+)d/', $uptime, $matches)) {
            $seconds += (int) $matches[1] * 86400;
        }

        // Parse hours (e.g., "2h")
        if (preg_match('/(\d+)h/', $uptime, $matches)) {
            $seconds += (int) $matches[1] * 3600;
        }

        // Parse minutes (e.g., "30m")
        if (preg_match('/(\d+)m/', $uptime, $matches)) {
            $seconds += (int) $matches[1] * 60;
        }

        // Parse seconds (e.g., "45s")
        if (preg_match('/(\d+)s/', $uptime, $matches)) {
            $seconds += (int) $matches[1];
        }

        return $seconds;
    }
}
