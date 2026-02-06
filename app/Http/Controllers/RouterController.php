<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Models\HotspotUser;
use App\Services\MikroTikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
use Exception;

class RouterController extends Controller
{
    /**
     * Display a listing of the routers.
     */
    public function index(Request $request)
    {
        $query = Router::query();

        // Search by name, IP address, or location
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by active/inactive
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        // Sort
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $routers = $query->paginate($request->get('per_page', 15));

        return view('routers.index', compact('routers'));
    }

    /**
     * Show the form for creating a new router.
     */
    public function create()
    {
        return view('routers.create');
    }

    /**
     * Store a newly created router in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:routers,name'],
            'ip_address' => ['required', 'ip', 'unique:routers,ip_address'],
            'api_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
        ]);

        try {
            // Encrypt password before storing
            $validated['password'] = Crypt::encryptString($validated['password']);
            $validated['status'] = 'offline'; // Default status
            $validated['is_active'] = $request->boolean('is_active', true);

            $router = Router::create($validated);

            return redirect()
                ->route('routers.show', $router)
                ->with('success', 'Router created successfully.');
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create router: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified router with statistics.
     */
    public function show(Router $router)
    {
        try {
            // Load relationships
            $router->load(['hotspotUsers', 'vouchers']);

            $statistics = [
                'online_users' => 0,
                'total_users' => $router->hotspotUsers()->count(),
                'active_users' => $router->hotspotUsers()->active()->count(),
                'hotspot_servers' => 0,
                'system_info' => null,
            ];

            // Try to get real-time statistics from router
            if ($router->isOnline()) {
                try {
                    $decryptedPassword = Crypt::decryptString($router->password);
                    $tempRouter = clone $router;
                    $tempRouter->password = $decryptedPassword;

                    $mikrotik = new MikroTikService($tempRouter);

                    // Get online users count
                    $activeConnections = $mikrotik->getActiveConnections();
                    $statistics['online_users'] = count($activeConnections);

                    // Get hotspot servers
                    $hotspotServers = $mikrotik->getHotspotServers();
                    $statistics['hotspot_servers'] = count($hotspotServers);

                    // Get system info
                    $statistics['system_info'] = $mikrotik->getSystemResource();
                } catch (Exception $e) {
                    // If connection fails, update status
                    $router->updateStatus('offline');
                }
            }

            return view('routers.show', compact('router', 'statistics'));
        } catch (Exception $e) {
            return back()->with('error', 'Failed to load router details: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified router.
     */
    public function edit(Router $router)
    {
        try {
            // Decrypt password for editing
            $router->decrypted_password = Crypt::decryptString($router->password);
        } catch (Exception $e) {
            $router->decrypted_password = '';
        }

        return view('routers.edit', compact('router'));
    }

    /**
     * Update the specified router in storage.
     */
    public function update(Request $request, Router $router)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('routers')->ignore($router->id)],
            'ip_address' => ['required', 'ip', Rule::unique('routers')->ignore($router->id)],
            'api_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'status' => ['nullable', 'in:online,offline,maintenance'],
        ]);

