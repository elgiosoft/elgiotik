<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Router;
use App\Models\Customer;
use App\Models\BandwidthPlan;
use App\Services\HotspotUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class VoucherController extends Controller
{
    protected $hotspotUserService;

    public function __construct(HotspotUserService $hotspotUserService)
    {
        $this->hotspotUserService = $hotspotUserService;
    }

    /**
     * Display vouchers for a specific router
     */
    public function index(Request $request, Router $router)
    {
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        $query = $router->vouchers()->with(['bandwidthPlan', 'customer', 'hotspotUsers']);

        // Search by notes or ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', $search)
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by bandwidth plan
        if ($request->filled('bandwidth_plan_id')) {
            $query->where('bandwidth_plan_id', $request->bandwidth_plan_id);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $vouchers = $query->paginate($perPage)->withQueryString();

        // Get filter options for the view
        $bandwidthPlans = BandwidthPlan::active()->orderBy('name')->get();

        return view('vouchers.index', compact('vouchers', 'router', 'bandwidthPlans'));
    }

    /**
     * Show the form for creating a new voucher (profile)
     */
    public function create(Router $router)
    {
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        $user = auth()->user();

        // Scope bandwidth plans to user's plans
        $bandwidthPlans = $user->isAdmin()
            ? BandwidthPlan::active()->orderBy('name')->get()
            : BandwidthPlan::where('user_id', $user->id)->active()->orderBy('name')->get();

        return view('vouchers.create', compact('router', 'bandwidthPlans'));
    }

    /**
     * Store a newly created voucher (profile)
     */
    public function store(Request $request, Router $router)
    {
        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        $validated = $request->validate([
            'bandwidth_plan_id' => 'required|exists:bandwidth_plans,id',
            'user_capacity' => 'required|integer|min:1|max:1000',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'auto_sync' => 'boolean',
        ]);

        try {
            // Get bandwidth plan to set price if not provided
            $bandwidthPlan = BandwidthPlan::findOrFail($validated['bandwidth_plan_id']);

            // Authorization check for bandwidth plan
            if (!auth()->user()->ownsBandwidthPlan($bandwidthPlan)) {
                return back()->with('error', 'Unauthorized: You do not own the specified bandwidth plan.');
            }

            $price = $validated['price'] ?? $bandwidthPlan->price;

            $voucher = Voucher::create([
                'bandwidth_plan_id' => $validated['bandwidth_plan_id'],
                'router_id' => $router->id,
                'price' => $price,
                'user_capacity' => $validated['user_capacity'],
                'users_generated' => 0,
                'status' => 'active',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Auto-sync profile to router if requested
            if ($request->boolean('auto_sync', true)) {
                $syncResult = $this->hotspotUserService->syncProfileToRouter($voucher);

                if (!$syncResult['success']) {
                    return redirect()
                        ->route('routers.vouchers.show', [$router, $voucher])
                        ->with('warning', 'Voucher created but profile sync failed: ' . $syncResult['message']);
                }
            }

            return redirect()
                ->route('routers.vouchers.show', [$router, $voucher])
                ->with('success', 'Voucher profile created successfully.');

        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create voucher: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified voucher with hotspot users
     */
    public function show(Router $router, Voucher $voucher)
    {
        // Ensure voucher belongs to router
        if ($voucher->router_id !== $router->id) {
            abort(404);
        }

        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        $voucher->load([
            'bandwidthPlan',
            'customer',
            'hotspotUsers' => function ($query) {
                $query->latest();
            }
        ]);

        // Get statistics
        $stats = [
            'total_users' => $voucher->hotspotUsers()->count(),
            'pending_users' => $voucher->hotspotUsers()->pending()->count(),
            'paid_users' => $voucher->hotspotUsers()->paid()->count(),
            'synced_users' => $voucher->hotspotUsers()->synced()->count(),
            'failed_syncs' => $voucher->hotspotUsers()->notSynced()->count(),
            'remaining_capacity' => $voucher->getRemainingCapacity(),
        ];

        return view('vouchers.show', compact('router', 'voucher', 'stats'));
    }

    /**
     * Show the form for editing the voucher
     */
    public function edit(Router $router, Voucher $voucher)
    {
        // Ensure voucher belongs to router
        if ($voucher->router_id !== $router->id) {
            abort(404);
        }

        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        $user = auth()->user();

        // Scope bandwidth plans to user's plans
        $bandwidthPlans = $user->isAdmin()
            ? BandwidthPlan::active()->orderBy('name')->get()
            : BandwidthPlan::where('user_id', $user->id)->active()->orderBy('name')->get();

        $customers = Customer::active()->orderBy('name')->get();

        return view('vouchers.edit', compact('router', 'voucher', 'bandwidthPlans', 'customers'));
    }

    /**
     * Update the voucher
     */
    public function update(Request $request, Router $router, Voucher $voucher)
    {
        // Ensure voucher belongs to router
        if ($voucher->router_id !== $router->id) {
            abort(404);
        }

        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        $validated = $request->validate([
            'bandwidth_plan_id' => 'required|exists:bandwidth_plans,id',
            'customer_id' => 'nullable|exists:customers,id',
            'user_capacity' => 'required|integer|min:' . $voucher->users_generated,
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            // Authorization check for bandwidth plan
            $bandwidthPlan = BandwidthPlan::findOrFail($validated['bandwidth_plan_id']);
            if (!auth()->user()->ownsBandwidthPlan($bandwidthPlan)) {
                return back()->with('error', 'Unauthorized: You do not own the specified bandwidth plan.');
            }

            $voucher->update($validated);

            return redirect()
                ->route('routers.vouchers.show', [$router, $voucher])
                ->with('success', 'Voucher updated successfully.');

        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update voucher: ' . $e->getMessage());
        }
    }

    /**
     * Delete the voucher
     */
    public function destroy(Router $router, Voucher $voucher)
    {
        // Ensure voucher belongs to router
        if ($voucher->router_id !== $router->id) {
            abort(404);
        }

        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        // Check if voucher has generated users
        if ($voucher->users_generated > 0) {
            return redirect()
                ->route('routers.vouchers.index', $router)
                ->with('error', 'Cannot delete a voucher that has generated users.');
        }

        try {
            $voucher->delete();

            return redirect()
                ->route('routers.vouchers.index', $router)
                ->with('success', 'Voucher deleted successfully.');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to delete voucher: ' . $e->getMessage());
        }
    }

    /**
     * Sync profile to MikroTik router
     */
    public function syncProfile(Router $router, Voucher $voucher)
    {
        // Ensure voucher belongs to router
        if ($voucher->router_id !== $router->id) {
            abort(404);
        }

        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {
            $result = $this->hotspotUserService->syncProfileToRouter($voucher);

            if ($result['success']) {
                return back()->with('success', 'Profile synced to router successfully.');
            } else {
                return back()->with('error', 'Failed to sync profile: ' . $result['message']);
            }

        } catch (Exception $e) {
            return back()->with('error', 'Failed to sync profile: ' . $e->getMessage());
        }
    }

    /**
     * Show form to generate hotspot users
     */
    public function showGenerateUsers(Router $router, Voucher $voucher)
    {
        // Ensure voucher belongs to router
        if ($voucher->router_id !== $router->id) {
            abort(404);
        }

        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        $remainingCapacity = $voucher->getRemainingCapacity();

        return view('vouchers.generate-users', compact('router', 'voucher', 'remainingCapacity'));
    }

    /**
     * Generate hotspot users from voucher
     */
    public function generateUsers(Request $request, Router $router, Voucher $voucher)
    {
        // Ensure voucher belongs to router
        if ($voucher->router_id !== $router->id) {
            abort(404);
        }

        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        $validated = $request->validate([
            'count' => 'required|integer|min:1|max:' . $voucher->getRemainingCapacity(),
        ]);

        try {
            $result = $this->hotspotUserService->generateHotspotUsers(
                $voucher,
                $validated['count'],
                auth()->id()
            );

            if ($result['success']) {
                $message = $result['message'];

                if (count($result['errors']) > 0) {
                    return redirect()
                        ->route('routers.vouchers.show', [$router, $voucher])
                        ->with('warning', $message);
                }

                return redirect()
                    ->route('routers.vouchers.show', [$router, $voucher])
                    ->with('success', $message);
            } else {
                return back()
                    ->withInput()
                    ->with('error', $result['message']);
            }

        } catch (Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to generate users: ' . $e->getMessage());
        }
    }

    /**
     * Retry syncing failed users to router
     */
    public function retrySync(Router $router, Voucher $voucher)
    {
        // Ensure voucher belongs to router
        if ($voucher->router_id !== $router->id) {
            abort(404);
        }

        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {
            $result = $this->hotspotUserService->retrySyncFailedUsers($voucher);

            return back()->with('success', $result['message']);

        } catch (Exception $e) {
            return back()->with('error', 'Failed to retry sync: ' . $e->getMessage());
        }
    }

    /**
     * Activate voucher (change status)
     */
    public function activate(Router $router, Voucher $voucher)
    {
        // Ensure voucher belongs to router
        if ($voucher->router_id !== $router->id) {
            abort(404);
        }

        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {
            $voucher->markAsActive();

            return back()->with('success', 'Voucher activated successfully.');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to activate voucher: ' . $e->getMessage());
        }
    }

    /**
     * Disable voucher
     */
    public function disable(Router $router, Voucher $voucher)
    {
        // Ensure voucher belongs to router
        if ($voucher->router_id !== $router->id) {
            abort(404);
        }

        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {
            $voucher->markAsInactive();

            return back()->with('success', 'Voucher disabled successfully.');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to disable voucher: ' . $e->getMessage());
        }
    }

    /**
     * Enable voucher
     */
    public function enable(Router $router, Voucher $voucher)
    {
        // Ensure voucher belongs to router
        if ($voucher->router_id !== $router->id) {
            abort(404);
        }

        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        try {
            $voucher->markAsActive();

            return back()->with('success', 'Voucher enabled successfully.');

        } catch (Exception $e) {
            return back()->with('error', 'Failed to enable voucher: ' . $e->getMessage());
        }
    }

    /**
     * Print voucher users (for distribution)
     */
    public function print(Router $router, Voucher $voucher)
    {
        // Ensure voucher belongs to router
        if ($voucher->router_id !== $router->id) {
            abort(404);
        }

        // Authorization check
        if (!auth()->user()->ownsRouter($router)) {
            abort(403, 'Unauthorized access to this router.');
        }

        $voucher->load(['bandwidthPlan', 'hotspotUsers' => function ($query) {
            $query->orderBy('created_at', 'desc');
        }]);

        return view('vouchers.print', compact('router', 'voucher'));
    }
}
