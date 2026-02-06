@extends('layouts.app')

@section('title', 'Customer Reports')

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
                        <h2 class="text-2xl font-bold text-gray-900">Customer Reports</h2>
                        <p class="mt-1 text-sm text-gray-500">Customer statistics and insights</p>
                    </div>
                </div>
            </div>
            <a href="{{ route('reports.exports.customers', request()->all()) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                Export CSV
            </a>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form action="{{ route('reports.customers') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Total Customers</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($summary['total_customers']) }}</dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">New Customers</dt>
                <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ number_format($summary['new_customers']) }}</dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Active Customers</dt>
                <dd class="mt-1 text-3xl font-semibold text-green-600">{{ number_format($summary['active_customers']) }}</dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Avg Customer Value</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">${{ number_format($summary['average_customer_value'], 2) }}</dd>
            </div>
        </div>

        <!-- Top Customers by Spending -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Top Customers by Spending</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer Name</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Vouchers Purchased</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Spent</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topCustomers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                <div class="text-sm text-gray-500">{{ $customer->email ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($customer->vouchers_purchased) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-green-600">${{ number_format($customer->total_spent, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($customer->is_active)
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                                @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Inactive</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No customer data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- New Customers -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">New Customers in Period</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contact</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Vouchers</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Joined Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($newCustomers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $customer->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $customer->email ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $customer->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ $customer->total_vouchers }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $customer->created_at->format('M d, Y') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No new customers in this period</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
