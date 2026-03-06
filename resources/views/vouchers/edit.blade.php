@extends('layouts.app')

@section('title', 'Edit Voucher Profile')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <div class="flex items-center gap-4">
            <a href="{{ route('routers.vouchers.show', [$router, $voucher]) }}" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Voucher Profile</h1>
                <p class="mt-1 text-sm text-gray-600">Update profile #{{ $voucher->id }} for {{ $router->name }}</p>
            </div>
        </div>
    </div>

    <!-- Warning if users generated -->
    @if($voucher->users_generated > 0)
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Warning: Users Already Generated</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>This profile has {{ $voucher->users_generated }} generated user(s). Some fields are restricted to prevent issues with existing users.</p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <form action="{{ route('routers.vouchers.update', [$router, $voucher]) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Bandwidth Plan -->
            <div>
                <label for="bandwidth_plan_id" class="block text-sm font-medium text-gray-700">
                    Bandwidth Plan <span class="text-red-500">*</span>
                </label>
                <select name="bandwidth_plan_id" id="bandwidth_plan_id" required onchange="updatePriceFromPlan()" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('bandwidth_plan_id') border-red-300 @enderror">
                    <option value="">Select a bandwidth plan</option>
                    @foreach($bandwidthPlans ?? [] as $plan)
                        <option value="{{ $plan->id }}"
                                data-price="{{ $plan->price }}"
                                data-download="{{ $plan->download_speed }}"
                                data-upload="{{ $plan->upload_speed }}"
                                data-validity="{{ $plan->validity_period }}"
                                {{ old('bandwidth_plan_id', $voucher->bandwidth_plan_id) == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} - {{ $plan->download_speed }}/{{ $plan->upload_speed }}
                            @if($plan->validity_period)
                                - {{ $plan->validity_period }}h validity
                            @endif
                            - ${{ number_format($plan->price, 2) }}
                        </option>
                    @endforeach
                </select>
                @error('bandwidth_plan_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Customer Assignment -->
            <div>
                <label for="customer_id" class="block text-sm font-medium text-gray-700">
                    Assign to Customer
                </label>
                <select name="customer_id" id="customer_id" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('customer_id') border-red-300 @enderror">
                    <option value="">No customer assigned</option>
                    @foreach($customers ?? [] as $customer)
                        <option value="{{ $customer->id }}" {{ old('customer_id', $voucher->customer_id) == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }}
                            @if($customer->phone)
                                - {{ $customer->phone }}
                            @endif
                        </option>
                    @endforeach
                </select>
                @error('customer_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Optionally assign this profile to a specific customer</p>
            </div>

            <!-- User Capacity -->
            <div>
                <label for="user_capacity" class="block text-sm font-medium text-gray-700">
                    User Capacity <span class="text-red-500">*</span>
                </label>
                <input type="number" name="user_capacity" id="user_capacity" min="{{ $voucher->users_generated }}" max="1000" value="{{ old('user_capacity', $voucher->user_capacity) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('user_capacity') border-red-300 @enderror">
                @error('user_capacity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">
                    @if($voucher->users_generated > 0)
                        Minimum capacity: {{ $voucher->users_generated }} (already generated). You can increase this to allow generating more users.
                    @else
                        Maximum number of hotspot users that can be generated from this profile (1-1000)
                    @endif
                </p>
            </div>

            <!-- Price -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">
                    Price Per User <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price', $voucher->price) }}" required class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border border-gray-300 rounded-md @error('price') border-red-300 @enderror" placeholder="0.00">
                </div>
                @error('price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">
                    Notes
                </label>
                <textarea name="notes" id="notes" rows="3" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md @error('notes') border-red-300 @enderror" placeholder="Optional notes about this voucher profile">{{ old('notes', $voucher->notes) }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Status Info -->
            <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Profile Status Information</h4>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Current Status</dt>
                        <dd class="mt-1 font-medium text-gray-900">
                            @if($voucher->status === 'active')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Active
                                </span>
                            @elseif($voucher->status === 'inactive')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Inactive
                                </span>
                            @elseif($voucher->status === 'expired')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Expired
                                </span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Users Generated</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $voucher->users_generated }} / {{ $voucher->user_capacity }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Created</dt>
                        <dd class="mt-1 font-medium text-gray-900">{{ $voucher->created_at->format('M d, Y') }}</dd>
                    </div>
                    @if($voucher->mikrotik_profile_id)
                        <div>
                            <dt class="text-gray-500">MikroTik Profile ID</dt>
                            <dd class="mt-1 font-mono text-xs text-gray-900">{{ $voucher->mikrotik_profile_id }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('routers.vouchers.show', [$router, $voucher]) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Update Profile
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updatePriceFromPlan() {
    const select = document.getElementById('bandwidth_plan_id');
    const priceInput = document.getElementById('price');

    if (select.value) {
        const option = select.options[select.selectedIndex];
        const price = option.getAttribute('data-price');
        priceInput.value = parseFloat(price).toFixed(2);
    }
}
</script>
@endpush
