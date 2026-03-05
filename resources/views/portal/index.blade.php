<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VillageNet WiFi Portal</title>
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

    <div class="min-h-full py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto space-y-8">

            <!-- Logo and Header -->
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl blur opacity-75 animate-pulse"></div>
                        <div class="relative bg-white rounded-2xl p-4 shadow-xl">
                            <svg class="w-16 h-16" fill="url(#gradient)" viewBox="0 0 24 24">
                                <defs>
                                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                        <stop offset="0%" style="stop-color:#2563eb;stop-opacity:1" />
                                        <stop offset="100%" style="stop-color:#9333ea;stop-opacity:1" />
                                    </linearGradient>
                                </defs>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" stroke="url(#gradient)" fill="none" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <h1 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                    VillageNet
                </h1>
                <p class="mt-2 text-sm text-gray-600">
                    Welcome to our WiFi Hotspot
                </p>
            </div>

            <!-- Main Content Grid -->
            <div class="grid md:grid-cols-3 gap-8" id="plans">
                <!-- Left: Login Card -->
                <div class="md:col-span-1">
                    <div class="bg-white rounded-2xl shadow-2xl p-8 space-y-6 border border-gray-100 sticky top-8">
                        <div class="text-center">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">Connect to WiFi</h2>
                            <p class="text-sm text-gray-600">Enter your voucher code to get online</p>
                        </div>

                        <form action="{{ route('portal.activate') }}" method="POST" class="space-y-6">
                            @csrf

                            <div>
                                <label for="voucher_code" class="block text-sm font-medium text-gray-700 mb-2">
                                    Voucher Code
                                </label>
                                <input
                                    id="voucher_code"
                                    name="voucher_code"
                                    type="text"
                                    required
                                    placeholder="Enter your 12-digit code"
                                    class="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm">
                                @error('voucher_code')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <button
                                type="submit"
                                class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-150 ease-in-out transform hover:scale-105 shadow-lg hover:shadow-xl">
                                Connect Now
                            </button>
                        </form>

                        <div class="text-center pt-4 border-t border-gray-200">
                            <p class="text-sm text-gray-600 mb-3">
                                Don't have a voucher code?
                            </p>
                            <p class="font-medium text-blue-600 text-sm">
                                Choose a plan on the right →
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Right: Plans Section -->
                <div class="md:col-span-2 md:pl-8">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 mb-2">
                            Choose Your Plan
                        </h2>
                        <p class="text-sm text-gray-600">Select the perfect internet package for your needs</p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                    @php
                        $featuredPlans = $bandwidthPlans->whereIn('name', [
                            'Basic 1 Hour',
                            'Standard 3 Hours',
                            'Premium Daily',
                            'Weekly Pack'
                        ])->values();
                    @endphp

                    @foreach($featuredPlans as $plan)
                    @php
                        $isPopular = $plan->name === 'Premium Daily';
                    @endphp

                    <div class="bg-white rounded-xl shadow-lg border @if($isPopular) border-purple-400 @else border-gray-200 @endif p-5 hover:shadow-xl transition-all duration-150 relative">

                        @if($isPopular)
                        <div class="absolute -top-2 left-1/2 -translate-x-1/2 bg-gradient-to-r from-blue-600 to-purple-600 text-white text-xs font-semibold px-3 py-0.5 rounded-full">
                            POPULAR
                        </div>
                        @endif

                        <div class="text-center mb-4 @if($isPopular) mt-1 @endif">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                {{ $plan->name }}
                            </h3>

                            <div class="mb-2">
                                <span class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                                    {{ number_format($plan->price, 0) }}
                                </span>
                                <span class="text-sm text-gray-600"> XAF</span>
                            </div>

                            <p class="text-xs text-gray-500">
                                @if($plan->validity_days)
                                    {{ $plan->validity_days }} Day{{ $plan->validity_days > 1 ? 's' : '' }}
                                @else
                                    {{ $plan->validity_hours }} Hour{{ $plan->validity_hours > 1 ? 's' : '' }}
                                @endif
                            </p>
                        </div>

                        <ul class="space-y-2 mb-4">
                            <li class="flex items-center gap-2 text-xs text-gray-700">
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>{{ str_replace(['M', 'K'], ['Mbps', 'Kbps'], $plan->download_speed) }}</span>
                            </li>
                            <li class="flex items-center gap-2 text-xs text-gray-700">
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>Unlimited data</span>
                            </li>
                            <li class="flex items-center gap-2 text-xs text-gray-700">
                                <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                <span>
                                    @if($plan->name === 'Basic 1 Hour') 1 device
                                    @elseif(in_array($plan->name, ['Standard 3 Hours', 'Premium Daily'])) 2 devices
                                    @else 3 devices
                                    @endif
                                </span>
                            </li>
                        </ul>

                        <form action="{{ route('portal.payment') }}" method="GET">
                            <input type="hidden" name="plan_id" value="{{ $plan->id }}">
                            <button
                                type="submit"
                                class="w-full py-2.5 px-4 border border-transparent text-sm font-semibold rounded-lg @if($isPopular) text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 @else text-gray-700 bg-gray-100 hover:bg-gray-200 @endif focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-150">
                                Select Plan
                            </button>
                        </form>
                    </div>
                    @endforeach
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500 pt-8">
                <p>&copy; {{ date('Y') }} VillageNet. All rights reserved.</p>
            </div>

        </div>
    </div>

</body>
</html>