        try {
            // Only update password if provided
            if ($request->filled('password')) {
                $validated['password'] = Crypt::encryptString($validated['password']);
            } else {
                unset($validated['password']);
            }

            $validated['is_active'] = $request->boolean('is_active');

            $router->update($validated);

            return redirect()
                ->route('routers.show', $router)
                ->with('success', 'Router updated successfully.');
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update router: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified router from storage.
     */
    public function destroy(Router $router)
    {
        try {
            // Check if there are active users
            $activeUsersCount = $router->hotspotUsers()->where('is_online', true)->count();

            if ($activeUsersCount > 0) {
                return back()->with('error', "Cannot delete router. There are {$activeUsersCount} active user(s) connected.");
            }

            // Check if there are any users at all (you might want to warn about this)
            $totalUsersCount = $router->hotspotUsers()->count();

            if ($totalUsersCount > 0) {
                return back()->with('warning', "Router has {$totalUsersCount} user(s) associated. Please reassign or delete them first.");
            }

            $routerName = $router->name;
            $router->delete();

            return redirect()
                ->route('routers.index')
                ->with('success', "Router '{$routerName}' deleted successfully.");
        } catch (Exception $e) {
            return back()->with('error', 'Failed to delete router: ' . $e->getMessage());
        }
    }

    /**
     * Test connection to the router.
     */
    public function testConnection(Router $router)
    {
        try {
            // Decrypt password for connection test
            $decryptedPassword = Crypt::decryptString($router->password);
            $tempRouter = clone $router;
            $tempRouter->password = $decryptedPassword;

            $mikrotik = new MikroTikService();
            $result = $mikrotik->testConnection($tempRouter);

            if ($result['success']) {
                $router->updateStatus('online');

                return back()->with('success', 'Connection successful! Router is online.');
            } else {
                $router->updateStatus('offline');

                return back()->with('error', 'Connection failed: ' . $result['message']);
            }
        } catch (Exception $e) {
            $router->updateStatus('offline');

            return back()->with('error', 'Connection test failed: ' . $e->getMessage());
        }
    }

    /**
     * Sync hotspot users from the router.
     */
    public function syncUsers(Router $router)
    {
        try {
            // Decrypt password for connection
            $decryptedPassword = Crypt::decryptString($router->password);
            $tempRouter = clone $router;
            $tempRouter->password = $decryptedPassword;

            $mikrotik = new MikroTikService($tempRouter);

            // Get users from router
            $routerUsers = $mikrotik->getHotspotUsers();
            $activeConnections = $mikrotik->getActiveConnections();

            // Create a map of active users
            $activeUsernames = collect($activeConnections)->pluck('user')->toArray();

            $synced = 0;
            $created = 0;
            $updated = 0;
            $errors = [];

            foreach ($routerUsers as $routerUser) {
                try {
                    $username = $routerUser['name'] ?? null;

                    if (!$username) {
                        continue;
                    }

                    // Check if user exists in database
                    $hotspotUser = HotspotUser::where('username', $username)
                        ->where('router_id', $router->id)
                        ->first();

                    $isOnline = in_array($username, $activeUsernames);

                    if ($hotspotUser) {
                        // Update existing user
                        $hotspotUser->update([
                            'is_online' => $isOnline,
                            'status' => ($routerUser['disabled'] ?? 'no') === 'yes' ? 'disabled' : 'active',
                        ]);
                        $updated++;
                    } else {
                        // Create new user
                        HotspotUser::create([
                            'username' => $username,
                            'password' => $routerUser['password'] ?? null,
                            'router_id' => $router->id,
                            'status' => ($routerUser['disabled'] ?? 'no') === 'yes' ? 'disabled' : 'active',
                            'is_online' => $isOnline,
                            'mac_address' => $routerUser['mac-address'] ?? null,
                        ]);
                        $created++;
                    }

                    $synced++;
                } catch (Exception $e) {
                    $errors[] = [
                        'user' => $routerUser['name'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $message = "Sync completed: {$synced} users processed ({$created} created, {$updated} updated)";

            if (count($errors) > 0) {
                $message .= ". " . count($errors) . " error(s) occurred.";
            }

            return back()->with('success', $message);
        } catch (Exception $e) {
            return back()->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Disconnect a specific user from the router.
     */
    public function disconnect(Router $router, Request $request)
    {
        $validated = $request->validate([
            'username' => ['required', 'string'],
        ]);

        try {
            // Decrypt password for connection
            $decryptedPassword = Crypt::decryptString($router->password);
            $tempRouter = clone $router;
            $tempRouter->password = $decryptedPassword;

            $mikrotik = new MikroTikService($tempRouter);

            $success = $mikrotik->disconnectUser($validated['username']);

            if ($success) {
                // Update user status in database
                $hotspotUser = HotspotUser::where('username', $validated['username'])
                    ->where('router_id', $router->id)
                    ->first();

                if ($hotspotUser) {
                    $hotspotUser->markAsOffline();
                }

                return back()->with('success', "User '{$validated['username']}' disconnected successfully.");
            } else {
                return back()->with('error', "Failed to disconnect user. User may not be online.");
            }
        } catch (Exception $e) {
            return back()->with('error', 'Disconnect failed: ' . $e->getMessage());
        }
    }
}
