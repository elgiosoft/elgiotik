@extends('layouts.app')

@section('title', 'Batch Generate Vouchers')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <div class="flex items-center gap-4">
            <a href="{{ route('vouchers.index') }}" class="text-gray-400 hover:text-gray-600">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Batch Generate Vouchers</h1>
                <p class="mt-1 text-sm text-gray-600">Generate multiple vouchers at once with the same configuration</p>
            </div>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm text-blue-700">
                    Voucher codes will be automatically generated with unique identifiers. You can specify how many vouchers to create in a single batch.
                </p>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <form action="{{ route('vouchers.batch-store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Quantity -->
            <div>
                <label for="quantity" class="block text-sm font-medium text-gray-700">
                    Number of Vouchers <span class="text-red-500">*</span>
                </label>
                <input type="number" name="quantity" id="quantity" min="1" max="1000" value="{{ old('quantity', 10) }}" required class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border border-gray-300 rounded-md @error('quantity') border-red-300 @enderror">
                @error('quantity')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Enter the number of vouchers to generate (max: 1000)</p>
            </div>

            <!-- Code Prefix -->
            <div>
                <label for="prefix" class="block text-sm font-medium text-gray-700">
                    Code Prefix
                </label>
                <input type="text" name="prefix" id="prefix" value="{{ old('prefix', 'VOC') }}" maxlength="10" class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border border-gray-300 rounded-md @error('prefix') border-red-300 @enderror" placeholder="VOC">
                @error('prefix')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Optional prefix for voucher codes (e.g., VOC will generate VOC-XXXXX)</p>
            </div>

            <!-- Bandwidth Plan -->
            <div>
                <label for="bandwidth_plan_id" class="block text-sm font-medium text-gray-700">
                    Bandwidth Plan <span class="text-red-500">*</span>
                </label>
                <select name="bandwidth_plan_id" id="bandwidth_plan_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('bandwidth_plan_id') border-red-300 @enderror">
                    <option value="">Select a plan</option>
                    @foreach($plans ?? [] as $plan)
                        <option value="{{ $plan->id }}" {{ old('bandwidth_plan_id') == $plan->id ? 'selected' : '' }}>
                            {{ $plan->name }} - {{ $plan->speed ?? 'N/A' }}
                        </option>
                    @endforeach
                </select>
                @error('bandwidth_plan_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">All vouchers will be created with this plan</p>
            </div>

            <!-- Router -->
            <div>
                <label for="router_id" class="block text-sm font-medium text-gray-700">
                    Router <span class="text-red-500">*</span>
                </label>
                <select name="router_id" id="router_id" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('router_id') border-red-300 @enderror">
                    <option value="">Select a router</option>
                    @foreach($routers ?? [] as $router)
                        <option value="{{ $router->id }}" {{ old('router_id') == $router->id ? 'selected' : '' }}>
                            {{ $router->name }} ({{ $router->ip_address }})
                        </option>
                    @endforeach
                </select>
                @error('router_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">All vouchers will be assigned to this router</p>
            </div>

            <!-- Price -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">
                    Price per Voucher <span class="text-red-500">*</span>
                </label>
                <div class="mt-1 relative rounded-md shadow-sm">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">$</span>
                    </div>
                    <input type="number" name="price" id="price" step="0.01" min="0" value="{{ old('price') }}" required class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-7 pr-12 sm:text-sm border border-gray-300 rounded-md @error('price') border-red-300 @enderror" placeholder="0.00">
                </div>
                @error('price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">Each voucher will be created with this price</p>
            </div>

            <!-- Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">
                    Initial Status
                </label>
                <select name="status" id="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md @error('status') border-red-300 @enderror">
                    <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="disabled" {{ old('status') == 'disabled' ? 'selected' : '' }}>Disabled</option>
                </select>
                @error('status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">All vouchers will be created with this status</p>
            </div>

            <!-- Notes -->
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">
                    Notes
                </label>
                <textarea name="notes" id="notes" rows="3" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border border-gray-300 rounded-md @error('notes') border-red-300 @enderror" placeholder="Optional notes about this batch">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-sm text-gray-500">These notes will be added to all generated vouchers</p>
            </div>

            <!-- Summary Box -->
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200" x-data="{ quantity: {{ old('quantity', 10) }}, price: {{ old('price', 0) }} }">
                <h3 class="text-sm font-medium text-gray-900 mb-3">Generation Summary</h3>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500">Vouchers to Generate</dt>
                        <dd class="mt-1 font-semibold text-gray-900" x-text="quantity"></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500">Total Value</dt>
                        <dd class="mt-1 font-semibold text-gray-900">$<span x-text="(quantity * price).toFixed(2)"></span></dd>
                    </div>
                </dl>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
                <a href="{{ route('vouchers.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Generate Vouchers
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Update summary in real-time
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.getElementById('quantity');
    const priceInput = document.getElementById('price');

    function updateSummary() {
        const quantity = parseInt(quantityInput.value) || 0;
        const price = parseFloat(priceInput.value) || 0;

        // Alpine.js will handle the display updates
        quantityInput.dispatchEvent(new Event('input'));
        priceInput.dispatchEvent(new Event('input'));
    }

    quantityInput.addEventListener('input', updateSummary);
    priceInput.addEventListener('input', updateSummary);
});
</script>
@endpush
