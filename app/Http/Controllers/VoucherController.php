<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Router;
use App\Models\Customer;
use App\Models\BandwidthPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     * Supports search, filtering by status, router, customer, bandwidth plan, and pagination.
     */
    public function index(Request $request)
    {
        $query = Voucher::with(['bandwidthPlan', 'router', 'customer', 'soldBy']);

        // Search by voucher code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('code', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by router
        if ($request->filled('router_id')) {
            $query->where('router_id', $request->router_id);
        }

        // Filter by customer
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by bandwidth plan
        if ($request->filled('bandwidth_plan_id')) {
            $query->where('bandwidth_plan_id', $request->bandwidth_plan_id);
        }

        // Filter by sold status
        if ($request->filled('sold_status')) {
            if ($request->sold_status === 'sold') {
                $query->whereNotNull('sold_at');
            } elseif ($request->sold_status === 'unsold') {
                $query->whereNull('sold_at');
            }
        }

        // Filter by activated status
        if ($request->filled('activated_status')) {
            if ($request->activated_status === 'activated') {
                $query->whereNotNull('activated_at');
            } elseif ($request->activated_status === 'not_activated') {
                $query->whereNull('activated_at');
            }
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $vouchers = $query->paginate($perPage)->withQueryString();

        // Get filter options for the view
        $routers = Router::active()->orderBy('name')->get();
        $customers = Customer::active()->orderBy('name')->get();
        $bandwidthPlans = BandwidthPlan::active()->orderBy('name')->get();

        return view('vouchers.index', compact('vouchers', 'routers', 'customers', 'bandwidthPlans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $routers = Router::active()->orderBy('name')->get();
        $bandwidthPlans = BandwidthPlan::active()->orderBy('name')->get();

        return view('vouchers.create', compact('routers', 'bandwidthPlans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'bandwidth_plan_id' => 'required|exists:bandwidth_plans,id',
            'router_id' => 'required|exists:routers,id',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        // Generate unique voucher code
        $code = $this->generateUniqueVoucherCode();

        // Get bandwidth plan to set price if not provided
        $bandwidthPlan = BandwidthPlan::findOrFail($validated['bandwidth_plan_id']);
        $price = $validated['price'] ?? $bandwidthPlan->price;

        $voucher = Voucher::create([
            'code' => $code,
            'bandwidth_plan_id' => $validated['bandwidth_plan_id'],
            'router_id' => $validated['router_id'],
            'price' => $price,
            'status' => 'active',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()
            ->route('vouchers.show', $voucher)
            ->with('success', 'Voucher created successfully.');
    }

    /**
     * Display the specified resource.
     * Shows voucher details with usage history.
     */
    public function show(Voucher $voucher)
    {
        $voucher->load([
            'bandwidthPlan',
            'router',
            'customer',
            'soldBy',
            'hotspotUsers.sessions' => function ($query) {
                $query->latest()->limit(10);
            }
        ]);

        // Get usage statistics
        $usageStats = [
            'total_sessions' => $voucher->hotspotUsers->sum(function ($user) {
                return $user->sessions->count();
            }),
            'total_bytes_in' => $voucher->hotspotUsers->sum('bytes_in'),
            'total_bytes_out' => $voucher->hotspotUsers->sum('bytes_out'),
            'total_session_time' => $voucher->hotspotUsers->sum('session_time'),
            'online_users' => $voucher->hotspotUsers->where('is_online', true)->count(),
        ];

        return view('vouchers.show', compact('voucher', 'usageStats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Voucher $voucher)
    {
        $routers = Router::active()->orderBy('name')->get();
        $bandwidthPlans = BandwidthPlan::active()->orderBy('name')->get();
        $customers = Customer::active()->orderBy('name')->get();

        return view('vouchers.edit', compact('voucher', 'routers', 'bandwidthPlans', 'customers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'bandwidth_plan_id' => 'required|exists:bandwidth_plans,id',
            'router_id' => 'required|exists:routers,id',
            'customer_id' => 'nullable|exists:customers,id',
            'price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        $voucher->update($validated);

        return redirect()
            ->route('vouchers.show', $voucher)
            ->with('success', 'Voucher updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     * Only allows deletion if voucher has not been used.
     */
    public function destroy(Voucher $voucher)
    {
        // Check if voucher has been used
        if ($voucher->isUsed() || $voucher->isActivated()) {
            return redirect()
                ->route('vouchers.index')
                ->with('error', 'Cannot delete a voucher that has been used or activated.');
        }

        // Check if voucher has been sold
        if ($voucher->isSold()) {
            return redirect()
                ->route('vouchers.index')
                ->with('error', 'Cannot delete a voucher that has been sold.');
        }

        $code = $voucher->code;
        $voucher->delete();

        return redirect()
            ->route('vouchers.index')
            ->with('success', "Voucher {$code} deleted successfully.");
    }

    /**
     * Show the batch generation form.
     */
    public function generate()
    {
        $routers = Router::active()->orderBy('name')->get();
        $bandwidthPlans = BandwidthPlan::active()->orderBy('name')->get();

        return view('vouchers.generate', compact('routers', 'bandwidthPlans'));
    }

    /**
     * Generate multiple vouchers at once.
     */
    public function batchGenerate(Request $request)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:1000',
            'bandwidth_plan_id' => 'required|exists:bandwidth_plans,id',
            'router_id' => 'required|exists:routers,id',
            'price' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
            'code_prefix' => 'nullable|string|max:10|alpha_dash',
            'code_length' => 'nullable|integer|min:6|max:20',
        ]);

        // Get bandwidth plan to set price if not provided
        $bandwidthPlan = BandwidthPlan::findOrFail($validated['bandwidth_plan_id']);
        $price = $validated['price'] ?? $bandwidthPlan->price;
        $codeLength = $validated['code_length'] ?? 12;
        $codePrefix = $validated['code_prefix'] ?? '';

        $vouchers = [];
        $generatedCodes = [];

        DB::beginTransaction();

        try {
            for ($i = 0; $i < $validated['quantity']; $i++) {
                // Generate unique code
                do {
                    $code = $this->generateVoucherCode($codeLength, $codePrefix);
                } while (
                    in_array($code, $generatedCodes) ||
                    Voucher::where('code', $code)->exists()
                );

                $generatedCodes[] = $code;

                $vouchers[] = [
                    'code' => $code,
                    'bandwidth_plan_id' => $validated['bandwidth_plan_id'],
                    'router_id' => $validated['router_id'],
                    'price' => $price,
                    'status' => 'active',
                    'notes' => $validated['notes'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Bulk insert vouchers
            Voucher::insert($vouchers);

            DB::commit();

            return redirect()
                ->route('vouchers.index', ['search' => $codePrefix])
                ->with('success', "{$validated['quantity']} vouchers generated successfully.");

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Failed to generate vouchers: ' . $e->getMessage());
        }
    }

    /**
     * Activate a voucher for a customer.
     */
    public function activate(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'mac_address' => 'nullable|mac_address',
        ]);

        // Check if voucher can be activated
        if ($voucher->isUsed()) {
            return redirect()
                ->back()
                ->with('error', 'Voucher has already been used.');
        }

        if ($voucher->isDisabled()) {
            return redirect()
                ->back()
                ->with('error', 'Voucher is disabled and cannot be activated.');
        }

        if ($voucher->isExpired()) {
            return redirect()
                ->back()
                ->with('error', 'Voucher has expired.');
        }

        if ($voucher->isActivated()) {
            return redirect()
                ->back()
                ->with('error', 'Voucher has already been activated.');
        }

        DB::beginTransaction();

        try {
            // Update voucher
            $voucher->update([
                'customer_id' => $validated['customer_id'] ?? $voucher->customer_id,
                'mac_address' => $validated['mac_address'] ?? $voucher->mac_address,
            ]);

            // Activate voucher (sets status to 'used', activated_at, and expires_at)
            $voucher->activate();

            DB::commit();

            return redirect()
                ->route('vouchers.show', $voucher)
                ->with('success', 'Voucher activated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()
                ->back()
                ->with('error', 'Failed to activate voucher: ' . $e->getMessage());
        }
    }

    /**
     * Mark voucher as sold.
     */
    public function sell(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'price' => 'nullable|numeric|min:0',
        ]);

        // Check if voucher can be sold
        if ($voucher->isSold()) {
            return redirect()
                ->back()
                ->with('error', 'Voucher has already been sold.');
        }

        if ($voucher->isDisabled()) {
            return redirect()
                ->back()
                ->with('error', 'Disabled voucher cannot be sold.');
        }

        // Update price if provided
        if (isset($validated['price'])) {
            $voucher->price = $validated['price'];
        }

        // Mark as sold
        $voucher->markAsSold(auth()->id(), $validated['customer_id'] ?? null);

        return redirect()
            ->route('vouchers.show', $voucher)
            ->with('success', 'Voucher marked as sold successfully.');
    }

    /**
     * Disable a voucher.
     */
    public function disable(Voucher $voucher)
    {
        if ($voucher->isDisabled()) {
            return redirect()
                ->back()
                ->with('info', 'Voucher is already disabled.');
        }

        $voucher->disable();

        return redirect()
            ->route('vouchers.show', $voucher)
            ->with('success', 'Voucher disabled successfully.');
    }

    /**
     * Enable a voucher.
     */
    public function enable(Voucher $voucher)
    {
        if ($voucher->isExpired()) {
            return redirect()
                ->back()
                ->with('error', 'Cannot enable an expired voucher.');
        }

        if (!$voucher->isDisabled()) {
            return redirect()
                ->back()
                ->with('info', 'Voucher is already enabled.');
        }

        $voucher->enable();

        return redirect()
            ->route('vouchers.show', $voucher)
            ->with('success', 'Voucher enabled successfully.');
    }

    /**
     * Generate printable voucher view.
     */
    public function print(Request $request)
    {
        $validated = $request->validate([
            'voucher_ids' => 'required|array',
            'voucher_ids.*' => 'exists:vouchers,id',
        ]);

        $vouchers = Voucher::with(['bandwidthPlan', 'router'])
            ->whereIn('id', $validated['voucher_ids'])
            ->get();

        return view('vouchers.print', compact('vouchers'));
    }

    /**
     * Generate a unique voucher code.
     */
    private function generateUniqueVoucherCode(int $length = 12, string $prefix = ''): string
    {
        $maxAttempts = 100;
        $attempts = 0;

        do {
            $code = $this->generateVoucherCode($length, $prefix);
            $attempts++;

            if ($attempts >= $maxAttempts) {
                throw new \RuntimeException('Unable to generate unique voucher code after ' . $maxAttempts . ' attempts.');
            }
        } while (Voucher::where('code', $code)->exists());

        return $code;
    }

    /**
     * Generate a voucher code.
     */
    private function generateVoucherCode(int $length = 12, string $prefix = ''): string
    {
        // Generate alphanumeric code (uppercase letters and numbers)
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // Removed confusing characters (I, O, 0, 1)
        $codeLength = $length - strlen($prefix);

        $code = '';
        for ($i = 0; $i < $codeLength; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return strtoupper($prefix . $code);
    }
}
