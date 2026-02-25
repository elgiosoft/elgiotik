<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - VillageNet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-green-50 via-white to-emerald-50 min-h-screen">

    <!-- Animated Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-green-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-emerald-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-teal-300 rounded-full mix-blend-multiply filter blur-xl opacity-70 animate-blob animation-delay-4000"></div>
    </div>

    <div class="relative min-h-screen flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-3xl">

            <!-- Success Animation -->
            <div class="flex justify-center mb-8">
                <div class="relative">
                    <!-- Outer Ring -->
                    <div class="absolute inset-0 w-32 h-32 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full opacity-20 animate-ping"></div>
                    <!-- Middle Ring -->
                    <div class="relative w-32 h-32 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full flex items-center justify-center shadow-2xl">
                        <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center">
                            <svg class="w-14 h-14 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            <div class="text-center mb-12">
                <h1 class="text-5xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent mb-3">
                    Payment Successful!
                </h1>
                <p class="text-xl text-gray-600">Your internet plan is now ready to activate</p>
            </div>

            <!-- Main Card -->
            <div class="bg-white/80 backdrop-blur-xl rounded-3xl shadow-2xl border border-white/20 overflow-hidden mb-8">

                <!-- Transaction Details -->
                <div class="bg-gradient-to-r from-green-600 to-emerald-600 px-8 py-6">
                    <div class="flex items-center justify-between text-white">
                        <div>
                            <p class="text-green-100 text-sm mb-1">Plan Activated</p>
                            <h2 class="text-2xl font-bold">{{ $planName ?? 'VillageNet Premium' }}</h2>
                        </div>
                        <div class="text-right">
                            <p class="text-green-100 text-sm mb-1">Amount Paid</p>
                            <p class="text-3xl font-bold">${{ number_format($amount ?? 10, 2) }}</p>
                        </div>
                    </div>
                </div>

                <div class="p-8">
                    <!-- Transaction Info -->
                    <div class="grid md:grid-cols-3 gap-4 mb-8 pb-8 border-b border-gray-200">
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-sm text-gray-600 mb-1">Transaction ID</p>
                            <p class="font-mono font-semibold text-gray-900">#VN-TXN-{{ rand(10000, 99999) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-sm text-gray-600 mb-1">Date & Time</p>
                            <p class="font-semibold text-gray-900">{{ now()->format('M d, Y H:i') }}</p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4">
                            <p class="text-sm text-gray-600 mb-1">Payment Method</p>
                            <p class="font-semibold text-gray-900">{{ ucfirst($provider ?? 'MTN') }} Money</p>
                        </div>
                    </div>

                    <!-- Voucher Code Section -->
                    <div class="bg-gradient-to-br from-amber-50 to-orange-50 border-2 border-dashed border-orange-300 rounded-3xl p-8 mb-6">
                        <div class="text-center mb-6">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-orange-500 to-amber-600 rounded-2xl mb-4 shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                </svg>
                            </div>
                            <h3 class="text-2xl font-bold text-gray-900 mb-2">Your Voucher Code</h3>
                            <p class="text-gray-600">Use this code to connect to WiFi</p>
                        </div>

                        <!-- Code Display -->
                        <div class="bg-white rounded-2xl p-6 mb-6 shadow-lg">
                            <p class="text-center text-5xl font-bold font-mono tracking-wider bg-gradient-to-r from-orange-600 to-amber-600 bg-clip-text text-transparent">
                                {{ $voucherCode ?? 'VN-8822-991' }}
                            </p>
                        </div>

                        <!-- Copy Button -->
                        <button
                            onclick="copyVoucherCode()"
                            id="copyButton"
                            class="w-full bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white font-bold py-4 px-6 rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="copyIcon">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            <span id="copyText">Copy Voucher Code</span>
                        </button>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid md:grid-cols-2 gap-4">
                        <form action="{{ route('portal.activate') }}" method="POST">
                            @csrf
                            <input type="hidden" name="voucher_code" value="{{ $voucherCode ?? 'VN-8822-991' }}">
                            <button
                                type="submit"
                                class="w-full bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-bold py-4 px-6 rounded-2xl shadow-xl hover:shadow-2xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"/>
                                </svg>
                                <span>Activate & Connect Now</span>
                            </button>
                        </form>

                        <button
                            onclick="window.print()"
                            class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-4 px-6 rounded-2xl transition-all border-2 border-gray-200 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>Print Receipt</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="bg-blue-50/80 backdrop-blur-xl border-2 border-blue-200 rounded-2xl p-6">
                <div class="flex gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-900 mb-2">Next Steps</h3>
                        <ol class="text-sm text-gray-700 space-y-1">
                            <li class="flex items-start gap-2">
                                <span class="flex-shrink-0 w-5 h-5 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
                                <span>Save or copy your voucher code above</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="flex-shrink-0 w-5 h-5 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
                                <span>Click "Activate & Connect Now" to get your WiFi credentials</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="flex-shrink-0 w-5 h-5 bg-blue-600 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                <span>Connect to VillageNet WiFi and start browsing!</span>
                            </li>
                        </ol>
                    </div>
                </div>
            </div>

            <!-- Back to Home -->
            <div class="text-center mt-8">
                <a href="{{ route('portal.index') }}" class="text-gray-600 hover:text-indigo-600 font-semibold transition-colors inline-flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span>Back to Home</span>
                </a>
            </div>

        </div>
    </div>

    <script>
        function copyVoucherCode() {
            const voucherCode = "{{ $voucherCode ?? 'VN-8822-991' }}";
            navigator.clipboard.writeText(voucherCode).then(() => {
                const button = document.getElementById('copyButton');
                const text = document.getElementById('copyText');
                const icon = document.getElementById('copyIcon');

                text.textContent = 'Copied!';
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>';

                setTimeout(() => {
                    text.textContent = 'Copy Voucher Code';
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>';
                }, 2000);
            });
        }
    </script>

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

        @media print {
            body {
                background: white;
            }
            .fixed, button:not(.print-visible), a[href] {
                display: none !important;
            }
        }
    </style>
</body>
</html>
