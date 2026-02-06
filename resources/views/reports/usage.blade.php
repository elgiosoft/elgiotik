@extends('layouts.app')

@section('title', 'Usage Reports')

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
                        <h2 class="text-2xl font-bold text-gray-900">Usage Reports</h2>
                        <p class="mt-1 text-sm text-gray-500">Data consumption and session patterns</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow mb-6 p-4">
            <form action="{{ route('reports.usage') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                <dt class="text-sm font-medium text-gray-500">Total Data Consumed</dt>
                <dd class="mt-1 text-2xl font-semibold text-purple-600">{{ $summary['total_data_consumed'] }}</dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Total Sessions</dt>
                <dd class="mt-1 text-3xl font-semibold text-gray-900">{{ number_format($summary['total_sessions']) }}</dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Unique Users</dt>
                <dd class="mt-1 text-3xl font-semibold text-blue-600">{{ number_format($summary['unique_users']) }}</dd>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <dt class="text-sm font-medium text-gray-500">Avg Session Duration</dt>
                <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $summary['average_session_duration'] }}</dd>
            </div>
        </div>

        <!-- Usage by Bandwidth Plan -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Usage by Bandwidth Plan</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan Name</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Users</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sessions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Data Used</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Duration</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($usageByPlan as $plan)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $plan->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($plan->unique_users) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($plan->total_sessions) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-purple-600 font-medium">{{ $plan->total_bytes_formatted }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ $plan->total_duration_formatted }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No usage data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Top Users by Data Consumption -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Top Users by Data Consumption</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Username</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Plan</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Data Used</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Sessions</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($topUsers as $user)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->username }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->customer_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->plan_name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-purple-600">{{ $user->total_bytes_formatted }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ number_format($user->total_sessions) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-500">{{ $user->total_duration_formatted }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No user data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
