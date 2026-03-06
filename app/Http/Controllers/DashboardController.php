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
        $user = auth()->user();

        // Get user's routers (or all routers for admin)
        $routerQuery = $user->isAdmin() ? Router::query() : Router::where('user_id', $user->id);
        $routerIds = $routerQuery->pluck('id');

        // Router statistics
        $totalRouters = (clone $routerQuery)->count();
        $activeRouters = (clone $routerQuery)->active()->count();

        // Customer statistics (scoped to user's routers)
        $totalCustomers = Customer::whereHas('vouchers', function($q) use ($routerIds) {
            $q->whereIn('router_id', $routerIds);
        })->count();
        $activeCustomers = Customer::active()->whereHas('vouchers', function($q) use ($routerIds) {
            $q->whereIn('router_id', $routerIds);
        })->count();

        // Revenue statistics (scoped to user's routers)
        $totalRevenue = Voucher::sold()->whereIn('router_id', $routerIds)->sum('price');
        $todayRevenue = Voucher::sold()
            ->whereIn('router_id', $routerIds)
            ->whereDate('sold_at', today())
            ->sum('price');
        $weekRevenue = Voucher::sold()
            ->whereIn('router_id', $routerIds)
            ->whereBetween('sold_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->sum('price');
        $monthRevenue = Voucher::sold()
            ->whereIn('router_id', $routerIds)
            ->whereYear('sold_at', now()->year)
            ->whereMonth('sold_at', now()->month)
            ->sum('price');

        // Voucher statistics (scoped to user's routers)
        $totalVouchers = Voucher::whereIn('router_id', $routerIds)->count();
        $activeVouchers = Voucher::active()->whereIn('router_id', $routerIds)->count();
        $usedVouchers = Voucher::used()->whereIn('router_id', $routerIds)->count();
        $soldVouchers = Voucher::sold()->whereIn('router_id', $routerIds)->count();

        // Hotspot users statistics (scoped to user's routers)
        $totalHotspotUsers = HotspotUser::whereIn('router_id', $routerIds)->count();
        $activeHotspotUsers = HotspotUser::active()->whereIn('router_id', $routerIds)->count();
        $onlineUsers = HotspotUser::online()->whereIn('router_id', $routerIds)->count();

        // Recent vouchers with eager loading for performance (scoped to user's routers)
        $recentVouchers = Voucher::with([
            'bandwidthPlan:id,name,price',
            'router:id,name',
            'customer:id,name',
            'soldBy:id,name'
        ])
            ->whereIn('router_id', $routerIds)
            ->latest()
            ->limit(10)
            ->get();

        // Recent customers with voucher counts (scoped to user's routers)
        $recentCustomers = Customer::withCount([
            'vouchers' => function ($query) use ($routerIds) {
                $query->whereIn('router_id', $routerIds);
            },
            'vouchers as active_vouchers_count' => function ($query) use ($routerIds) {
                $query->where('status', 'active')->whereIn('router_id', $routerIds);
            },
            'vouchers as sold_vouchers_count' => function ($query) use ($routerIds) {
                $query->whereNotNull('sold_at')->whereIn('router_id', $routerIds);
            }
        ])
            ->whereHas('vouchers', function ($query) use ($routerIds) {
                $query->whereIn('router_id', $routerIds);
            })
            ->with('createdBy:id,name')
            ->latest()
            ->limit(10)
            ->get();

        // Additional statistics for dashboard cards (scoped to user's routers)
        $statistics = [
            'routers' => [
                'total' => $totalRouters,
                'active' => $activeRouters,
                'inactive' => $totalRouters - $activeRouters,
                'online' => (clone $routerQuery)->online()->count(),
                'offline' => (clone $routerQuery)->offline()->count(),
            ],
            'customers' => [
                'total' => $totalCustomers,
                'active' => $activeCustomers,
                'inactive' => $totalCustomers - $activeCustomers,
                'with_active_vouchers' => Customer::has('vouchers', '>', 0)
                    ->whereHas('vouchers', function ($query) use ($routerIds) {
                        $query->where('status', 'active')->whereIn('router_id', $routerIds);
                    })
                    ->count(),
            ],
            'vouchers' => [
                'total' => $totalVouchers,
                'active' => $activeVouchers,
                'used' => $usedVouchers,
                'sold' => $soldVouchers,
                'unsold' => Voucher::unsold()->whereIn('router_id', $routerIds)->count(),
                'expired' => Voucher::expired()->whereIn('router_id', $routerIds)->count(),
            ],
            'hotspot_users' => [
                'total' => $totalHotspotUsers,
                'active' => $activeHotspotUsers,
                'online' => $onlineUsers,
                'offline' => $totalHotspotUsers - $onlineUsers,
                'expired' => HotspotUser::expired()->whereIn('router_id', $routerIds)->count(),
            ],
            'revenue' => [
                'total' => $totalRevenue,
                'today' => $todayRevenue,
                'week' => $weekRevenue,
                'month' => $monthRevenue,
                'currency' => 'USD', // You can make this configurable from settings
            ],
        ];

        // Top selling bandwidth plans (scoped to user's routers)
        $topBandwidthPlans = DB::table('vouchers')
            ->select(
                'bandwidth_plans.name',
                DB::raw('COUNT(vouchers.id) as total_sold'),
                DB::raw('SUM(vouchers.price) as total_revenue')
            )
            ->join('bandwidth_plans', 'vouchers.bandwidth_plan_id', '=', 'bandwidth_plans.id')
            ->whereNotNull('vouchers.sold_at')
            ->whereIn('vouchers.router_id', $routerIds)
            ->groupBy('bandwidth_plans.id', 'bandwidth_plans.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Revenue trend (last 7 days) (scoped to user's routers)
        $revenueTrend = Voucher::select(
            DB::raw('DATE(sold_at) as date'),
            DB::raw('SUM(price) as revenue'),
            DB::raw('COUNT(id) as vouchers_sold')
        )
            ->whereNotNull('sold_at')
            ->whereIn('router_id', $routerIds)
            ->whereBetween('sold_at', [
                now()->subDays(6)->startOfDay(),
                now()->endOfDay()
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top customers by spending (scoped to user's routers)
        $topCustomers = Customer::select('customers.*')
            ->selectSub(function ($query) use ($routerIds) {
                $query->from('vouchers')
                    ->whereColumn('vouchers.customer_id', 'customers.id')
                    ->whereNotNull('sold_at')
                    ->whereIn('router_id', $routerIds)
                    ->selectRaw('SUM(price)');
            }, 'total_spent')
            ->selectSub(function ($query) use ($routerIds) {
                $query->from('vouchers')
                    ->whereColumn('vouchers.customer_id', 'customers.id')
                    ->whereNotNull('sold_at')
                    ->whereIn('router_id', $routerIds)
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
        $user = auth()->user();

        // Get user's routers (or all routers for admin)
        $routerQuery = $user->isAdmin() ? Router::query() : Router::where('user_id', $user->id);
        $routerIds = $routerQuery->pluck('id');

        $statistics = [
            'routers' => [
                'total' => (clone $routerQuery)->count(),
                'active' => (clone $routerQuery)->active()->count(),
                'online' => (clone $routerQuery)->online()->count(),
                'offline' => (clone $routerQuery)->offline()->count(),
            ],
            'customers' => [
                'total' => Customer::whereHas('vouchers', function($q) use ($routerIds) {
                    $q->whereIn('router_id', $routerIds);
                })->count(),
                'active' => Customer::active()->whereHas('vouchers', function($q) use ($routerIds) {
                    $q->whereIn('router_id', $routerIds);
                })->count(),
            ],
            'vouchers' => [
                'total' => Voucher::whereIn('router_id', $routerIds)->count(),
                'active' => Voucher::active()->whereIn('router_id', $routerIds)->count(),
                'used' => Voucher::used()->whereIn('router_id', $routerIds)->count(),
                'sold' => Voucher::sold()->whereIn('router_id', $routerIds)->count(),
            ],
            'hotspot_users' => [
                'total' => HotspotUser::whereIn('router_id', $routerIds)->count(),
                'active' => HotspotUser::active()->whereIn('router_id', $routerIds)->count(),
                'online' => HotspotUser::online()->whereIn('router_id', $routerIds)->count(),
            ],
            'revenue' => [
                'total' => Voucher::sold()->whereIn('router_id', $routerIds)->sum('price'),
                'today' => Voucher::sold()->whereIn('router_id', $routerIds)->whereDate('sold_at', today())->sum('price'),
                'week' => Voucher::sold()
                    ->whereIn('router_id', $routerIds)
                    ->whereBetween('sold_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->sum('price'),
                'month' => Voucher::sold()
                    ->whereIn('router_id', $routerIds)
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
        $user = auth()->user();
        $days = $request->input('days', 30);

        // Get user's routers (or all routers for admin)
        $routerQuery = $user->isAdmin() ? Router::query() : Router::where('user_id', $user->id);
        $routerIds = $routerQuery->pluck('id');

        $revenueData = Voucher::select(
            DB::raw('DATE(sold_at) as date'),
            DB::raw('SUM(price) as revenue'),
            DB::raw('COUNT(id) as vouchers_sold')
        )
            ->whereNotNull('sold_at')
            ->whereIn('router_id', $routerIds)
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
        $user = auth()->user();

        // Get user's routers (or all routers for admin)
        $routerQuery = $user->isAdmin() ? Router::query() : Router::where('user_id', $user->id);

        $onlineUsersPerRouter = (clone $routerQuery)->select('routers.id', 'routers.name')
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
