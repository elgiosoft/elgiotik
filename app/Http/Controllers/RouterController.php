<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Models\HotspotUser;
use App\Services\MikroTikService;
use App\Services\WireGuardService;
use App\Services\OpenVPNService;
use App\Services\VPNFactory;
use ElgioPay\SDK\ElgioPayClient;
use ElgioPay\SDK\ElgioPayException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
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

        // Scope to user's routers only (unless admin)
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

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
            'ip_address' => ['required', 'ip',  
                Rule::unique('routers', 'ip_address')->where(function ($query) use ($request) {
                    return $query->where('user_id', auth()->id());
                }),],
            'api_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'vpn_enabled' => ['boolean'],
            'routeros_version' => ['required', 'string', 'regex:/^\d+\.\d+/'],
        ]);

        try {
            // Set user_id to authenticated user
            $validated['user_id'] = auth()->id();

            // Encrypt password before storing
            $validated['password'] = Crypt::encryptString($validated['password']);
            $validated['status'] = 'offline'; // Default status
            $validated['is_active'] = $request->boolean('is_active', true);
            $validated['vpn_enabled'] = $request->boolean('vpn_enabled', config('mikrotik.vpn.auto_provision', true));

            // Determine VPN type based on RouterOS version
            $validated['vpn_type'] = VPNFactory::determineVPNType($validated['routeros_version']);

            $router = Router::create($validated);

            // Auto-provision VPN if enabled
            if ($validated['vpn_enabled']) {
                $this->provisionVpn($router);
            }

            $message = 'Router created successfully.';
            if ($router->vpn_enabled) {
                $vpnTypeName = VPNFactory::getVPNTypeName($router->vpn_type);
                $message .= " {$vpnTypeName} VPN configuration generated. Download the setup script from the router details page.";
            }

            return redirect()
                ->route('routers.show', $router)
                ->with('success', $message);
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
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

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
                    $mikrotik = new MikroTikService($router);

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
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

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
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('routers')->ignore($router->id)],
            'ip_address' => ['required', 'ip', Rule::unique('routers', 'ip_address')
                        ->where(fn ($query) => $query->where('user_id', auth()->id()))
                        ->ignore($router->id),
            ],
            'api_port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:6'],
            'location' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['boolean'],
            'status' => ['nullable', 'in:online,offline,maintenance'],
            'routeros_version' => ['required', 'string', 'regex:/^\d+\.\d+/'],
        ]);

        try {
            // Only update password if provided
            if ($request->filled('password')) {
                $validated['password'] = Crypt::encryptString($validated['password']);
            } else {
                unset($validated['password']);
            }

            $validated['is_active'] = $request->boolean('is_active');

            // Check if RouterOS version changed and VPN is enabled
            $versionChanged = $router->routeros_version !== $validated['routeros_version'];
            if ($versionChanged) {
                // Update VPN type based on new RouterOS version
                $newVpnType = VPNFactory::determineVPNType($validated['routeros_version']);
                $validated['vpn_type'] = $newVpnType;

                // If VPN is enabled and type changed, regenerate configuration
                if ($router->vpn_enabled && $router->vpn_type !== $newVpnType) {
                    Log::info("RouterOS version changed for {$router->name}, VPN type updated from {$router->vpn_type} to {$newVpnType}");
                }
            }

            $router->update($validated);

            $message = 'Router updated successfully.';

            // Warn user if VPN needs reconfiguration
            if ($versionChanged && $router->vpn_enabled && $router->vpn_type !== $router->getOriginal('vpn_type')) {
                $message .= ' RouterOS version changed - VPN type updated. Please regenerate VPN configuration from the router details page.';
            }

            return redirect()
                ->route('routers.show', $router)
                ->with('success', $message);
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
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

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
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {

            $mikrotik = new MikroTikService();
            $result = $mikrotik->testConnection($router);

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
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {

            $mikrotik = new MikroTikService($router);

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
                $message .= ". " . count($errors) . " error(s) occurred.". join(', ', array_map(function($error) {
                    return "User '{$error['user']}': {$error['error']}";
                }, $errors));
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
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        $validated = $request->validate([
            'username' => ['required', 'string'],
        ]);

        try {

            $mikrotik = new MikroTikService($router);

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

    /**
     * Provision VPN for router (generate keys, assign IP, create config)
     */
    protected function provisionVpn(Router $router): void
    {
        try {
            // Get appropriate VPN service based on router version
            $vpnService = VPNFactory::forRouter($router);

            // Get next available IP
            $vpnIp = $vpnService->getNextAvailableIp();

            // Generate keys/certificates based on VPN type
            if ($router->vpn_type === 'wireguard') {
                $keys = $vpnService->generateKeyPair();
                $port = config('mikrotik.vpn.port', 51820);
            } else {
                // OpenVPN
                $keys = $vpnService->generateCertificates($router);
                $port = config('mikrotik.vpn.openvpn_port', 1194);
            }

            // Update router with VPN details
            $router->update([
                'vpn_ip' => $vpnIp,
                'vpn_public_key' => $keys['public_key'] ?? null,
                'vpn_private_key' => $keys['private_key'] ?? $keys['psk'],
                'vpn_endpoint' => config('mikrotik.vpn.server_endpoint'),
                'vpn_listen_port' => $port,
            ]);

            // Generate MikroTik configuration script
            $config = $vpnService->generateMikroTikConfig($router);
            $router->update(['vpn_config_script' => $config]);

            // Try to add peer to server (if WireGuard)
            if ($router->vpn_type === 'wireguard' && method_exists($vpnService, 'addPeerToServer')) {
                $vpnService->addPeerToServer($router);
            }

            Log::info("VPN provisioned for router {$router->name}", [
                'router_id' => $router->id,
                'vpn_type' => $router->vpn_type,
                'vpn_ip' => $vpnIp,
            ]);

        } catch (Exception $e) {
            Log::error("Failed to provision VPN for router {$router->name}: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Enable VPN for an existing router
     */
    public function enableVpn(Router $router)
    {
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {
            if ($router->vpn_enabled) {
                return back()->with('info', 'VPN is already enabled for this router.');
            }

            $router->update(['vpn_enabled' => true]);
            $this->provisionVpn($router);

            return back()->with('success', 'VPN enabled and configured successfully. Download the setup script below.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to enable VPN: ' . $e->getMessage());
        }
    }

    /**
     * Disable VPN for a router
     */
    public function disableVpn(Router $router)
    {
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {
            if (!$router->vpn_enabled) {
                return back()->with('info', 'VPN is not enabled for this router.');
            }

            // Remove peer from server (if WireGuard)
            if ($router->vpn_type === 'wireguard') {
                $vpnService = new WireGuardService();
                $vpnService->removePeerFromServer($router);
            }

            // Clear VPN data but keep history
            $router->update(['vpn_enabled' => false]);

            Log::info("VPN disabled for router {$router->name}", [
                'router_id' => $router->id,
            ]);

            return back()->with('success', 'VPN disabled successfully.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to disable VPN: ' . $e->getMessage());
        }
    }

    /**
     * Regenerate VPN configuration
     */
    public function regenerateVpn(Router $router)
    {
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {
            if (!$router->vpn_enabled) {
                return back()->with('error', 'VPN is not enabled for this router.');
            }

            $vpnService = VPNFactory::forRouter($router);

            // Remove old peer (if WireGuard)
            if ($router->vpn_type === 'wireguard' && method_exists($vpnService, 'removePeerFromServer')) {
                $vpnService->removePeerFromServer($router);
            }

            // Generate new keys/certificates
            if ($router->vpn_type === 'wireguard') {
                $keys = $vpnService->generateKeyPair();
            } else {
                $keys = $vpnService->generateCertificates($router);
            }

            // Update router
            $router->update([
                'vpn_public_key' => $keys['public_key'] ?? null,
                'vpn_private_key' => $keys['private_key'] ?? $keys['psk'],
            ]);

            // Regenerate config
            $config = $vpnService->generateMikroTikConfig($router);
            $router->update(['vpn_config_script' => $config]);

            // Add new peer (if WireGuard)
            if ($router->vpn_type === 'wireguard' && method_exists($vpnService, 'addPeerToServer')) {
                $vpnService->addPeerToServer($router);
            }

            Log::info("VPN regenerated for router {$router->name}", [
                'router_id' => $router->id,
            ]);

            return back()->with('success', 'VPN configuration regenerated successfully. Download the new setup script.');
        } catch (Exception $e) {
            return back()->with('error', 'Failed to regenerate VPN: ' . $e->getMessage());
        }
    }

    /**
     * Download VPN setup script for router
     */
    public function downloadVpnScript(Router $router)
    {
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        if (!$router->vpn_enabled || !$router->vpn_config_script) {
            return back()->with('error', 'VPN configuration not available for this router.');
        }

        $filename = 'mikrotik-vpn-' . str_replace(' ', '-', strtolower($router->name)) . '.rsc';

        return response($router->vpn_config_script)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Withdraw router wallet balance using ElgioPay payout
     */
    public function withdraw(Request $request, Router $router)
    {
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        // Validate request
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:' . $router->wallet_balance],
            'phone_number' => ['required', 'string', 'regex:/^237[0-9]{9}$/'],
        ]);

        try {
            $amount = $validated['amount'];
            $phoneNumber = $validated['phone_number'];

            if ($amount <= 0) {
                return back()->with('error', 'Invalid withdrawal amount.');
            }

            if ($amount > $router->wallet_balance) {
                return back()->with('error', 'Insufficient balance. Available: ' . number_format($router->wallet_balance, 0) . ' XAF');
            }

            // Initialize ElgioPay client
            $elgioPay = new ElgioPayClient();

            // Create payout request
            $payoutData = [
                'amount' => $amount * 10, // Convert to minimum currency unit
                'phone_number' => $phoneNumber,
                'payment_method' => 'mtn_mobile_money',
                'currency' => 'XAF',
                'reference' => 'WD-' . $router->id . '-' . time(),
                'description' => 'Router wallet withdrawal - ' . $router->name,
            ];

            $payoutResponse = $elgioPay->createPayout($payoutData);

            // Debit the wallet
            $router->debitWallet(
                $amount,
                $payoutResponse['payout_id'] ?? $payoutData['reference'],
                'Withdrawal via ElgioPay to ' . $phoneNumber,
                ['payout_response' => $payoutResponse]
            );

            Log::info("Wallet withdrawal processed for router {$router->name}", [
                'router_id' => $router->id,
                'amount' => $amount,
                'phone_number' => $phoneNumber,
                'reference' => $payoutData['reference'],
            ]);

            return back()->with('success', 'Withdrawal request submitted successfully. Amount: ' . number_format($amount, 0) . ' XAF to ' . $phoneNumber);

        } catch (ElgioPayException $e) {
            Log::error('ElgioPay withdrawal error: ' . $e->getMessage());
            return back()->with('error', 'Withdrawal failed: ' . $e->getMessage());

        } catch (Exception $e) {
            Log::error('Withdrawal error: ' . $e->getMessage());
            return back()->with('error', 'Withdrawal failed: ' . $e->getMessage());
        }
    }

    /**
     * Download guest portal HTML file
     */
    public function downloadPortal(Router $router)
    {
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {
            // Render the portal view as HTML
            $html = view('guest.portal', compact('router'))->render();

            $filename = 'hotspot-portal-' . str_replace(' ', '-', strtolower($router->name)) . '.html';

            return response($html)
                ->header('Content-Type', 'text/html')
                ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to generate portal file: ' . $e->getMessage());
        }
    }

    /**
     * Upload portal HTML file directly to MikroTik router
     */
    public function uploadPortalToRouter(Router $router)
    {
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {
            // Check router status
            if ($router->status === 'offline') {
                return back()->with('error', 'Router is offline. Please test connection first.');
            }

            // Render the portal view as HTML
            $html = view('guest.portal', compact('router'))->render();
            $filename = 'login.html';

            // Connect to MikroTik
            $mikrotik = new MikroTikService($router);

            // Upload file to router
            $result = $mikrotik->uploadFile($filename, $html, 'hotspot');

            if ($result) {
                Log::info("Portal HTML uploaded to router {$router->name}", [
                    'router_id' => $router->id,
                    'filename' => $filename,
                ]);

                return back()->with('success', 'Portal HTML file uploaded successfully to router at hotspot/login.html');
            } else {
                return back()->with('error', 'Failed to upload portal file to router.');
            }

        } catch (Exception $e) {
            Log::error('Portal upload error: ' . $e->getMessage());
            return back()->with('error', 'Failed to upload portal to router: ' . $e->getMessage());
        }
    }

    /**
     * Generate router hash if missing
     */
    public function generateHash(Router $router)
    {
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {
            // Check if router already has a hash
            if ($router->router_hash) {
                return back()->with('info', 'Router already has a hash generated.');
            }

            // Generate new hash
            $router->update([
                'router_hash' => \Illuminate\Support\Str::random(32),
            ]);

            Log::info("Router hash generated for {$router->name}", [
                'router_id' => $router->id,
                'router_hash' => $router->router_hash,
            ]);

            return back()->with('success', 'Router hash generated successfully! You can now use the guest portal.');

        } catch (Exception $e) {
            Log::error('Hash generation error: ' . $e->getMessage());
            return back()->with('error', 'Failed to generate router hash: ' . $e->getMessage());
        }
    }

    /**
     * Load vouchers from router by importing hotspot user profiles
     */
    public function loadVouchers(Router $router)
    {
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {
            // Check if router is online
            if ($router->status === 'offline') {
                return back()->with('error', 'Router is offline. Please test connection first.');
            }

            // Connect to MikroTik
            $mikrotik = new MikroTikService($router);

            // Get all hotspot user profiles from router
            $profiles = $mikrotik->getProfiles();

            if (empty($profiles)) {
                return back()->with('info', 'No user profiles found on the router.');
            }

            // Get all bandwidth plans for this user
            $bandwidthPlans = auth()->user()->bandwidthPlans;

            $created = 0;
            $skipped = 0;
            $errors = [];

            foreach ($profiles as $profile) {
                try {
                    $profileName = $profile['name'] ?? null;

                    if (!$profileName) {
                        continue;
                    }

                    // Skip default profiles
                    if (in_array($profileName, ['default', 'default-encryption'])) {
                        $skipped++;
                        continue;
                    }

                    // Check if voucher already exists for this profile
                    $existingVoucher = $router->vouchers()
                        ->where('mikrotik_profile_id', $profileName)
                        ->first();

                    if ($existingVoucher) {
                        $skipped++;
                        continue;
                    }

                    // Try to match bandwidth plan by rate limit
                    $rateLimit = $profile['rate-limit'] ?? null;
                    $bandwidthPlanId = 0; // Default to 0 if no match

                    if ($rateLimit && !empty($bandwidthPlans)) {
                        $bandwidthPlanId = $this->matchBandwidthPlan($rateLimit, $bandwidthPlans);
                    }

                    // Create voucher from profile
                    $router->vouchers()->create([
                        'bandwidth_plan_id' => $bandwidthPlanId,
                        'mikrotik_profile_id' => $profileName,
                        'status' => 'active',
                        'price' => 0, // Default price, can be updated later
                        'user_capacity' => 1, // Default capacity
                        'users_generated' => 0,
                    ]);

                    $created++;

                } catch (Exception $e) {
                    $errors[] = [
                        'profile' => $profileName ?? 'unknown',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            $message = "Load completed: {$created} voucher(s) created, {$skipped} skipped";

            if (count($errors) > 0) {
                $message .= ". " . count($errors) . " error(s) occurred.";
            }

            Log::info("Vouchers loaded from router {$router->name}", [
                'router_id' => $router->id,
                'created' => $created,
                'skipped' => $skipped,
                'errors' => count($errors),
            ]);

            return back()->with('success', $message);

        } catch (Exception $e) {
            Log::error('Load vouchers error: ' . $e->getMessage());
            return back()->with('error', 'Failed to load vouchers from router: ' . $e->getMessage());
        }
    }

    /**
     * Try to match a MikroTik rate limit to a bandwidth plan
     *
     * @param string $rateLimit MikroTik rate limit format (e.g., "5M/5M", "10M/10M")
     * @param \Illuminate\Support\Collection $bandwidthPlans Available bandwidth plans
     * @return int Bandwidth plan ID or 0 if no match
     */
    protected function matchBandwidthPlan(string $rateLimit, $bandwidthPlans): int
    {
        // Parse rate limit (format: "upload/download" e.g., "5M/5M")
        $parts = explode('/', $rateLimit);

        if (count($parts) !== 2) {
            return 0;
        }

        $uploadLimit = $this->parseSpeed($parts[0]);
        $downloadLimit = $this->parseSpeed($parts[1]);

        // Try to find matching bandwidth plan
        foreach ($bandwidthPlans as $plan) {
            $planUpload = $this->parseSpeed($plan->upload_speed ?? '');
            $planDownload = $this->parseSpeed($plan->download_speed ?? '');

            // Match if speeds are equal (or within 10% tolerance)
            if ($this->speedsMatch($uploadLimit, $planUpload) &&
                $this->speedsMatch($downloadLimit, $planDownload)) {
                return $plan->id;
            }
        }

        return 0; // No match found
    }

    /**
     * Parse speed string to bytes per second
     *
     * @param string $speed Speed string (e.g., "5M", "1G", "512k")
     * @return int Speed in bytes per second
     */
    protected function parseSpeed(string $speed): int
    {
        $speed = trim(strtoupper($speed));

        if (empty($speed)) {
            return 0;
        }

        // Extract number and unit
        preg_match('/^(\d+(?:\.\d+)?)\s*([KMGT])?/', $speed, $matches);

        if (empty($matches)) {
            return 0;
        }

        $number = (float) $matches[1];
        $unit = $matches[2] ?? '';

        // Convert to bytes per second
        $multipliers = [
            'K' => 1024,
            'M' => 1024 * 1024,
            'G' => 1024 * 1024 * 1024,
            'T' => 1024 * 1024 * 1024 * 1024,
        ];

        $multiplier = $multipliers[$unit] ?? 1;

        return (int) ($number * $multiplier);
    }

    /**
     * Check if two speeds match (within 10% tolerance)
     *
     * @param int $speed1 First speed in bytes per second
     * @param int $speed2 Second speed in bytes per second
     * @return bool True if speeds match
     */
    protected function speedsMatch(int $speed1, int $speed2): bool
    {
        if ($speed1 == 0 || $speed2 == 0) {
            return false;
        }

        $tolerance = 0.1; // 10% tolerance
        $ratio = $speed1 / $speed2;

        return $ratio >= (1 - $tolerance) && $ratio <= (1 + $tolerance);
    }
}
