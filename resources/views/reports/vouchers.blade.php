@extends('layouts.app')

@section('title', 'Voucher Statistics')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <div class="flex items-center">
                    <a href="{{ route('reports.index') }}" class="mr-4 text-gray-600 hover:text-gray-900">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Voucher Statistics</h2>
                        <p class="mt-1 text-sm text-gray-500">Voucher lifecycle tracking and status reports</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form action="{{ route('reports.vouchers') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">Apply</button>
                </div>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Total Vouchers</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($summary['total_vouchers']) }}</dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Generated in Period</dt>
                <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ number_format($summary['generated_in_period']) }}</dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Sold in Period</dt>
                <dd class="mt-1 text-3xl font-semibold text-green-600">{{ number_format($summary['sold_in_period']) }}</dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Sale Rate</dt>
                <dd class="mt-1 text-3xl font-semibold text-purple-600">{{ number_format($summary['sale_rate'], 1) }}%</dd>
            </div>
        </div>

        <!-- Status Overview -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Voucher Status Distribution</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                    <div class="text-center">
                        <div class="text-3xl font-bold text-green-600">{{ number_format($voucherStats['active']) }}</div>
                        <div class="text-sm text-gray-500 mt-1">Active</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-blue-600">{{ number_format($voucherStats['used']) }}</div>
                        <div class="text-sm text-gray-500 mt-1">Used</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-red-600">{{ number_format($voucherStats['expired']) }}</div>
                        <div class="text-sm text-gray-500 mt-1">Expired</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-gray-600">{{ number_format($voucherStats['disabled']) }}</div>
                        <div class="text-sm text-gray-500 mt-1">Disabled</div>
                    </div>
                    <div class="text-center">
                        <div class="text-3xl font-bold text-yellow-600">{{ number_format($voucherStats['unsold']) }}</div>
                        <div class="text-sm text-gray-500 mt-1">Unsold</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lifecycle Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Lifecycle Metrics</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Avg Time to Sale</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $summary['average_time_to_sale'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Avg Time to Activation</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ $summary['average_time_to_activation'] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Activation Rate</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ number_format($summary['activation_rate'], 1) }}%</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Expiring Soon (7 days)</dt>
                        <dd class="text-sm font-medium text-red-600">{{ number_format($summary['expiring_soon']) }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Period Statistics</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Generated</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ number_format($summary['generated_in_period']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Sold</dt>
                        <dd class="text-sm font-medium text-green-600">{{ number_format($summary['sold_in_period']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Used</dt>
                        <dd class="text-sm font-medium text-blue-600">{{ number_format($summary['used_in_period']) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Expired</dt>
                        <dd class="text-sm font-medium text-red-600">{{ number_format($summary['expired_in_period']) }}</dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Vouchers by Bandwidth Plan -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Vouchers by Bandwidth Plan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan Name</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Active</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sold</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Used</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sale Rate</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($vouchersByPlan as $plan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $plan->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">${{ number_format($plan->price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($plan->total_vouchers) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">{{ number_format($plan->active_vouchers) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($plan->sold_vouchers) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-blue-600">{{ number_format($plan->used_vouchers) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-purple-600">{{ number_format($plan->sale_rate, 1) }}%</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No voucher data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
