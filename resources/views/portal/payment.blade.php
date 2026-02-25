<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Payment - VillageNet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen">

    <!-- Animated Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-pink-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
    </div>

    <div class="relative min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-2xl">

            <!-- Back Button -->
            <a href="{{ route('portal.index') }}" class="inline-flex items-center gap-2 text-gray-700 hover:text-indigo-600 font-semibold mb-8 transition-colors group">
                <svg class="w-5 h-5 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                <span>Back to Home</span>
            </a>

            <!-- Main Card -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden">

                <!-- Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-8 py-8">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-16 h-16 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Complete Payment</h1>
                            <p class="text-indigo-100">Secure mobile money payment</p>
                        </div>
                    </div>

                    <!-- Progress Steps -->
                    <div class="flex items-center justify-between max-w-md mx-auto mt-8">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center font-bold text-indigo-600 shadow-lg">1</div>
                            <span class="ml-2 text-white font-semibold text-sm">Select Provider</span>
                        </div>
                        <div class="flex-1 h-1 bg-white/30 mx-4"></div>
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-white/30 rounded-full flex items-center justify-center font-bold text-white">2</div>
                            <span class="ml-2 text-white/70 font-semibold text-sm">Confirm</span>
                        </div>
                    </div>
                </div>

                <!-- Form Content -->
                <div class="p-8">
                    <form id="paymentForm" x-data="paymentHandler()" @submit.prevent="submitPayment">
                        @csrf
                        <input type="hidden" name="plan_id" value="{{ $bandwidthPlan->id }}">

                        <!-- Selected Plan Summary -->
                        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-6 mb-8 border-2 border-indigo-100">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <p class="text-sm text-gray-600 mb-1">Selected Plan</p>
                                    <h3 class="text-2xl font-bold text-gray-900">{{ $bandwidthPlan->name }}</h3>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600 mb-1">Total Amount</p>
                                    <p class="text-3xl font-bold bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                        ${{ number_format($bandwidthPlan->price, 2) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-600">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <span>{{ $bandwidthPlan->download_speed }} / {{ $bandwidthPlan->upload_speed }}</span>
                                </div>
                                <span>•</span>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span>
                                        @if($bandwidthPlan->validity_days > 0)
                                            {{ $bandwidthPlan->validity_days }} {{ Str::plural('Day', $bandwidthPlan->validity_days) }}
                                        @elseif($bandwidthPlan->validity_hours > 0)
                                            {{ $bandwidthPlan->validity_hours }} {{ Str::plural('Hour', $bandwidthPlan->validity_hours) }}
                                        @else
                                            Unlimited
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1: Select Payment Provider -->
                        <div class="mb-8">
                            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="w-8 h-8 bg-gradient-to-br from-indigo-600 to-purple-600 text-white rounded-lg flex items-center justify-center text-sm">1</span>
                                Select Payment Provider
                            </h2>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- MTN Money -->
                                <label class="relative cursor-pointer group">
                                    <input
                                        type="radio"
                                        name="provider"
                                        value="mtn"
                                        x-model="selectedProvider"
                                        class="sr-only peer"
                                        required>
                                    <div class="bg-gradient-to-br from-white to-gray-50 border-3 border-gray-300 peer-checked:border-yellow-400 peer-checked:shadow-xl rounded-2xl p-6 transition-all group-hover:shadow-lg">
                                        <div class="flex flex-col items-center text-center">
                                            <div class="w-20 h-20 bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg">
                                                <span class="text-white font-bold text-2xl">MTN</span>
                                            </div>
                                            <h3 class="text-lg font-bold text-gray-900 mb-1">MTN Money</h3>
                                            <p class="text-sm text-gray-600">Mobile Money</p>
                                        </div>
                                        <!-- Checkmark -->
                                        <div class="absolute top-4 right-4 w-8 h-8 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full items-center justify-center shadow-lg hidden peer-checked:flex">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                </label>

                                <!-- Orange Money -->
                                <label class="relative cursor-pointer group">
                                    <input
                                        type="radio"
                                        name="provider"
                                        value="orange"
                                        x-model="selectedProvider"
                                        class="sr-only peer"
                                        required>
                                    <div class="bg-gradient-to-br from-white to-gray-50 border-3 border-gray-300 peer-checked:border-orange-400 peer-checked:shadow-xl rounded-2xl p-6 transition-all group-hover:shadow-lg">
                                        <div class="flex flex-col items-center text-center">
                                            <div class="w-20 h-20 bg-gradient-to-br from-orange-400 to-orange-600 rounded-2xl flex items-center justify-center mb-4 shadow-lg">
                                                <svg class="w-10 h-10 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/>
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                            <h3 class="text-lg font-bold text-gray-900 mb-1">Orange Money</h3>
                                            <p class="text-sm text-gray-600">Mobile Money</p>
                                        </div>
                                        <!-- Checkmark -->
                                        <div class="absolute top-4 right-4 w-8 h-8 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full items-center justify-center shadow-lg hidden peer-checked:flex">
                                            <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @error('provider')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Step 2: Enter Phone Number -->
                        <div class="mb-8">
                            <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="w-8 h-8 bg-gradient-to-br from-indigo-600 to-purple-600 text-white rounded-lg flex items-center justify-center text-sm">2</span>
                                Enter Your Phone Number
                            </h2>

                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </div>
                                <input
                                    type="tel"
                                    name="phone_number"
                                    id="phone_number"
                                    value="{{ old('phone_number') }}"
                                    placeholder="+237 6XX XXX XXX"
                                    class="w-full pl-12 pr-4 py-4 text-lg bg-white border-2 border-gray-200 rounded-2xl focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 transition-all outline-none @error('phone_number') border-red-400 @enderror"
                                    required>
                            </div>
                            @error('phone_number')
                                <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    {{ $message }}
                                </p>
                            @enderror

                            <!-- Info Box -->
                            <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                                <div class="flex gap-3">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-blue-900 mb-1">Payment Process</h4>
                                        <p class="text-sm text-blue-800">
                                            After clicking confirm, you'll receive a <strong>USSD push notification</strong> on your phone. Enter your <strong>Mobile Money PIN</strong> to complete the payment.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex items-center gap-4">
                            <a
                                href="{{ route('portal.index') }}"
                                class="flex-1 text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-4 px-6 rounded-2xl transition-all border-2 border-gray-200">
                                Cancel
                            </a>
                            <button
                                type="submit"
                                :disabled="loading"
                                class="flex-1 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-4 px-6 rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span x-show="!loading">Confirm Payment</span>
                                <span x-show="loading" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Processing...
                                </span>
                                <svg x-show="!loading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Badge -->
            <div class="mt-8 text-center">
                <div class="inline-flex items-center gap-2 text-gray-600 text-sm">
                    <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Secured by VillageNet Payment Gateway • SSL Encrypted</span>
                </div>
            </div>

        </div>
    </div>

    <!-- Payment Status Modal -->
    <div x-data="{ show: false }"
         x-show="show"
         @show-payment-modal.window="show = true"
         @close-payment-modal.window="show = false"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">

        <!-- Backdrop -->
        <div class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal Content -->
        <div class="flex items-center justify-center min-h-screen px-4 py-12">
            <div class="relative bg-white rounded-3xl shadow-2xl max-w-2xl w-full p-8 transform transition-all"
                 @click.away="false">

                <div id="payment-status-content">
                    <!-- Content will be loaded via AJAX -->
                </div>

            </div>
        </div>
    </div>

    <style>
        @keyframes blob {
            0% { transform: translate(0px, 0px) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
            100% { transform: translate(0px, 0px) scale(1); }
        }

        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>

    <script>
        function paymentHandler() {
            return {
                selectedProvider: 'mtn',
                loading: false,
                statusCheckInterval: null,

                async submitPayment(event) {
                    this.loading = true;
                    const form = event.target;
                    const formData = new FormData(form);

                    try {
                        const response = await fetch('{{ route("portal.payment.process") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: formData
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            // Show payment status modal
                            window.dispatchEvent(new CustomEvent('show-payment-modal'));

                            // Start checking payment status
                            this.startStatusCheck(data.transaction_id);
                        } else {
                            // Show error message
                            alert(data.message || 'Payment initiation failed. Please try again.');
                        }
                    } catch (error) {
                        console.error('Payment error:', error);
                        alert('An error occurred while processing your payment. Please try again.');
                    } finally {
                        this.loading = false;
                    }
                },

                async startStatusCheck(transactionId) {
                    // Load initial status
                    await this.checkPaymentStatus(transactionId);

                    // Poll every 3 seconds
                    this.statusCheckInterval = setInterval(async () => {
                        await this.checkPaymentStatus(transactionId);
                    }, 3000);
                },

                async checkPaymentStatus(transactionId) {
                    try {
                        const response = await fetch(`{{ route("portal.payment-status") }}?transaction_id=${transactionId}&ajax=1`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });

                        const data = await response.json();

                        // Update modal content
                        document.getElementById('payment-status-content').innerHTML = this.renderStatusContent(data);

                        // If completed or failed, stop polling and redirect after delay
                        if (data.status === 'completed' || data.status === 'failed') {
                            clearInterval(this.statusCheckInterval);

                            if (data.status === 'completed' && data.redirect_url) {
                                setTimeout(() => {
                                    window.location.href = data.redirect_url;
                                }, 2000);
                            }
                        }
                    } catch (error) {
                        console.error('Status check error:', error);
                    }
                },

                renderStatusContent(data) {
                    if (data.status === 'completed') {
                        return `
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full mb-6 shadow-2xl">
                                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h1 class="text-4xl font-bold text-gray-900 mb-2">Payment Successful!</h1>
                                <p class="text-gray-600 mb-6">Redirecting to your voucher...</p>
                                <div class="animate-pulse flex justify-center">
                                    <div class="flex space-x-2">
                                        <div class="w-3 h-3 bg-indigo-600 rounded-full"></div>
                                        <div class="w-3 h-3 bg-indigo-600 rounded-full"></div>
                                        <div class="w-3 h-3 bg-indigo-600 rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                        `;
                    } else if (data.status === 'failed') {
                        return `
                            <div class="text-center">
                                <div class="inline-flex items-center justify-center w-24 h-24 bg-gradient-to-br from-red-400 to-rose-600 rounded-full mb-6 shadow-2xl">
                                    <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <h1 class="text-4xl font-bold text-gray-900 mb-2">Payment Failed</h1>
                                <p class="text-gray-600 mb-6">${data.failure_reason || 'Your payment could not be processed'}</p>
                                <a href="{{ route('portal.index') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-4 px-8 rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                    </svg>
                                    <span>Try Again</span>
                                </a>
                            </div>
                        `;
                    } else {
                        // Processing state
                        return `
                            <div class="text-center">
                                <!-- Animated Spinner -->
                                <div class="inline-flex items-center justify-center w-24 h-24 mb-6">
                                    <div class="relative">
                                        <div class="w-24 h-24 border-8 border-blue-200 rounded-full"></div>
                                        <div class="w-24 h-24 border-8 border-blue-600 rounded-full border-t-transparent animate-spin absolute top-0 left-0"></div>
                                    </div>
                                </div>

                                <h1 class="text-4xl font-bold bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent mb-3">
                                    Processing Payment
                                </h1>
                                <p class="text-xl text-gray-600 mb-8">Please wait while we confirm your payment</p>

                                <!-- Status Badge -->
                                <div class="inline-flex items-center gap-2 bg-blue-100 text-blue-800 px-6 py-3 rounded-full font-semibold mb-8">
                                    <div class="w-2 h-2 bg-blue-600 rounded-full animate-pulse"></div>
                                    <span class="capitalize">${data.status}</span>
                                </div>

                                <!-- Instructions -->
                                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-2xl p-6 mb-6 text-left">
                                    <div class="flex gap-4">
                                        <div class="flex-shrink-0">
                                            <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                                                </svg>
                                            </div>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-bold text-gray-900 mb-2">Check Your Phone</h3>
                                            <p class="text-sm text-gray-700 mb-3">
                                                A USSD push notification has been sent to <strong>${data.customer_phone || 'your phone'}</strong>.
                                                Enter your Mobile Money PIN to complete the payment.
                                            </p>
                                            <div class="text-xs text-gray-600">
                                                <strong>Transaction ID:</strong> ${data.transaction_id}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Auto Check Notice -->
                                <div class="text-center">
                                    <p class="text-sm text-gray-600">
                                        <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                                        </svg>
                                        Automatically checking status...
                                    </p>
                                </div>
                            </div>
                        `;
                    }
                }
            }
        }
    </script>
</body>
</html>
