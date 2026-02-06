@extends('layouts.app')

@section('title', 'Bandwidth Plan Details')

@section('content')
<div class="py-6">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="md:flex md:items-center md:justify-between mb-6">
            <div class="flex-1 min-w-0">
                <div class="flex items-center">
                    <a href="{{ route('bandwidth-plans.index') }}" class="mr-4 text-gray-600 hover:text-gray-900">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            {{ $bandwidthPlan->name }}
                        </h2>
                        <p class="mt-1 text-sm text-gray-500">
                            Bandwidth plan details and statistics
                        </p>
                    </div>
                </div>
            </div>
            <div class="mt-4 flex space-x-3 md:mt-0 md:ml-4">
                <a href="{{ route('bandwidth-plans.edit', $bandwidthPlan) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </a>
            </div>
        </div>

        <!-- Plan Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Price</p>
                        <p class="text-2xl font-semibold text-gray-900">${{ number_format($bandwidthPlan->price, 2) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Total Vouchers</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $bandwidthPlan->vouchers_count ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Active Vouchers</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $bandwidthPlan->active_vouchers_count ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-8 w-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500">Active Users</p>
                        <p class="text-2xl font-semibold text-gray-900">{{ $bandwidthPlan->active_users_count ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plan Details -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Speed & Limits -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Speed & Limits</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="space-y-4">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Download Speed</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $bandwidthPlan->download_speed }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Upload Speed</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $bandwidthPlan->upload_speed }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Rate Limit</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $bandwidthPlan->rate_limit }}</dd>
                        </div>
                        @if($bandwidthPlan->data_limit)
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Data Limit</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ number_format($bandwidthPlan->data_limit / (1024*1024), 0) }} MB</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Validity & Timeouts -->
            <div class="bg-white shadow rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Validity & Timeouts</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="space-y-4">
                        @if($bandwidthPlan->validity_days)
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Validity (Days)</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $bandwidthPlan->validity_days }} days</dd>
                        </div>
                        @endif
                        @if($bandwidthPlan->validity_hours)
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Validity (Hours)</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $bandwidthPlan->validity_hours }} hours</dd>
                        </div>
                        @endif
                        @if($bandwidthPlan->session_timeout)
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Session Timeout</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $bandwidthPlan->session_timeout }} minutes</dd>
                        </div>
                        @endif
                        @if($bandwidthPlan->idle_timeout)
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Idle Timeout</dt>
                            <dd class="text-sm text-gray-900 font-semibold">{{ $bandwidthPlan->idle_timeout }} minutes</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Plan Information -->
            <div class="bg-white shadow rounded-lg lg:col-span-2">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Plan Information</h3>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Status</dt>
                            <dd>
                                @if($bandwidthPlan->is_active)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                                @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Price</dt>
                            <dd class="text-sm text-gray-900">${{ number_format($bandwidthPlan->price, 2) }} USD</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Created</dt>
                            <dd class="text-sm text-gray-900">{{ $bandwidthPlan->created_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500 mb-1">Last Updated</dt>
                            <dd class="text-sm text-gray-900">{{ $bandwidthPlan->updated_at->format('M d, Y g:i A') }}</dd>
                        </div>
                        @if($bandwidthPlan->description)
                        <div class="md:col-span-2">
                            <dt class="text-sm font-medium text-gray-500 mb-1">Description</dt>
                            <dd class="text-sm text-gray-900">{{ $bandwidthPlan->description }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>
            </div>
        </div>

        <!-- Danger Zone -->
        <div class="mt-6 bg-white shadow rounded-lg border border-red-200">
            <div class="px-6 py-4 border-b border-red-200">
                <h3 class="text-lg font-medium text-red-900">Danger Zone</h3>
            </div>
            <div class="px-6 py-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">Delete this bandwidth plan</h4>
                        <p class="text-sm text-gray-500">Once you delete a plan, there is no going back. Please be certain.</p>
                    </div>
                    <form action="{{ route('bandwidth-plans.destroy', $bandwidthPlan) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this bandwidth plan? This action cannot be undone.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
                            Delete Plan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
