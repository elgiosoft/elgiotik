<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\HotspotUser;
use App\Models\Router;
use App\Models\Voucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard with statistics and recent data.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Router statistics
        $totalRouters = Router::count();
        $activeRouters = Router::active()->count();

        // Customer statistics
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::active()->count();

        // Revenue statistics
        $totalRevenue = Voucher::sold()->sum('price');
        $todayRevenue = Voucher::sold()
            ->whereDate('sold_at', today())
            ->sum('price');
        $weekRevenue = Voucher::sold()
            ->whereBetween('sold_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->sum('price');
        $monthRevenue = Voucher::sold()
            ->whereYear('sold_at', now()->year)
            ->whereMonth('sold_at', now()->month)
            ->sum('price');

        // Voucher statistics
        $totalVouchers = Voucher::count();
        $activeVouchers = Voucher::active()->count();
        $usedVouchers = Voucher::used()->count();
        $soldVouchers = Voucher::sold()->count();

        // Hotspot users statistics
        $totalHotspotUsers = HotspotUser::count();
        $activeHotspotUsers = HotspotUser::active()->count();
        $onlineUsers = HotspotUser::online()->count();

        // Recent vouchers with eager loading for performance
        $recentVouchers = Voucher::with([
            'bandwidthPlan:id,name,price',
            'router:id,name',
            'customer:id,name',
            'soldBy:id,name'
        ])
            ->latest()
            ->limit(10)
            ->get();

        // Recent customers with voucher counts
        $recentCustomers = Customer::withCount([
            'vouchers',
            'vouchers as active_vouchers_count' => function ($query) {
                $query->where('status', 'active');
            },
            'vouchers as sold_vouchers_count' => function ($query) {
                $query->whereNotNull('sold_at');
            }
        ])
            ->with('createdBy:id,name')
            ->latest()
            ->limit(10)
            ->get();

        // Additional statistics for dashboard cards
        $statistics = [
            'routers' => [
                'total' => $totalRouters,
                'active' => $activeRouters,
                'inactive' => $totalRouters - $activeRouters,
                'online' => Router::online()->count(),
                'offline' => Router::offline()->count(),
            ],
            'customers' => [
                'total' => $totalCustomers,
                'active' => $activeCustomers,
                'inactive' => $totalCustomers - $activeCustomers,
                'with_active_vouchers' => Customer::has('vouchers', '>', 0)
                    ->whereHas('vouchers', function ($query) {
                        $query->where('status', 'active');
                    })
                    ->count(),
            ],
            'vouchers' => [
                'total' => $totalVouchers,
                'active' => $activeVouchers,
                'used' => $usedVouchers,
                'sold' => $soldVouchers,
                'unsold' => Voucher::unsold()->count(),
                'expired' => Voucher::expired()->count(),
            ],
            'hotspot_users' => [
                'total' => $totalHotspotUsers,
                'active' => $activeHotspotUsers,
                'online' => $onlineUsers,
                'offline' => $totalHotspotUsers - $onlineUsers,
                'expired' => HotspotUser::expired()->count(),
            ],
            'revenue' => [
                'total' => $totalRevenue,
                'today' => $todayRevenue,
                'week' => $weekRevenue,
                'month' => $monthRevenue,
                'currency' => 'USD', // You can make this configurable from settings
            ],
        ];

        // Top selling bandwidth plans
        $topBandwidthPlans = DB::table('vouchers')
            ->select(
                'bandwidth_plans.name',
                DB::raw('COUNT(vouchers.id) as total_sold'),
                DB::raw('SUM(vouchers.price) as total_revenue')
            )
            ->join('bandwidth_plans', 'vouchers.bandwidth_plan_id', '=', 'bandwidth_plans.id')
            ->whereNotNull('vouchers.sold_at')
            ->groupBy('bandwidth_plans.id', 'bandwidth_plans.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Revenue trend (last 7 days)
        $revenueTrend = Voucher::select(
            DB::raw('DATE(sold_at) as date'),
            DB::raw('SUM(price) as revenue'),
            DB::raw('COUNT(id) as vouchers_sold')
        )
            ->whereNotNull('sold_at')
            ->whereBetween('sold_at', [
                now()->subDays(6)->startOfDay(),
                now()->endOfDay()
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top customers by spending
        $topCustomers = Customer::select('customers.*')
            ->selectSub(function ($query) {
                $query->from('vouchers')
                    ->whereColumn('vouchers.customer_id', 'customers.id')
                    ->whereNotNull('sold_at')
                    ->selectRaw('SUM(price)');
            }, 'total_spent')
            ->selectSub(function ($query) {
                $query->from('vouchers')
                    ->whereColumn('vouchers.customer_id', 'customers.id')
                    ->whereNotNull('sold_at')
                    ->selectRaw('COUNT(*)');
            }, 'vouchers_purchased')
            ->having('total_spent', '>', 0)
            ->orderByDesc('total_spent')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'statistics',
            'recentVouchers',
            'recentCustomers',
            'topBandwidthPlans',
            'revenueTrend',
            'topCustomers'
        ));
    }

    /**
     * Get dashboard statistics as JSON (for API or AJAX requests).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        $statistics = [
            'routers' => [
                'total' => Router::count(),
                'active' => Router::active()->count(),
                'online' => Router::online()->count(),
                'offline' => Router::offline()->count(),
            ],
            'customers' => [
                'total' => Customer::count(),
                'active' => Customer::active()->count(),
            ],
            'vouchers' => [
                'total' => Voucher::count(),
                'active' => Voucher::active()->count(),
                'used' => Voucher::used()->count(),
                'sold' => Voucher::sold()->count(),
            ],
            'hotspot_users' => [
                'total' => HotspotUser::count(),
                'active' => HotspotUser::active()->count(),
                'online' => HotspotUser::online()->count(),
            ],
            'revenue' => [
                'total' => Voucher::sold()->sum('price'),
                'today' => Voucher::sold()->whereDate('sold_at', today())->sum('price'),
                'week' => Voucher::sold()
                    ->whereBetween('sold_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->sum('price'),
                'month' => Voucher::sold()
                    ->whereYear('sold_at', now()->year)
                    ->whereMonth('sold_at', now()->month)
                    ->sum('price'),
            ],
        ];

        return response()->json($statistics);
    }

    /**
     * Get revenue chart data.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function revenueChart(Request $request)
    {
        $days = $request->input('days', 30);

        $revenueData = Voucher::select(
            DB::raw('DATE(sold_at) as date'),
            DB::raw('SUM(price) as revenue'),
            DB::raw('COUNT(id) as vouchers_sold')
        )
            ->whereNotNull('sold_at')
            ->whereBetween('sold_at', [
                now()->subDays($days - 1)->startOfDay(),
                now()->endOfDay()
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return response()->json($revenueData);
    }

    /**
     * Get online users trend data.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function onlineUsersTrend()
    {
        $onlineUsersPerRouter = Router::select('routers.id', 'routers.name')
            ->withCount([
                'hotspotUsers as online_users' => function ($query) {
                    $query->where('is_online', true);
                },
                'hotspotUsers as total_users'
            ])
            ->where('is_active', true)
            ->get();

        return response()->json($onlineUsersPerRouter);
    }
}
