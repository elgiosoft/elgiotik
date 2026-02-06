@extends('layouts.app')

@section('title', 'Edit Voucher')

@section('content')
<div class="py-6">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center">
                <a href="{{ route('vouchers.show', $voucher) }}" class="mr-4 text-gray-600 hover:text-gray-900">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Edit Voucher</h2>
                    <p class="mt-1 text-sm text-gray-500">Update voucher information for {{ $voucher->code }}</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <div class="bg-white shadow rounded-lg">
            <form action="{{ route('vouchers.update', $voucher) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Voucher Code (Read-only) -->
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">
                        Voucher Code
                    </label>
                    <input type="text" id="code" value="{{ $voucher->code }}" readonly class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm bg-gray-50 text-gray-500 sm:text-sm cursor-not-allowed">
                    <p class="mt-1 text-sm text-gray-500">Voucher code cannot be changed</p>
                </div>

                <!-- Bandwidth Plan -->
                <div>
                    <label for="bandwidth_plan_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Bandwidth Plan <span class="text-red-500">*</span>
                    </label>
                    <select name="bandwidth_plan_id" id="bandwidth_plan_id" required class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('bandwidth_plan_id') border-red-500 @enderror">
                        <option value="">Select a plan</option>
                        @foreach($bandwidthPlans as $plan)
                            <option value="{{ $plan->id }}" {{ old('bandwidth_plan_id', $voucher->bandwidth_plan_id) == $plan->id ? 'selected' : '' }}>
                                {{ $plan->name }} - {{ $plan->download_speed }}/{{ $plan->upload_speed }}
                            </option>
                        @endforeach
                    </select>
                    @error('bandwidth_plan_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Router -->
                <div>
                    <label for="router_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Router <span class="text-red-500">*</span>
                    </label>
                    <select name="router_id" id="router_id" required class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('router_id') border-red-500 @enderror">
                        <option value="">Select a router</option>
                        @foreach($routers as $router)
                            <option value="{{ $router->id }}" {{ old('router_id', $voucher->router_id) == $router->id ? 'selected' : '' }}>
                                {{ $router->name }} ({{ $router->ip_address }})
                            </option>
                        @endforeach
                    </select>
                    @error('router_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Customer -->
                <div>
                    <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">
                        Customer
                    </label>
                    <select name="customer_id" id="customer_id" class="block w-full pl-3 pr-10 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('customer_id') border-red-500 @enderror">
                        <option value="">No customer assigned</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ old('customer_id', $voucher->customer_id) == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }} - {{ $customer->phone }}
                            </option>
                        @endforeach
                    </select>
                    @error('customer_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Price -->
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                        Price <span class="text-red-500">*</span>
                    </label>
                    <div class="relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">$</span>
                        </div>
                        <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price', $voucher->price) }}" required class="block w-full pl-7 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('price') border-red-500 @enderror" placeholder="0.00">
                    </div>
                    @error('price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes" id="notes" rows="3" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm @error('notes') border-red-500 @enderror" placeholder="Optional notes about this voucher">{{ old('notes', $voucher->notes) }}</textarea>
                    @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Info -->
                <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-2">Voucher Status Information</h4>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Current Status</dt>
                            <dd class="mt-1 font-medium text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ $voucher->status === 'active' ? 'bg-green-100 text-green-800' :
                                       ($voucher->status === 'used' ? 'bg-blue-100 text-blue-800' :
                                       ($voucher->status === 'expired' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')) }}">
                                    {{ ucfirst($voucher->status) }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Created</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $voucher->created_at->format('M d, Y') }}</dd>
                        </div>
                        @if($voucher->sold_at)
                        <div>
                            <dt class="text-gray-500">Sold At</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $voucher->sold_at->format('M d, Y') }}</dd>
                        </div>
                        @endif
                        @if($voucher->activated_at)
                        <div>
                            <dt class="text-gray-500">Activated At</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $voucher->activated_at->format('M d, Y') }}</dd>
                        </div>
                        @endif
                        @if($voucher->expires_at)
                        <div>
                            <dt class="text-gray-500">Expires At</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $voucher->expires_at->format('M d, Y') }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('vouchers.show', $voucher) }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Update Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
