@extends('layouts.app')

@section('title', 'Create Voucher Profile')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <div class="flex items-center gap-4">
            <a href="{{ route('routers.vouchers.index', $router) }}" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Create Voucher Profile</h1>
                <p class="mt-1 text-sm text-gray-600">Create a bandwidth profile template for {{ $router->name }}</p>
            </div>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">About Voucher Profiles</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>A voucher profile is a template based on a bandwidth plan. Once created, you can generate multiple hotspot users (with unique usernames and passwords) from this profile. The profile will be automatically synced to your MikroTik router.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <form action="{{ route('routers.vouchers.store', $router) }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Bandwidth Plan -->
            <div>
                <label for="bandwidth_plan_id" class="block text-sm font-medium text-gray-700">
                    Bandwidth Plan Template <span class="text-red-500">*</span>
                </label>
                <select name="bandwidth_plan_id" id="bandwidth_plan_id" required onchange="updatePriceFromPlan()" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('bandwidth_plan_id') border-red-300 @enderror">
                    <option value="">Select a bandwidth plan</option>
                    @foreach($bandwidthPlans ?? [] as $plan)
                        <option value="{{ $plan->id }}"
                                data-price="{{ $plan->price }}"
                                data-download="{{ $plan->download_speed }}"
                                data-upload="{{ $plan->upload_speed }}"
                                data-validity="{{ $plan->validity_period }}"
                                {{ old('bandwidth_plan_id') == $plan->id ? 'selected' : '' }}>
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
                <p class="mt-1 text-sm text-gray-500">Select the bandwidth plan that will be used as a template for this voucher profile</p>
            </div>

            <!-- User Capacity -->
            <div>
                <label for="user_capacity" class="block text-sm font-medium text-gray-700">
                    User Capacity <span class="text-red-500">*</span>
                </label>
                <input type="number" name="user_capacity" id="user_capacity" min="1" max="1000" value="{{ old('user_capacity', 10) }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('user_capacity') border-red-300 @enderror">
                @error('user_capacity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Maximum number of hotspot users that can be generated from this voucher profile (1-1000)</p>
            </div>

            <!-- Price -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">
                    Price Per User
                </label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price') }}" class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border border-gray-300 rounded-md @error('price') border-red-300 @enderror" placeholder="0.00">
                </div>
                @error('price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Leave empty to use the bandwidth plan's default price</p>
            </div>

            <!-- Auto-sync to Router -->
            <div>
                <div class="flex items-start">
                    <div class="flex items-center h-5">
                        <input type="checkbox" name="auto_sync" id="auto_sync" checked value="1" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                    </div>
                    <div class="ml-3 text-sm">
                        <label for="auto_sync" class="font-medium text-gray-700">Automatically sync profile to router</label>
                        <p class="text-gray-500">The bandwidth profile will be created on the MikroTik router immediately after creating this voucher</p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">
                    Notes
                </label>
                <textarea name="notes" id="notes" rows="3" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md @error('notes') border-red-300 @enderror" placeholder="Optional notes about this voucher profile">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('routers.vouchers.index', $router) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Create Voucher Profile
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
