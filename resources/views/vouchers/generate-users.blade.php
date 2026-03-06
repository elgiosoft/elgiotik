@extends('layouts.app')

@section('title', 'Generate Hotspot Users')

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
                <h1 class="text-2xl font-bold text-gray-900">Generate Hotspot Users</h1>
                <p class="mt-1 text-sm text-gray-600">Profile #{{ $voucher->id }} - {{ $router->name }}</p>
            </div>
        </div>
    </div>

    <!-- Profile Info Card -->
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ $voucher->bandwidthPlan->name }}</h3>
                <div class="mt-2 grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-500">Bandwidth:</span>
                        <span class="ml-2 font-medium text-gray-900">{{ $voucher->bandwidthPlan->download_speed }}/{{ $voucher->bandwidthPlan->upload_speed }}</span>
                    </div>
                    @if($voucher->bandwidthPlan->validity_period)
                        <div>
                            <span class="text-gray-500">Validity:</span>
                            <span class="ml-2 font-medium text-gray-900">{{ $voucher->bandwidthPlan->validity_period }} hours</span>
                        </div>
                    @endif
                    <div>
                        <span class="text-gray-500">Price per user:</span>
                        <span class="ml-2 font-medium text-gray-900">${{ number_format($voucher->price, 2) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500">Capacity:</span>
                        <span class="ml-2 font-medium text-gray-900">{{ $voucher->users_generated }} / {{ $voucher->user_capacity }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Remaining Capacity Warning/Info -->
    @if($remainingCapacity === 0)
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">No Capacity Available</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <p>This profile has reached its maximum user capacity ({{ $voucher->user_capacity }}). Edit the profile to increase capacity before generating more users.</p>
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('routers.vouchers.edit', [$router, $voucher]) }}" class="inline-flex items-center px-3 py-2 border border-transparent text-sm font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200">
                            Edit Profile Capacity
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Available Capacity</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <p>You can generate up to <span class="font-bold">{{ $remainingCapacity }}</span> more user(s) from this profile.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Generate Form -->
        <div class="bg-white shadow-sm rounded-lg border border-gray-200">
            <form action="{{ route('routers.vouchers.generateUsers', [$router, $voucher]) }}" method="POST" class="p-6 space-y-6">
                @csrf

                <!-- Number of Users -->
                <div>
                    <label for="count" class="block text-sm font-medium text-gray-700">
                        Number of Users to Generate <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <input
                            type="number"
                            name="count"
                            id="count"
                            min="1"
                            max="{{ $remainingCapacity }}"
                            value="{{ old('count', min(10, $remainingCapacity)) }}"
                            required
                            class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('count') border-red-300 @enderror"
                            oninput="updateEstimate()"
                        >
                    </div>
                    @error('count')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Enter number between 1 and {{ $remainingCapacity }}</p>
                </div>

                <!-- Estimate -->
                <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Generation Summary</h4>
                    <dl class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">Users to create</dt>
                            <dd class="mt-1 font-bold text-gray-900 text-lg" id="user-count">{{ old('count', min(10, $remainingCapacity)) }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Total value</dt>
                            <dd class="mt-1 font-bold text-gray-900 text-lg" id="total-value">${{ number_format($voucher->price * min(10, $remainingCapacity), 2) }}</dd>
                        </div>
                        <div class="col-span-2">
                            <dt class="text-gray-500">What will happen:</dt>
                            <dd class="mt-2 text-gray-700 space-y-1">
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Random usernames and passwords will be generated</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Users will be saved to the database</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>System will attempt to sync users to the MikroTik router</span>
                                </div>
                                <div class="flex items-start gap-2">
                                    <svg class="w-4 h-4 text-green-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>You'll be able to print vouchers after generation</span>
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <a href="{{ route('routers.vouchers.show', [$router, $voucher]) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Generate Users
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function updateEstimate() {
    const count = parseInt(document.getElementById('count').value) || 0;
    const pricePerUser = {{ $voucher->price }};

    document.getElementById('user-count').textContent = count;
    document.getElementById('total-value').textContent = '$' + (count * pricePerUser).toFixed(2);
}
</script>
@endpush
