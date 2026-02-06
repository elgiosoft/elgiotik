<?php

namespace App\Http\Controllers;

use App\Models\BandwidthPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BandwidthPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * Supports search by name, description
     * Filter by active/inactive status
     */
    public function index(Request $request)
    {
        $query = BandwidthPlan::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by active status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->inactive();
            }
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        $plans = $query->paginate(15)->withQueryString();

        return view('bandwidth-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('bandwidth-plans.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * Validates all fields including MikroTik rate limit format
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:bandwidth_plans,name',
            'rate_limit' => ['required', 'string', 'regex:/^\d+[kKmMgG]?$/'],
            'download_speed' => ['required', 'string', 'regex:/^\d+[kKmMgG]?$/'],
            'upload_speed' => ['required', 'string', 'regex:/^\d+[kKmMgG]?$/'],
            'price' => 'required|numeric|min:0|max:999999.99',
            'validity_days' => 'nullable|integer|min:1|max:3650',
            'validity_hours' => 'nullable|integer|min:1|max:87600',
            'data_limit' => 'nullable|integer|min:1',
            'session_timeout' => 'nullable|integer|min:1',
            'idle_timeout' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        // Format rate limits for MikroTik
        $validated['rate_limit'] = $this->formatRateLimit($validated['rate_limit']);
        $validated['download_speed'] = $this->formatRateLimit($validated['download_speed']);
        $validated['upload_speed'] = $this->formatRateLimit($validated['upload_speed']);

        // Set default for is_active if not provided
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $plan = BandwidthPlan::create($validated);

        return redirect()
            ->route('bandwidth-plans.show', $plan)
            ->with('success', 'Bandwidth plan created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * Shows plan details with active users count and total vouchers
     */
    public function show(BandwidthPlan $bandwidthPlan)
    {
        // Load relationships with counts
        $bandwidthPlan->loadCount([
            'vouchers',
            'vouchers as active_vouchers_count' => function ($query) {
                $query->where('status', 'active');
            },
            'vouchers as used_vouchers_count' => function ($query) {
                $query->where('status', 'used');
            },
            'hotspotUsers as active_users_count' => function ($query) {
                $query->where('status', 'active');
            },
        ]);

        return view('bandwidth-plans.show', compact('bandwidthPlan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BandwidthPlan $bandwidthPlan)
    {
        return view('bandwidth-plans.edit', compact('bandwidthPlan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BandwidthPlan $bandwidthPlan)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('bandwidth_plans', 'name')->ignore($bandwidthPlan->id),
            ],
            'rate_limit' => ['required', 'string', 'regex:/^\d+[kKmMgG]?$/'],
            'download_speed' => ['required', 'string', 'regex:/^\d+[kKmMgG]?$/'],
            'upload_speed' => ['required', 'string', 'regex:/^\d+[kKmMgG]?$/'],
            'price' => 'required|numeric|min:0|max:999999.99',
            'validity_days' => 'nullable|integer|min:1|max:3650',
            'validity_hours' => 'nullable|integer|min:1|max:87600',
            'data_limit' => 'nullable|integer|min:1',
            'session_timeout' => 'nullable|integer|min:1',
            'idle_timeout' => 'nullable|integer|min:1',
            'description' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        // Format rate limits for MikroTik
        $validated['rate_limit'] = $this->formatRateLimit($validated['rate_limit']);
        $validated['download_speed'] = $this->formatRateLimit($validated['download_speed']);
        $validated['upload_speed'] = $this->formatRateLimit($validated['upload_speed']);

        // Handle checkbox when not checked
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = false;
        }

        $bandwidthPlan->update($validated);

        return redirect()
            ->route('bandwidth-plans.show', $bandwidthPlan)
            ->with('success', 'Bandwidth plan updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * Only allows deletion if no vouchers exist for this plan
     */
    public function destroy(BandwidthPlan $bandwidthPlan)
    {
        // Check if plan has any vouchers
        $vouchersCount = $bandwidthPlan->vouchers()->count();

        if ($vouchersCount > 0) {
            return redirect()
                ->route('bandwidth-plans.index')
                ->with('error', "Cannot delete bandwidth plan. It has {$vouchersCount} voucher(s) associated with it.");
        }

        // Check if plan has any hotspot users
        $usersCount = $bandwidthPlan->hotspotUsers()->count();

        if ($usersCount > 0) {
            return redirect()
                ->route('bandwidth-plans.index')
                ->with('error', "Cannot delete bandwidth plan. It has {$usersCount} hotspot user(s) associated with it.");
        }

        $planName = $bandwidthPlan->name;
        $bandwidthPlan->delete();

        return redirect()
            ->route('bandwidth-plans.index')
            ->with('success', "Bandwidth plan '{$planName}' deleted successfully.");
    }

    /**
     * Format rate limit string for MikroTik compatibility
     *
     * Converts user input like "512k", "1M", "100" to proper MikroTik format
     * Examples:
     *   512 -> 512k
     *   512k -> 512k
     *   1M -> 1M
     *   10m -> 10M
     *   2G -> 2G
     *
     * @param string $rateLimit
     * @return string
     */
    protected function formatRateLimit(string $rateLimit): string
    {
        // Trim whitespace
        $rateLimit = trim($rateLimit);

        // If it's just a number without suffix, assume kilobits and add 'k'
        if (is_numeric($rateLimit)) {
            return $rateLimit . 'k';
        }

        // Get the numeric part and unit
        preg_match('/^(\d+)([kKmMgG])?$/', $rateLimit, $matches);

        if (empty($matches)) {
            return $rateLimit; // Return as-is if pattern doesn't match
        }

        $number = $matches[1];
        $unit = $matches[2] ?? 'k';

        // Normalize unit to uppercase
        $unit = strtoupper($unit);

        return $number . $unit;
    }

    /**
     * Convert formatted rate limit to bytes per second
     * Useful for comparisons and calculations
     *
     * @param string $rateLimit
     * @return int
     */
    protected function rateLimitToBytes(string $rateLimit): int
    {
        preg_match('/^(\d+)([kKmMgG])?$/', $rateLimit, $matches);

        if (empty($matches)) {
            return 0;
        }

        $number = (int) $matches[1];
        $unit = strtoupper($matches[2] ?? 'K');

        $multipliers = [
            'K' => 1024,
            'M' => 1024 * 1024,
            'G' => 1024 * 1024 * 1024,
        ];

        return $number * ($multipliers[$unit] ?? 1024);
    }

    /**
     * Convert bytes to human readable rate limit
     *
     * @param int $bytes
     * @return string
     */
    protected function bytesToRateLimit(int $bytes): string
    {
        $units = ['K', 'M', 'G'];
        $divisor = 1024;

        foreach ($units as $unit) {
            if ($bytes < $divisor * 1024) {
                return round($bytes / $divisor) . $unit;
            }
            $divisor *= 1024;
        }

        return round($bytes / $divisor) . 'G';
    }
}
