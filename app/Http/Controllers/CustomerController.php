<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Features:
     * - Search by name, email, phone, location
     * - Filter by status (active/inactive)
     * - Pagination (15 per page)
     */
    public function index(Request $request)
    {
        $query = Customer::with('createdBy');

        // Search functionality
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Filter by status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->inactive();
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $customers = $query->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validation:
     * - name: required, max 255 chars
     * - email: optional, valid email, unique, max 255 chars
     * - phone: required, max 20 chars
     * - address: optional
     * - location: optional, max 255 chars
     * - notes: optional
     * - is_active: boolean, default true
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:customers,email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        // Add created_by from authenticated user
        $validated['created_by'] = auth()->id();

        // Set default for is_active if not provided
        $validated['is_active'] = $validated['is_active'] ?? true;

        $customer = Customer::create($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * Shows:
     * - Customer details
     * - All vouchers (with pagination)
     * - All hotspot users (with pagination)
     * - Purchase history (sold vouchers)
     * - Spending statistics
     */
    public function show(Customer $customer)
    {
        // Load relationships with pagination-ready queries
        $customer->load([
            'createdBy',
            'vouchers' => function ($query) {
                $query->with(['bandwidthPlan', 'router', 'soldBy'])
                      ->latest()
                      ->take(10);
            },
            'hotspotUsers' => function ($query) {
                $query->with(['bandwidthPlan', 'router'])
                      ->latest()
                      ->take(10);
            }
        ]);

        // Get statistics
        $statistics = [
            'total_vouchers' => $customer->getTotalVouchers(),
            'active_vouchers' => $customer->getActiveVouchers(),
            'used_vouchers' => $customer->getUsedVouchers(),
            'total_spent' => $customer->getTotalSpent(),
            'active_hotspot_users' => $customer->getActiveHotspotUsersCount(),
            'online_hotspot_users' => $customer->getOnlineHotspotUsersCount(),
        ];

        // Get recent purchases (sold vouchers)
        $recentPurchases = $customer->vouchers()
            ->with(['bandwidthPlan', 'router', 'soldBy'])
            ->whereNotNull('sold_at')
            ->latest('sold_at')
            ->take(5)
            ->get();

        return view('customers.show', compact('customer', 'statistics', 'recentPurchases'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     *
     * Validation:
     * - name: required, max 255 chars
     * - email: optional, valid email, unique (except current customer), max 255 chars
     * - phone: required, max 20 chars
     * - address: optional
     * - location: optional, max 255 chars
     * - notes: optional
     * - is_active: boolean
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('customers', 'email')->ignore($customer->id)
            ],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'is_active' => ['boolean'],
        ]);

        $customer->update($validated);

        return redirect()
            ->route('customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Checks:
     * - Cannot delete if customer has active vouchers
     * - Cannot delete if customer has active hotspot users
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has active vouchers
        $activeVouchersCount = $customer->vouchers()
            ->where('status', 'active')
            ->count();

        if ($activeVouchersCount > 0) {
            return back()->with('error',
                "Cannot delete customer. They have {$activeVouchersCount} active voucher(s)."
            );
        }

        // Check if customer has active hotspot users
        $activeHotspotUsersCount = $customer->hotspotUsers()
            ->where('status', 'active')
            ->count();

        if ($activeHotspotUsersCount > 0) {
            return back()->with('error',
                "Cannot delete customer. They have {$activeHotspotUsersCount} active hotspot user(s)."
            );
        }

        // Safe to delete
        $customer->delete();

        return redirect()
            ->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Display customer's complete purchase history.
     *
     * Shows all vouchers that were sold to this customer,
     * ordered by purchase date (sold_at).
     */
    public function purchaseHistory(Customer $customer)
    {
        $purchases = $customer->vouchers()
            ->with(['bandwidthPlan', 'router', 'soldBy'])
            ->whereNotNull('sold_at')
            ->latest('sold_at')
            ->paginate(20);

        // Calculate totals
        $totalPurchases = $purchases->total();
        $totalSpent = $customer->getTotalSpent();

        // Group purchases by month for statistics
        $purchasesByMonth = $customer->vouchers()
            ->whereNotNull('sold_at')
            ->select(
                DB::raw('DATE_FORMAT(sold_at, "%Y-%m") as month'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(price) as total')
            )
            ->groupBy('month')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get();

        return view('customers.purchase-history', compact(
            'customer',
            'purchases',
            'totalPurchases',
            'totalSpent',
            'purchasesByMonth'
        ));
    }

    /**
     * Display customer's active services.
     *
     * Shows:
     * - Active vouchers
     * - Active hotspot users
     * - Online hotspot users
     */
    public function activeServices(Customer $customer)
    {
        // Get active vouchers with related data
        $activeVouchers = $customer->vouchers()
            ->with(['bandwidthPlan', 'router'])
            ->where('status', 'active')
            ->latest()
            ->paginate(15, ['*'], 'vouchers_page');

        // Get active hotspot users with related data
        $activeHotspotUsers = $customer->hotspotUsers()
            ->with(['bandwidthPlan', 'router'])
            ->where('status', 'active')
            ->latest()
            ->paginate(15, ['*'], 'hotspot_page');

        // Get online hotspot users count
        $onlineUsersCount = $customer->hotspotUsers()
            ->where('is_online', true)
            ->count();

        return view('customers.active-services', compact(
            'customer',
            'activeVouchers',
            'activeHotspotUsers',
            'onlineUsersCount'
        ));
    }
}
