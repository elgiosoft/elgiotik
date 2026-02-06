<?php

namespace App\Http\Controllers;

use App\Models\BandwidthPlan;
use App\Models\Customer;
use App\Models\HotspotUser;
use App\Models\UserSession;
use App\Models\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display the reports dashboard with available report types.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $quickStats = [
            'total_revenue' => Voucher::sold()->sum('price'),
            'monthly_revenue' => Voucher::sold()
                ->whereYear('sold_at', now()->year)
                ->whereMonth('sold_at', now()->month)
                ->sum('price'),
            'total_customers' => Customer::count(),
            'active_customers' => Customer::active()->count(),
            'vouchers_sold' => Voucher::sold()->count(),
            'total_data_consumed' => HotspotUser::sum('bytes_in') + HotspotUser::sum('bytes_out'),
            'total_sessions' => UserSession::count(),
            'active_users' => HotspotUser::online()->count(),
        ];

        $reportTypes = [
            [
                'name' => 'Sales Report',
                'description' => 'View sales data with date range filters and grouping options',
                'icon' => 'dollar-sign',
                'route' => 'reports.sales',
                'color' => 'success',
            ],
            [
                'name' => 'Revenue Analytics',
                'description' => 'Detailed revenue breakdown by bandwidth plans with charts',
                'icon' => 'trending-up',
                'route' => 'reports.revenue',
                'color' => 'primary',
            ],
            [
                'name' => 'Customer Reports',
                'description' => 'Customer statistics, new customers, and top spenders',
                'icon' => 'users',
                'route' => 'reports.customers',
                'color' => 'info',
            ],
            [
                'name' => 'Usage Reports',
                'description' => 'Data consumption, session times, and usage patterns',
                'icon' => 'activity',
                'route' => 'reports.usage',
                'color' => 'warning',
            ],
            [
                'name' => 'Voucher Statistics',
                'description' => 'Voucher lifecycle tracking and status reports',
                'icon' => 'ticket',
                'route' => 'reports.vouchers',
                'color' => 'secondary',
            ],
        ];

        return view('reports.index', compact('quickStats', 'reportTypes'));
    }

    /**
     * Display sales report with date range filter and grouping options.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function sales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $groupBy = $request->input('group_by', 'day'); // day, week, month
        $format = $request->input('format', 'html'); // html, json

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Determine date format and grouping based on selection
        $dateFormat = match($groupBy) {
            'day' => '%Y-%m-%d',
            'week' => '%Y-%u',
            'month' => '%Y-%m',
            default => '%Y-%m-%d',
        };

        // Sales grouped by period
        $salesData = Voucher::select(
            DB::raw("DATE_FORMAT(sold_at, '{$dateFormat}') as period"),
            DB::raw('COUNT(*) as total_sales'),
            DB::raw('SUM(price) as total_revenue'),
            DB::raw('AVG(price) as average_price')
        )
            ->whereNotNull('sold_at')
            ->whereBetween('sold_at', [$startDateTime, $endDateTime])
            ->groupBy('period')
            ->orderBy('period')
            ->get()
            ->map(function ($item) use ($groupBy) {
                // Format period label
                if ($groupBy === 'week') {
                    $item->period_label = 'Week ' . substr($item->period, 5);
                } elseif ($groupBy === 'month') {
                    $item->period_label = Carbon::createFromFormat('Y-m', $item->period)->format('F Y');
                } else {
                    $item->period_label = Carbon::parse($item->period)->format('M d, Y');
                }
                return $item;
            });

        // Sales by bandwidth plan
        $salesByPlan = Voucher::select(
            'bandwidth_plans.name as plan_name',
            DB::raw('COUNT(vouchers.id) as total_sales'),
            DB::raw('SUM(vouchers.price) as total_revenue')
        )
            ->join('bandwidth_plans', 'vouchers.bandwidth_plan_id', '=', 'bandwidth_plans.id')
            ->whereNotNull('vouchers.sold_at')
            ->whereBetween('vouchers.sold_at', [$startDateTime, $endDateTime])
            ->groupBy('bandwidth_plans.id', 'bandwidth_plans.name')
            ->orderByDesc('total_revenue')
            ->get();

        // Sales by user (seller)
        $salesByUser = Voucher::select(
            'users.name as seller_name',
            DB::raw('COUNT(vouchers.id) as total_sales'),
            DB::raw('SUM(vouchers.price) as total_revenue')
        )
            ->join('users', 'vouchers.sold_by', '=', 'users.id')
            ->whereNotNull('vouchers.sold_at')
            ->whereBetween('vouchers.sold_at', [$startDateTime, $endDateTime])
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total_revenue')
            ->get();

        // Summary statistics
        $summary = [
            'total_sales' => $salesData->sum('total_sales'),
            'total_revenue' => $salesData->sum('total_revenue'),
            'average_sale_value' => $salesData->sum('total_sales') > 0
                ? $salesData->sum('total_revenue') / $salesData->sum('total_sales')
                : 0,
            'best_day' => $salesData->sortByDesc('total_revenue')->first(),
            'period_start' => $startDateTime->format('M d, Y'),
            'period_end' => $endDateTime->format('M d, Y'),
        ];

        // Chart data
        $chartData = [
            'labels' => $salesData->pluck('period_label')->toArray(),
            'revenue' => $salesData->pluck('total_revenue')->toArray(),
            'sales_count' => $salesData->pluck('total_sales')->toArray(),
        ];

        if ($format === 'json') {
            return response()->json([
                'sales_data' => $salesData,
                'sales_by_plan' => $salesByPlan,
                'sales_by_user' => $salesByUser,
                'summary' => $summary,
                'chart_data' => $chartData,
            ]);
        }

        return view('reports.sales', compact(
            'salesData',
            'salesByPlan',
            'salesByUser',
            'summary',
            'chartData',
            'startDate',
            'endDate',
            'groupBy'
        ));
    }

    /**
     * Display revenue analytics with breakdown by bandwidth plan.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function revenue(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $format = $request->input('format', 'html');

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Total revenue statistics
        $totalRevenue = Voucher::whereNotNull('sold_at')
            ->whereBetween('sold_at', [$startDateTime, $endDateTime])
            ->sum('price');

        $previousPeriodStart = $startDateTime->copy()->subDays($startDateTime->diffInDays($endDateTime) + 1);
        $previousPeriodEnd = $startDateTime->copy()->subDay();

        $previousRevenue = Voucher::whereNotNull('sold_at')
            ->whereBetween('sold_at', [$previousPeriodStart, $previousPeriodEnd])
            ->sum('price');

        $revenueGrowth = $previousRevenue > 0
            ? (($totalRevenue - $previousRevenue) / $previousRevenue) * 100
            : 0;

        // Revenue by bandwidth plan
        $revenueByPlan = BandwidthPlan::select(
            'bandwidth_plans.id',
            'bandwidth_plans.name',
            'bandwidth_plans.price as plan_price',
            DB::raw('COUNT(vouchers.id) as vouchers_sold'),
            DB::raw('SUM(vouchers.price) as total_revenue'),
            DB::raw('ROUND((SUM(vouchers.price) / ' . ($totalRevenue ?: 1) . ') * 100, 2) as revenue_percentage')
        )
            ->leftJoin('vouchers', function($join) use ($startDateTime, $endDateTime) {
                $join->on('bandwidth_plans.id', '=', 'vouchers.bandwidth_plan_id')
                    ->whereNotNull('vouchers.sold_at')
                    ->whereBetween('vouchers.sold_at', [$startDateTime, $endDateTime]);
            })
            ->groupBy('bandwidth_plans.id', 'bandwidth_plans.name', 'bandwidth_plans.price')
            ->orderByDesc('total_revenue')
            ->get();

        // Revenue trend (daily)
        $revenueTrend = Voucher::select(
            DB::raw('DATE(sold_at) as date'),
            DB::raw('SUM(price) as revenue'),
            DB::raw('COUNT(id) as sales_count')
        )
            ->whereNotNull('sold_at')
            ->whereBetween('sold_at', [$startDateTime, $endDateTime])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->date_label = Carbon::parse($item->date)->format('M d');
                return $item;
            });

        // Revenue by hour of day (to identify peak hours)
        $revenueByHour = Voucher::select(
            DB::raw('HOUR(sold_at) as hour'),
            DB::raw('SUM(price) as revenue'),
            DB::raw('COUNT(id) as sales_count')
        )
            ->whereNotNull('sold_at')
            ->whereBetween('sold_at', [$startDateTime, $endDateTime])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(function ($item) {
                $item->hour_label = sprintf('%02d:00', $item->hour);
                return $item;
            });

        // Summary statistics
        $summary = [
            'total_revenue' => $totalRevenue,
            'previous_revenue' => $previousRevenue,
            'revenue_growth' => round($revenueGrowth, 2),
            'average_daily_revenue' => $startDateTime->diffInDays($endDateTime) > 0
                ? $totalRevenue / ($startDateTime->diffInDays($endDateTime) + 1)
                : $totalRevenue,
            'total_sales' => Voucher::whereNotNull('sold_at')
                ->whereBetween('sold_at', [$startDateTime, $endDateTime])
                ->count(),
            'average_transaction_value' => Voucher::whereNotNull('sold_at')
                ->whereBetween('sold_at', [$startDateTime, $endDateTime])
                ->avg('price'),
        ];

        // Chart data
        $chartData = [
            'trend' => [
                'labels' => $revenueTrend->pluck('date_label')->toArray(),
                'revenue' => $revenueTrend->pluck('revenue')->toArray(),
                'sales' => $revenueTrend->pluck('sales_count')->toArray(),
            ],
            'by_plan' => [
                'labels' => $revenueByPlan->pluck('name')->toArray(),
                'revenue' => $revenueByPlan->pluck('total_revenue')->toArray(),
                'percentages' => $revenueByPlan->pluck('revenue_percentage')->toArray(),
            ],
            'by_hour' => [
                'labels' => $revenueByHour->pluck('hour_label')->toArray(),
                'revenue' => $revenueByHour->pluck('revenue')->toArray(),
            ],
        ];

        if ($format === 'json') {
            return response()->json([
                'summary' => $summary,
                'revenue_by_plan' => $revenueByPlan,
                'revenue_trend' => $revenueTrend,
                'revenue_by_hour' => $revenueByHour,
                'chart_data' => $chartData,
            ]);
        }

        return view('reports.revenue', compact(
            'summary',
            'revenueByPlan',
            'revenueTrend',
            'revenueByHour',
            'chartData',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display customer reports with new customers and top spenders.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function customers(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $format = $request->input('format', 'html');

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // New customers in period
        $newCustomers = Customer::whereBetween('created_at', [$startDateTime, $endDateTime])
            ->withCount([
                'vouchers as total_vouchers',
                'vouchers as sold_vouchers' => function ($query) {
                    $query->whereNotNull('sold_at');
                }
            ])
            ->with('createdBy:id,name')
            ->latest()
            ->get();

        // Top customers by spending
        $topCustomers = Customer::select('customers.*')
            ->selectSub(function ($query) use ($startDateTime, $endDateTime) {
                $query->from('vouchers')
                    ->whereColumn('vouchers.customer_id', 'customers.id')
                    ->whereNotNull('sold_at')
                    ->whereBetween('sold_at', [$startDateTime, $endDateTime])
                    ->selectRaw('SUM(price)');
            }, 'total_spent')
            ->selectSub(function ($query) use ($startDateTime, $endDateTime) {
                $query->from('vouchers')
                    ->whereColumn('vouchers.customer_id', 'customers.id')
                    ->whereNotNull('sold_at')
                    ->whereBetween('sold_at', [$startDateTime, $endDateTime])
                    ->selectRaw('COUNT(*)');
            }, 'vouchers_purchased')
            ->having('total_spent', '>', 0)
            ->orderByDesc('total_spent')
            ->limit(20)
            ->get();

        // Customer acquisition trend
        $customerTrend = Customer::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as new_customers')
        )
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->date_label = Carbon::parse($item->date)->format('M d');
                return $item;
            });

        // Customer activity breakdown
        $customerActivity = [
            'active_with_vouchers' => Customer::active()
                ->whereHas('vouchers', function ($query) {
                    $query->where('status', 'active');
                })
                ->count(),
            'active_without_vouchers' => Customer::active()
                ->whereDoesntHave('vouchers', function ($query) {
                    $query->where('status', 'active');
                })
                ->count(),
            'inactive' => Customer::where('is_active', false)->count(),
            'with_online_users' => Customer::whereHas('hotspotUsers', function ($query) {
                $query->where('is_online', true);
            })->count(),
        ];

        // Summary statistics
        $summary = [
            'total_customers' => Customer::count(),
            'new_customers' => $newCustomers->count(),
            'active_customers' => Customer::active()->count(),
            'customers_with_purchases' => $topCustomers->count(),
            'total_revenue_from_customers' => $topCustomers->sum('total_spent'),
            'average_customer_value' => $topCustomers->count() > 0
                ? $topCustomers->sum('total_spent') / $topCustomers->count()
                : 0,
        ];

        // Chart data
        $chartData = [
            'acquisition_trend' => [
                'labels' => $customerTrend->pluck('date_label')->toArray(),
                'data' => $customerTrend->pluck('new_customers')->toArray(),
            ],
            'top_spenders' => [
                'labels' => $topCustomers->take(10)->pluck('name')->toArray(),
                'data' => $topCustomers->take(10)->pluck('total_spent')->toArray(),
            ],
            'activity_breakdown' => [
                'labels' => ['Active with Vouchers', 'Active without Vouchers', 'Inactive', 'With Online Users'],
                'data' => [
                    $customerActivity['active_with_vouchers'],
                    $customerActivity['active_without_vouchers'],
                    $customerActivity['inactive'],
                    $customerActivity['with_online_users'],
                ],
            ],
        ];

        if ($format === 'json') {
            return response()->json([
                'summary' => $summary,
                'new_customers' => $newCustomers,
                'top_customers' => $topCustomers,
                'customer_trend' => $customerTrend,
                'customer_activity' => $customerActivity,
                'chart_data' => $chartData,
            ]);
        }

        return view('reports.customers', compact(
            'summary',
            'newCustomers',
            'topCustomers',
            'customerTrend',
            'customerActivity',
            'chartData',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display usage reports showing data consumption and session patterns.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function usage(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $format = $request->input('format', 'html');

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Total data consumption
        $totalBytesIn = UserSession::whereBetween('started_at', [$startDateTime, $endDateTime])
            ->sum('bytes_in');
        $totalBytesOut = UserSession::whereBetween('started_at', [$startDateTime, $endDateTime])
            ->sum('bytes_out');
        $totalBytes = $totalBytesIn + $totalBytesOut;

        // Data consumption by bandwidth plan
        $usageByPlan = BandwidthPlan::select(
            'bandwidth_plans.id',
            'bandwidth_plans.name',
            DB::raw('COUNT(DISTINCT user_sessions.hotspot_user_id) as unique_users'),
            DB::raw('SUM(user_sessions.bytes_in) as total_bytes_in'),
            DB::raw('SUM(user_sessions.bytes_out) as total_bytes_out'),
            DB::raw('SUM(user_sessions.bytes_in + user_sessions.bytes_out) as total_bytes'),
            DB::raw('SUM(user_sessions.duration) as total_duration'),
            DB::raw('COUNT(user_sessions.id) as total_sessions')
        )
            ->leftJoin('hotspot_users', 'bandwidth_plans.id', '=', 'hotspot_users.bandwidth_plan_id')
            ->leftJoin('user_sessions', function($join) use ($startDateTime, $endDateTime) {
                $join->on('hotspot_users.id', '=', 'user_sessions.hotspot_user_id')
                    ->whereBetween('user_sessions.started_at', [$startDateTime, $endDateTime]);
            })
            ->groupBy('bandwidth_plans.id', 'bandwidth_plans.name')
            ->orderByDesc('total_bytes')
            ->get()
            ->map(function ($item) {
                $item->total_bytes_formatted = $this->formatBytes($item->total_bytes ?? 0);
                $item->total_bytes_in_formatted = $this->formatBytes($item->total_bytes_in ?? 0);
                $item->total_bytes_out_formatted = $this->formatBytes($item->total_bytes_out ?? 0);
                $item->total_duration_formatted = $this->formatDuration($item->total_duration ?? 0);
                $item->average_session_duration = $item->total_sessions > 0
                    ? $this->formatDuration(($item->total_duration ?? 0) / $item->total_sessions)
                    : '00:00:00';
                return $item;
            });

        // Usage by time of day
        $usageByHour = UserSession::select(
            DB::raw('HOUR(started_at) as hour'),
            DB::raw('COUNT(*) as sessions'),
            DB::raw('SUM(bytes_in + bytes_out) as total_bytes'),
            DB::raw('AVG(duration) as average_duration')
        )
            ->whereBetween('started_at', [$startDateTime, $endDateTime])
            ->groupBy('hour')
            ->orderBy('hour')
            ->get()
            ->map(function ($item) {
                $item->hour_label = sprintf('%02d:00', $item->hour);
                $item->total_bytes_formatted = $this->formatBytes($item->total_bytes);
                return $item;
            });

        // Usage by day of week
        $usageByDayOfWeek = UserSession::select(
            DB::raw('DAYOFWEEK(started_at) as day_of_week'),
            DB::raw('COUNT(*) as sessions'),
            DB::raw('SUM(bytes_in + bytes_out) as total_bytes'),
            DB::raw('COUNT(DISTINCT hotspot_user_id) as unique_users')
        )
            ->whereBetween('started_at', [$startDateTime, $endDateTime])
            ->groupBy('day_of_week')
            ->orderBy('day_of_week')
            ->get()
            ->map(function ($item) {
                $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                $item->day_name = $days[$item->day_of_week - 1];
                $item->total_bytes_formatted = $this->formatBytes($item->total_bytes);
                return $item;
            });

        // Daily usage trend
        $dailyUsageTrend = UserSession::select(
            DB::raw('DATE(started_at) as date'),
            DB::raw('COUNT(*) as sessions'),
            DB::raw('SUM(bytes_in + bytes_out) as total_bytes'),
            DB::raw('COUNT(DISTINCT hotspot_user_id) as unique_users')
        )
            ->whereBetween('started_at', [$startDateTime, $endDateTime])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->date_label = Carbon::parse($item->date)->format('M d');
                $item->total_bytes_mb = round($item->total_bytes / (1024 * 1024), 2);
                return $item;
            });

        // Top users by data consumption
        $topUsers = HotspotUser::select(
            'hotspot_users.id',
            'hotspot_users.username',
            'customers.name as customer_name',
            'bandwidth_plans.name as plan_name',
            DB::raw('SUM(user_sessions.bytes_in + user_sessions.bytes_out) as total_bytes'),
            DB::raw('SUM(user_sessions.duration) as total_duration'),
            DB::raw('COUNT(user_sessions.id) as total_sessions')
        )
            ->leftJoin('customers', 'hotspot_users.customer_id', '=', 'customers.id')
            ->leftJoin('bandwidth_plans', 'hotspot_users.bandwidth_plan_id', '=', 'bandwidth_plans.id')
            ->leftJoin('user_sessions', function($join) use ($startDateTime, $endDateTime) {
                $join->on('hotspot_users.id', '=', 'user_sessions.hotspot_user_id')
                    ->whereBetween('user_sessions.started_at', [$startDateTime, $endDateTime]);
            })
            ->groupBy('hotspot_users.id', 'hotspot_users.username', 'customers.name', 'bandwidth_plans.name')
            ->orderByDesc('total_bytes')
            ->limit(20)
            ->get()
            ->map(function ($item) {
                $item->total_bytes_formatted = $this->formatBytes($item->total_bytes ?? 0);
                $item->total_duration_formatted = $this->formatDuration($item->total_duration ?? 0);
                return $item;
            });

        // Summary statistics
        $summary = [
            'total_data_consumed' => $this->formatBytes($totalBytes),
            'total_download' => $this->formatBytes($totalBytesIn),
            'total_upload' => $this->formatBytes($totalBytesOut),
            'total_sessions' => UserSession::whereBetween('started_at', [$startDateTime, $endDateTime])->count(),
            'unique_users' => UserSession::whereBetween('started_at', [$startDateTime, $endDateTime])
                ->distinct('hotspot_user_id')
                ->count(),
            'average_session_duration' => $this->formatDuration(
                UserSession::whereBetween('started_at', [$startDateTime, $endDateTime])->avg('duration') ?? 0
            ),
            'peak_hour' => $usageByHour->sortByDesc('sessions')->first(),
            'peak_day' => $usageByDayOfWeek->sortByDesc('sessions')->first(),
        ];

        // Chart data
        $chartData = [
            'daily_trend' => [
                'labels' => $dailyUsageTrend->pluck('date_label')->toArray(),
                'data' => $dailyUsageTrend->pluck('total_bytes_mb')->toArray(),
                'users' => $dailyUsageTrend->pluck('unique_users')->toArray(),
            ],
            'by_hour' => [
                'labels' => $usageByHour->pluck('hour_label')->toArray(),
                'sessions' => $usageByHour->pluck('sessions')->toArray(),
            ],
            'by_day_of_week' => [
                'labels' => $usageByDayOfWeek->pluck('day_name')->toArray(),
                'sessions' => $usageByDayOfWeek->pluck('sessions')->toArray(),
            ],
            'by_plan' => [
                'labels' => $usageByPlan->pluck('name')->toArray(),
                'data' => $usageByPlan->pluck('total_bytes')->map(fn($b) => round(($b ?? 0) / (1024 * 1024), 2))->toArray(),
            ],
        ];

        if ($format === 'json') {
            return response()->json([
                'summary' => $summary,
                'usage_by_plan' => $usageByPlan,
                'usage_by_hour' => $usageByHour,
                'usage_by_day_of_week' => $usageByDayOfWeek,
                'daily_usage_trend' => $dailyUsageTrend,
                'top_users' => $topUsers,
                'chart_data' => $chartData,
            ]);
        }

        return view('reports.usage', compact(
            'summary',
            'usageByPlan',
            'usageByHour',
            'usageByDayOfWeek',
            'dailyUsageTrend',
            'topUsers',
            'chartData',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display voucher statistics with lifecycle tracking.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View|\Illuminate\Http\JsonResponse
     */
    public function vouchers(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $format = $request->input('format', 'html');

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Voucher statistics by status
        $voucherStats = [
            'total' => Voucher::count(),
            'generated_in_period' => Voucher::whereBetween('created_at', [$startDateTime, $endDateTime])->count(),
            'sold' => Voucher::sold()
                ->whereBetween('sold_at', [$startDateTime, $endDateTime])
                ->count(),
            'used' => Voucher::used()
                ->whereBetween('activated_at', [$startDateTime, $endDateTime])
                ->count(),
            'expired' => Voucher::expired()
                ->whereBetween('expires_at', [$startDateTime, $endDateTime])
                ->count(),
            'active' => Voucher::active()->count(),
            'disabled' => Voucher::where('status', 'disabled')->count(),
            'unsold' => Voucher::unsold()->count(),
        ];

        // Voucher lifecycle metrics
        $lifecycleMetrics = Voucher::select(
            'vouchers.*',
            DB::raw('TIMESTAMPDIFF(SECOND, created_at, sold_at) as time_to_sale'),
            DB::raw('TIMESTAMPDIFF(SECOND, sold_at, activated_at) as time_to_activation')
        )
            ->whereNotNull('sold_at')
            ->whereBetween('sold_at', [$startDateTime, $endDateTime])
            ->get();

        $averageTimeToSale = $lifecycleMetrics->whereNotNull('time_to_sale')->avg('time_to_sale') ?? 0;
        $averageTimeToActivation = $lifecycleMetrics->whereNotNull('time_to_activation')->avg('time_to_activation') ?? 0;

        // Vouchers by bandwidth plan
        $vouchersByPlan = BandwidthPlan::select(
            'bandwidth_plans.id',
            'bandwidth_plans.name',
            'bandwidth_plans.price',
            DB::raw('COUNT(vouchers.id) as total_vouchers'),
            DB::raw('SUM(CASE WHEN vouchers.status = "active" THEN 1 ELSE 0 END) as active_vouchers'),
            DB::raw('SUM(CASE WHEN vouchers.status = "used" THEN 1 ELSE 0 END) as used_vouchers'),
            DB::raw('SUM(CASE WHEN vouchers.status = "expired" THEN 1 ELSE 0 END) as expired_vouchers'),
            DB::raw('SUM(CASE WHEN vouchers.sold_at IS NOT NULL THEN 1 ELSE 0 END) as sold_vouchers'),
            DB::raw('SUM(CASE WHEN vouchers.sold_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as sold_in_period')
        )
            ->leftJoin('vouchers', 'bandwidth_plans.id', '=', 'vouchers.bandwidth_plan_id')
            ->groupBy('bandwidth_plans.id', 'bandwidth_plans.name', 'bandwidth_plans.price')
            ->orderByDesc('total_vouchers')
            ->setBindings([$startDateTime, $endDateTime])
            ->get()
            ->map(function ($item) {
                $item->sale_rate = $item->total_vouchers > 0
                    ? round(($item->sold_vouchers / $item->total_vouchers) * 100, 2)
                    : 0;
                return $item;
            });

        // Voucher generation trend
        $generationTrend = Voucher::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as generated')
        )
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->date_label = Carbon::parse($item->date)->format('M d');
                return $item;
            });

        // Voucher sales trend
        $salesTrend = Voucher::select(
            DB::raw('DATE(sold_at) as date'),
            DB::raw('COUNT(*) as sold')
        )
            ->whereNotNull('sold_at')
            ->whereBetween('sold_at', [$startDateTime, $endDateTime])
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->date_label = Carbon::parse($item->date)->format('M d');
                return $item;
            });

        // Expiration forecast (next 30 days)
        $expirationForecast = Voucher::select(
            DB::raw('DATE(expires_at) as date'),
            DB::raw('COUNT(*) as expiring')
        )
            ->whereNotNull('expires_at')
            ->whereBetween('expires_at', [now()->startOfDay(), now()->addDays(30)->endOfDay()])
            ->where('status', '!=', 'expired')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                $item->date_label = Carbon::parse($item->date)->format('M d');
                return $item;
            });

        // Summary statistics
        $summary = [
            'total_vouchers' => $voucherStats['total'],
            'generated_in_period' => $voucherStats['generated_in_period'],
            'sold_in_period' => $voucherStats['sold'],
            'used_in_period' => $voucherStats['used'],
            'expired_in_period' => $voucherStats['expired'],
            'sale_rate' => $voucherStats['total'] > 0
                ? round(($voucherStats['sold'] / $voucherStats['generated_in_period']) * 100, 2)
                : 0,
            'activation_rate' => $voucherStats['sold'] > 0
                ? round(($voucherStats['used'] / $voucherStats['sold']) * 100, 2)
                : 0,
            'average_time_to_sale' => $this->formatDuration($averageTimeToSale),
            'average_time_to_activation' => $this->formatDuration($averageTimeToActivation),
            'expiring_soon' => Voucher::whereNotNull('expires_at')
                ->whereBetween('expires_at', [now(), now()->addDays(7)])
                ->where('status', '!=', 'expired')
                ->count(),
        ];

        // Chart data
        $chartData = [
            'generation_trend' => [
                'labels' => $generationTrend->pluck('date_label')->toArray(),
                'data' => $generationTrend->pluck('generated')->toArray(),
            ],
            'sales_trend' => [
                'labels' => $salesTrend->pluck('date_label')->toArray(),
                'data' => $salesTrend->pluck('sold')->toArray(),
            ],
            'expiration_forecast' => [
                'labels' => $expirationForecast->pluck('date_label')->toArray(),
                'data' => $expirationForecast->pluck('expiring')->toArray(),
            ],
            'status_distribution' => [
                'labels' => ['Active', 'Used', 'Expired', 'Disabled', 'Unsold'],
                'data' => [
                    $voucherStats['active'],
                    $voucherStats['used'],
                    $voucherStats['expired'],
                    $voucherStats['disabled'],
                    $voucherStats['unsold'],
                ],
            ],
            'by_plan' => [
                'labels' => $vouchersByPlan->pluck('name')->toArray(),
                'sold' => $vouchersByPlan->pluck('sold_in_period')->toArray(),
                'active' => $vouchersByPlan->pluck('active_vouchers')->toArray(),
            ],
        ];

        if ($format === 'json') {
            return response()->json([
                'summary' => $summary,
                'voucher_stats' => $voucherStats,
                'vouchers_by_plan' => $vouchersByPlan,
                'generation_trend' => $generationTrend,
                'sales_trend' => $salesTrend,
                'expiration_forecast' => $expirationForecast,
                'chart_data' => $chartData,
            ]);
        }

        return view('reports.vouchers', compact(
            'summary',
            'voucherStats',
            'vouchersByPlan',
            'generationTrend',
            'salesTrend',
            'expirationForecast',
            'chartData',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export sales report to CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportSales(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $sales = Voucher::with(['bandwidthPlan', 'customer', 'soldBy', 'router'])
            ->whereNotNull('sold_at')
            ->whereBetween('sold_at', [$startDateTime, $endDateTime])
            ->orderBy('sold_at', 'desc')
            ->get();

        $fileName = 'sales_report_' . $startDate . '_to_' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function() use ($sales) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'Voucher Code',
                'Bandwidth Plan',
                'Price',
                'Customer',
                'Sold By',
                'Router',
                'Sold Date',
                'Activated Date',
                'Expires Date',
                'Status',
            ]);

            // CSV Data
            foreach ($sales as $sale) {
                fputcsv($file, [
                    $sale->code,
                    $sale->bandwidthPlan->name ?? 'N/A',
                    number_format($sale->price, 2),
                    $sale->customer->name ?? 'N/A',
                    $sale->soldBy->name ?? 'N/A',
                    $sale->router->name ?? 'N/A',
                    $sale->sold_at ? $sale->sold_at->format('Y-m-d H:i:s') : 'N/A',
                    $sale->activated_at ? $sale->activated_at->format('Y-m-d H:i:s') : 'N/A',
                    $sale->expires_at ? $sale->expires_at->format('Y-m-d H:i:s') : 'N/A',
                    $sale->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export revenue report to CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportRevenue(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $revenueData = Voucher::select(
            DB::raw('DATE(sold_at) as date'),
            'bandwidth_plans.name as plan_name',
            DB::raw('COUNT(vouchers.id) as vouchers_sold'),
            DB::raw('SUM(vouchers.price) as total_revenue')
        )
            ->join('bandwidth_plans', 'vouchers.bandwidth_plan_id', '=', 'bandwidth_plans.id')
            ->whereNotNull('vouchers.sold_at')
            ->whereBetween('vouchers.sold_at', [$startDateTime, $endDateTime])
            ->groupBy('date', 'bandwidth_plans.id', 'bandwidth_plans.name')
            ->orderBy('date')
            ->orderBy('bandwidth_plans.name')
            ->get();

        $fileName = 'revenue_report_' . $startDate . '_to_' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function() use ($revenueData) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'Date',
                'Bandwidth Plan',
                'Vouchers Sold',
                'Total Revenue',
            ]);

            // CSV Data
            foreach ($revenueData as $data) {
                fputcsv($file, [
                    $data->date,
                    $data->plan_name,
                    $data->vouchers_sold,
                    number_format($data->total_revenue, 2),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export customer list to CSV.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportCustomers(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $customers = Customer::select('customers.*')
            ->selectSub(function ($query) {
                $query->from('vouchers')
                    ->whereColumn('vouchers.customer_id', 'customers.id')
                    ->whereNotNull('sold_at')
                    ->selectRaw('COUNT(*)');
            }, 'total_vouchers')
            ->selectSub(function ($query) {
                $query->from('vouchers')
                    ->whereColumn('vouchers.customer_id', 'customers.id')
                    ->whereNotNull('sold_at')
                    ->selectRaw('SUM(price)');
            }, 'total_spent')
            ->selectSub(function ($query) use ($startDateTime, $endDateTime) {
                $query->from('vouchers')
                    ->whereColumn('vouchers.customer_id', 'customers.id')
                    ->whereNotNull('sold_at')
                    ->whereBetween('sold_at', [$startDateTime, $endDateTime])
                    ->selectRaw('COUNT(*)');
            }, 'purchases_in_period')
            ->with('createdBy:id,name')
            ->orderBy('total_spent', 'desc')
            ->get();

        $fileName = 'customers_report_' . $startDate . '_to_' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');

            // CSV Headers
            fputcsv($file, [
                'Name',
                'Email',
                'Phone',
                'Location',
                'Status',
                'Total Vouchers',
                'Total Spent',
                'Purchases in Period',
                'Created By',
                'Created Date',
            ]);

            // CSV Data
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->name,
                    $customer->email ?? 'N/A',
                    $customer->phone ?? 'N/A',
                    $customer->location ?? 'N/A',
                    $customer->is_active ? 'Active' : 'Inactive',
                    $customer->total_vouchers ?? 0,
                    number_format($customer->total_spent ?? 0, 2),
                    $customer->purchases_in_period ?? 0,
                    $customer->createdBy->name ?? 'N/A',
                    $customer->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Helper method to format bytes to human-readable format.
     *
     * @param  int  $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes >= 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Helper method to format duration in seconds to HH:MM:SS format.
     *
     * @param  int|float  $seconds
     * @return string
     */
    private function formatDuration($seconds): string
    {
        $seconds = (int) $seconds;
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }
}
