<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're Connected! - VillageNet</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="h-full bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50">

    <div class="min-h-full flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-3xl w-full space-y-8">

            <!-- Success Icon -->
            <div class="flex justify-center">
                <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full blur opacity-75 animate-pulse"></div>
                    <div class="relative w-24 h-24 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center shadow-xl">
                        <svg class="w-12 h-12 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.05 3.636a1 1 0 010 1.414 7 7 0 000 9.9 1 1 0 11-1.414 1.414 9 9 0 010-12.728 1 1 0 011.414 0zm9.9 0a1 1 0 011.414 0 9 9 0 010 12.728 1 1 0 11-1.414-1.414 7 7 0 000-9.9 1 1 0 010-1.414zM7.879 6.464a1 1 0 010 1.414 3 3 0 000 4.243 1 1 0 11-1.415 1.414 5 5 0 010-7.07 1 1 0 011.415 0zm4.242 0a1 1 0 011.415 0 5 5 0 010 7.072 1 1 0 01-1.415-1.415 3 3 0 000-4.242 1 1 0 010-1.415zM10 9a1 1 0 011 1v.01a1 1 0 11-2 0V10a1 1 0 011-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            <div class="text-center">
                <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 mb-2">
                    You're Connected!
                </h1>
                <p class="text-gray-600">Your WiFi credentials are ready</p>
            </div>

            <!-- Main Credentials Card -->
            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">

                <!-- Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6">
                    <div class="flex items-center gap-3 text-white">
                        <div class="w-12 h-12 bg-white/20 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold">WiFi Login Credentials</h2>
                            <p class="text-blue-100 text-sm">Use these to connect to VillageNet</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 space-y-6">
                    <!-- Username -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wide">
                            Username
                        </label>
                        <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <code class="text-2xl font-mono font-bold text-gray-900">{{ $username ?? 'user_abc12345' }}</code>
                                <button
                                    onclick="copyText('{{ $username ?? 'user_abc12345' }}', 'username')"
                                    id="copy-username"
                                    class="p-2 bg-gray-200 hover:bg-blue-600 hover:text-white rounded-lg transition-all">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Password -->
                    <div x-data="{ showPassword: false }">
                        <label class="block text-sm font-semibold text-gray-700 mb-2 uppercase tracking-wide">
                            Password
                        </label>
                        <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <code class="text-2xl font-mono font-bold text-gray-900" x-text="showPassword ? '{{ $password ?? 'SecurePass123' }}' : '••••••••••••'"></code>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="button"
                                        @click="showPassword = !showPassword"
                                        class="p-2 bg-gray-200 hover:bg-blue-600 hover:text-white rounded-lg transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!showPassword">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="showPassword" style="display: none;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                        </svg>
                                    </button>
                                    <button
                                        onclick="copyText('{{ $password ?? 'SecurePass123' }}', 'password')"
                                        id="copy-password"
                                        class="p-2 bg-gray-200 hover:bg-blue-600 hover:text-white rounded-lg transition-all">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Voucher Reference -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                </svg>
                                <span class="text-sm text-gray-700 font-medium">Voucher Code:</span>
                            </div>
                            <code class="text-sm font-mono font-bold text-blue-600">{{ $voucherCode ?? 'VN-8822-991' }}</code>
                        </div>
                    </div>

                    <!-- Connection Instructions -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border-2 border-blue-200 rounded-xl p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4">How to Connect</h3>
                        <ol class="space-y-3">
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-6 h-6 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-full flex items-center justify-center font-bold text-sm">1</span>
                                <div>
                                    <p class="font-semibold text-gray-900">Connect to WiFi Network</p>
                                    <p class="text-sm text-gray-600">Look for <strong>"VillageNet"</strong> in your WiFi settings</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-6 h-6 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-full flex items-center justify-center font-bold text-sm">2</span>
                                <div>
                                    <p class="font-semibold text-gray-900">Open Any Website</p>
                                    <p class="text-sm text-gray-600">You'll be redirected to the login page</p>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <span class="flex-shrink-0 w-6 h-6 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-full flex items-center justify-center font-bold text-sm">3</span>
                                <div>
                                    <p class="font-semibold text-gray-900">Enter Your Credentials</p>
                                    <p class="text-sm text-gray-600">Use the username and password above</p>
                                </div>
                            </li>
                        </ol>
                    </div>

                    <!-- Action Buttons -->
                    <div class="grid md:grid-cols-2 gap-4">
                        <a
                            href="{{ route('portal.index') }}"
                            class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg shadow-lg hover:shadow-xl transition-all transform hover:scale-105 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                            <span>Back to Home</span>
                        </a>

                        <button
                            onclick="window.print()"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-3 px-6 rounded-lg transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                            </svg>
                            <span>Print Credentials</span>
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        function copyText(text, type) {
            navigator.clipboard.writeText(text).then(() => {
                const button = document.getElementById('copy-' + type);
                const originalHTML = button.innerHTML;

                button.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>';
                button.classList.add('bg-green-500', 'text-white');

                setTimeout(() => {
                    button.innerHTML = originalHTML;
                    button.classList.remove('bg-green-500', 'text-white');
                }, 2000);
            });
        }
    </script>

    <style>
        @media print {
            body {
                background: white;
            }
            button, a[href] {
                display: none !important;
            }
        }
    </style>
</body>
</html>
