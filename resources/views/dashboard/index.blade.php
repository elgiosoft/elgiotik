@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="py-6" x-data="dashboard()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
                <p class="mt-1 text-sm text-gray-500">Welcome back! Here's what's happening today.</p>
            </div>
            <div class="text-sm text-gray-600" x-text="currentTime"></div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Revenue Card -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-6 text-white transform transition hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">Total Revenue</p>
                        <h3 class="text-3xl font-bold mt-2">${{ number_format($statistics['revenue']['total'], 2) }}</h3>
                        <p class="text-blue-100 text-xs mt-2">
                            This month: ${{ number_format($statistics['revenue']['month'], 2) }}
                        </p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Online Users Card -->
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-6 text-white transform transition hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">Online Users</p>
                        <h3 class="text-3xl font-bold mt-2">{{ number_format($statistics['hotspot_users']['online']) }}</h3>
                        <p class="text-green-100 text-xs mt-2">
                            Total: {{ number_format($statistics['hotspot_users']['total']) }} users
                        </p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Customers Card -->
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-6 text-white transform transition hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">Total Customers</p>
                        <h3 class="text-3xl font-bold mt-2">{{ number_format($statistics['customers']['total']) }}</h3>
                        <p class="text-purple-100 text-xs mt-2">
                            Active: {{ number_format($statistics['customers']['active']) }}
                        </p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Active Vouchers Card -->
            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-6 text-white transform transition hover:scale-105">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">Active Vouchers</p>
                        <h3 class="text-3xl font-bold mt-2">{{ number_format($statistics['vouchers']['active']) }}</h3>
                        <p class="text-orange-100 text-xs mt-2">
                            Sold: {{ number_format($statistics['vouchers']['sold']) }}
                        </p>
                    </div>
                    <div class="bg-white bg-opacity-20 rounded-full p-3">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts and Quick Actions Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Revenue Chart -->
            <div class="lg:col-span-2 bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Revenue Trend (Last 7 Days)</h3>
                </div>
                <canvas id="revenueChart" height="80"></canvas>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-6">Quick Actions</h3>
                <div class="space-y-3">
                    <a href="{{ route('vouchers.create') }}" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg px-4 py-3 flex items-center justify-center space-x-2 hover:from-blue-600 hover:to-blue-700 transition transform hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        <span class="font-medium">New Voucher</span>
                    </a>

                    <a href="{{ route('customers.create') }}" class="w-full bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg px-4 py-3 flex items-center justify-center space-x-2 hover:from-green-600 hover:to-green-700 transition transform hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                        <span class="font-medium">Add Customer</span>
                    </a>

                    <a href="{{ route('reports.index') }}" class="w-full bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg px-4 py-3 flex items-center justify-center space-x-2 hover:from-purple-600 hover:to-purple-700 transition transform hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="font-medium">View Reports</span>
                    </a>

                    <a href="{{ route('routers.index') }}" class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg px-4 py-3 flex items-center justify-center space-x-2 hover:from-orange-600 hover:to-orange-700 transition transform hover:scale-105">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/>
                        </svg>
                        <span class="font-medium">Manage Routers</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Top Bandwidth Plans and Top Customers -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Top Bandwidth Plans -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-6">Top Bandwidth Plans</h3>
                <div class="space-y-4">
                    @forelse($topBandwidthPlans as $index => $plan)
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-{{ ['blue', 'green', 'purple', 'orange', 'pink'][$index % 5] }}-50 to-{{ ['blue', 'green', 'purple', 'orange', 'pink'][$index % 5] }}-100 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="bg-{{ ['blue', 'green', 'purple', 'orange', 'pink'][$index % 5] }}-500 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold">{{ $index + 1 }}</div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $plan->name }}</p>
                                <p class="text-sm text-gray-600">{{ number_format($plan->total_sold) }} sold</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-{{ ['blue', 'green', 'purple', 'orange', 'pink'][$index % 5] }}-600">${{ number_format($plan->total_revenue, 2) }}</p>
                            <p class="text-xs text-gray-500">revenue</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        No bandwidth plans data available
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Top Customers -->
            <div class="bg-white rounded-xl shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-6">Top Customers</h3>
                <div class="space-y-4">
                    @forelse($topCustomers as $index => $customer)
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-{{ ['blue', 'green', 'purple', 'orange', 'pink'][$index % 5] }}-50 to-{{ ['blue', 'green', 'purple', 'orange', 'pink'][$index % 5] }}-100 rounded-lg">
                        <div class="flex items-center space-x-3">
                            <div class="bg-{{ ['blue', 'green', 'purple', 'orange', 'pink'][$index % 5] }}-500 text-white rounded-full w-10 h-10 flex items-center justify-center font-bold">{{ $index + 1 }}</div>
                            <div>
                                <p class="font-semibold text-gray-900">{{ $customer->name }}</p>
                                <p class="text-sm text-gray-600">{{ $customer->vouchers_purchased }} vouchers</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-{{ ['blue', 'green', 'purple', 'orange', 'pink'][$index % 5] }}-600">${{ number_format($customer->total_spent, 2) }}</p>
                            <p class="text-xs text-gray-500">total spent</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        No customer data available
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Vouchers -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Recent Vouchers</h3>
                    <a href="{{ route('vouchers.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View all</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-2 text-xs font-semibold text-gray-700">Code</th>
                                <th class="text-left py-3 px-2 text-xs font-semibold text-gray-700">Plan</th>
                                <th class="text-left py-3 px-2 text-xs font-semibold text-gray-700">Customer</th>
                                <th class="text-right py-3 px-2 text-xs font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($recentVouchers as $voucher)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="py-3 px-2"><span class="font-mono text-xs font-semibold text-blue-600">{{ $voucher->code }}</span></td>
                                <td class="py-3 px-2 text-xs text-gray-900">{{ $voucher->bandwidthPlan->name ?? 'N/A' }}</td>
                                <td class="py-3 px-2 text-xs text-gray-600">{{ $voucher->customer->name ?? 'Unsold' }}</td>
                                <td class="py-3 px-2 text-right">
                                    @if($voucher->status === 'active')
                                    <span class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded-full">Active</span>
                                    @elseif($voucher->status === 'used')
                                    <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full">Used</span>
                                    @elseif($voucher->status === 'expired')
                                    <span class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded-full">Expired</span>
                                    @else
                                    <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full">{{ ucfirst($voucher->status) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-sm text-gray-500">No recent vouchers</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Customers -->
            <div class="bg-white rounded-xl shadow p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-medium text-gray-900">Recent Customers</h3>
                    <a href="{{ route('customers.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">View all</a>
                </div>
                <div class="space-y-3">
                    @forelse($recentCustomers as $customer)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                {{ strtoupper(substr($customer->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-900 text-sm">{{ $customer->name }}</p>
                                <p class="text-xs text-gray-500">{{ $customer->vouchers_count }} vouchers</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-gray-600">{{ $customer->created_at->diffForHumans() }}</p>
                            @if($customer->is_active)
                            <span class="inline-block px-2 py-0.5 bg-green-100 text-green-800 text-xs font-semibold rounded-full mt-1">Active</span>
                            @else
                            <span class="inline-block px-2 py-0.5 bg-gray-100 text-gray-800 text-xs font-semibold rounded-full mt-1">Inactive</span>
                            @endif
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-8 text-gray-500">
                        No recent customers
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    function dashboard() {
        return {
            currentTime: '',

            init() {
                this.updateTime();
                setInterval(() => this.updateTime(), 1000);
                this.initChart();
            },

            updateTime() {
                const now = new Date();
                const options = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                this.currentTime = now.toLocaleDateString('en-US', options);
            },

            initChart() {
                const ctx = document.getElementById('revenueChart');
                if (!ctx) return;

                const revenueTrend = @json($revenueTrend);
                const labels = revenueTrend.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
                });
                const data = revenueTrend.map(item => item.revenue);

                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Revenue',
                            data: data,
                            borderColor: 'rgb(59, 130, 246)',
                            backgroundColor: 'rgba(59, 130, 246, 0.1)',
                            tension: 0.4,
                            fill: true,
                            pointBackgroundColor: 'rgb(59, 130, 246)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 7
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                borderRadius: 8,
                                titleFont: {
                                    size: 14,
                                    weight: 'bold'
                                },
                                bodyFont: {
                                    size: 13
                                },
                                callbacks: {
                                    label: function(context) {
                                        return 'Revenue: $' + context.parsed.y.toLocaleString();
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return '$' + value.toLocaleString();
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }
        }
    }
</script>
@endpush
@endsection
