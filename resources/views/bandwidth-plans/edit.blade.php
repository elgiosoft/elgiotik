@extends('layouts.app')

@section('title', 'Edit Bandwidth Plan')

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center">
                <a href="{{ route('bandwidth-plans.show', $bandwidthPlan) }}" class="mr-4 text-gray-600 hover:text-gray-900">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Edit Bandwidth Plan</h2>
                    <p class="mt-1 text-sm text-gray-500">Update plan: {{ $bandwidthPlan->name }}</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('bandwidth-plans.update', $bandwidthPlan) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Plan Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Plan Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" id="name" value="{{ old('name', $bandwidthPlan->name) }}" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('name') border-red-500 @enderror">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Speed Settings -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="download_speed" class="block text-sm font-medium text-gray-700 mb-1">
                            Download Speed <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="download_speed" id="download_speed" value="{{ old('download_speed', $bandwidthPlan->download_speed) }}" placeholder="e.g., 2M, 512k" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('download_speed') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">Format: 512k, 1M, 10M</p>
                        @error('download_speed')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="upload_speed" class="block text-sm font-medium text-gray-700 mb-1">
                            Upload Speed <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="upload_speed" id="upload_speed" value="{{ old('upload_speed', $bandwidthPlan->upload_speed) }}" placeholder="e.g., 1M, 256k" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('upload_speed') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">Format: 256k, 512k, 1M</p>
                        @error('upload_speed')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="rate_limit" class="block text-sm font-medium text-gray-700 mb-1">
                            Rate Limit <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="rate_limit" id="rate_limit" value="{{ old('rate_limit', $bandwidthPlan->rate_limit) }}" placeholder="e.g., 2M" required class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('rate_limit') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">Total bandwidth limit</p>
                        @error('rate_limit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Pricing -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                        Price (USD) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="price" id="price" value="{{ old('price', $bandwidthPlan->price) }}" step="0.01" min="0" required class="block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('price') border-red-500 @enderror">
                    </div>
                    @error('price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Validity Period -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="validity_days" class="block text-sm font-medium text-gray-700 mb-1">
                            Validity (Days)
                        </label>
                        <input type="number" name="validity_days" id="validity_days" value="{{ old('validity_days', $bandwidthPlan->validity_days) }}" min="1" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('validity_days') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">How many days the plan is valid</p>
                        @error('validity_days')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="validity_hours" class="block text-sm font-medium text-gray-700 mb-1">
                            Validity (Hours)
                        </label>
                        <input type="number" name="validity_hours" id="validity_hours" value="{{ old('validity_hours', $bandwidthPlan->validity_hours) }}" min="1" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('validity_hours') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">Alternative: validity in hours</p>
                        @error('validity_hours')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Limits & Timeouts -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="data_limit" class="block text-sm font-medium text-gray-700 mb-1">
                            Data Limit (MB)
                        </label>
                        <input type="number" name="data_limit" id="data_limit" value="{{ old('data_limit', $bandwidthPlan->data_limit) }}" min="1" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('data_limit') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">Total data allowance in MB</p>
                        @error('data_limit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="session_timeout" class="block text-sm font-medium text-gray-700 mb-1">
                            Session Timeout (mins)
                        </label>
                        <input type="number" name="session_timeout" id="session_timeout" value="{{ old('session_timeout', $bandwidthPlan->session_timeout) }}" min="1" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('session_timeout') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">Max session duration</p>
                        @error('session_timeout')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="idle_timeout" class="block text-sm font-medium text-gray-700 mb-1">
                            Idle Timeout (mins)
                        </label>
                        <input type="number" name="idle_timeout" id="idle_timeout" value="{{ old('idle_timeout', $bandwidthPlan->idle_timeout) }}" min="1" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('idle_timeout') border-red-500 @enderror">
                        <p class="mt-1 text-xs text-gray-500">Disconnect if idle</p>
                        @error('idle_timeout')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea name="description" id="description" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('description') border-red-500 @enderror">{{ old('description', $bandwidthPlan->description) }}</textarea>
                    @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $bandwidthPlan->is_active) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Plan is active
                    </label>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('bandwidth-plans.show', $bandwidthPlan) }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Update Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
