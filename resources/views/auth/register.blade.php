<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Register - ElgioTik</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

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
        <div class="max-w-md w-full space-y-8">
            <!-- Logo and Header -->
            <div class="text-center">
                <div class="flex justify-center mb-6">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl blur opacity-75 animate-pulse"></div>
                        <div class="relative bg-white rounded-2xl p-4 shadow-xl">
                            <svg class="w-16 h-16 text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2L2 7v10c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V7l-10-5zm0 18c-4.41 0-8-3.59-8-8V8.32l8-3.56 8 3.56V12c0 4.41-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <h2 class="text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                    ElgioTik
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Create your account
                </p>
            </div>

            <!-- Registration Form Card -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 space-y-6 border border-gray-100"
                 x-data="registerForm()">
                <form @submit.prevent="submitForm" method="POST" action="{{ route('register') }}" class="space-y-6">
                    @csrf

                    <!-- Name Field -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Full Name
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                            <input
                                id="name"
                                name="name"
                                type="text"
                                autocomplete="name"
                                required
                                value="{{ old('name') }}"
                                x-model="name"
                                @input="clearError('name')"
                                class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm"
                                :class="{'border-red-500': errors.name}"
                                placeholder="John Doe"
                            >
                        </div>
                        @error('name')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                        <template x-if="errors.name">
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <span x-text="errors.name"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            Email Address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-envelope text-gray-400"></i>
                            </div>
                            <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                value="{{ old('email') }}"
                                x-model="email"
                                @input="clearError('email')"
                                class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm"
                                :class="{'border-red-500': errors.email}"
                                placeholder="you@example.com"
                            >
                        </div>
                        @error('email')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                        <template x-if="errors.email">
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <span x-text="errors.email"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Phone Number Field -->
                    <div>
                        <label for="phone_number" class="block text-sm font-medium text-gray-700 mb-2">
                            Phone Number
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                            <input
                                id="phone_number"
                                name="phone_number"
                                type="tel"
                                autocomplete="tel"
                                required
                                value="{{ old('phone_number') }}"
                                x-model="phone_number"
                                @input="clearError('phone_number')"
                                class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm"
                                :class="{'border-red-500': errors.phone_number}"
                                placeholder="237XXXXXXXXX"
                            >
                        </div>
                        @error('phone_number')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                        <template x-if="errors.phone_number">
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <span x-text="errors.phone_number"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input
                                id="password"
                                name="password"
                                :type="showPassword ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                x-model="password"
                                @input="clearError('password')"
                                class="appearance-none block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm"
                                :class="{'border-red-500': errors.password}"
                                placeholder="Enter your password"
                            >
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition duration-150"
                            >
                                <i :class="showPassword ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                        <template x-if="errors.password">
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <span x-text="errors.password"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Password Confirmation Field -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-lock text-gray-400"></i>
                            </div>
                            <input
                                id="password_confirmation"
                                name="password_confirmation"
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                autocomplete="new-password"
                                required
                                x-model="password_confirmation"
                                @input="clearError('password_confirmation')"
                                class="appearance-none block w-full pl-10 pr-10 py-3 border border-gray-300 rounded-lg placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-150 ease-in-out sm:text-sm"
                                :class="{'border-red-500': errors.password_confirmation}"
                                placeholder="Confirm your password"
                            >
                            <button
                                type="button"
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600 transition duration-150"
                            >
                                <i :class="showPasswordConfirm ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                        <template x-if="errors.password_confirmation">
                            <p class="mt-2 text-sm text-red-600 flex items-center">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                <span x-text="errors.password_confirmation"></span>
                            </p>
                        </template>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button
                            type="submit"
                            :disabled="loading"
                            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-150 ease-in-out transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none shadow-lg hover:shadow-xl"
                        >
                            <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                                <template x-if="!loading">
                                    <i class="fas fa-user-plus text-white opacity-75"></i>
                                </template>
                                <template x-if="loading">
                                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </template>
                            </span>
                            <span x-text="loading ? 'Creating account...' : 'Create Account'"></span>
                        </button>
                    </div>
                </form>

                <!-- Sign In Link -->
                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        Already have an account?
                        <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-500 transition duration-150">
                            Sign in
                        </a>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div class="text-center text-sm text-gray-500">
                <p>&copy; {{ date('Y') }} ElgioTik. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        function registerForm() {
            return {
                name: '{{ old('name') }}',
                email: '{{ old('email') }}',
                phone_number: '{{ old('phone_number') }}',
                password: '',
                password_confirmation: '',
                showPassword: false,
                showPasswordConfirm: false,
                loading: false,
                errors: {},

                submitForm(event) {
                    this.loading = true;
                    this.errors = {};

                    // Validate form
                    if (!this.validateForm()) {
                        this.loading = false;
                        return;
                    }

                    // Submit the form
                    event.target.submit();
                },

                validateForm() {
                    let isValid = true;

                    // Name validation
                    if (!this.name || this.name.trim() === '') {
                        this.errors.name = 'Name is required';
                        isValid = false;
                    }

                    // Email validation
                    if (!this.email) {
                        this.errors.email = 'Email is required';
                        isValid = false;
                    } else if (!this.isValidEmail(this.email)) {
                        this.errors.email = 'Please enter a valid email address';
                        isValid = false;
                    }

                    // Phone validation
                    if (!this.phone_number) {
                        this.errors.phone_number = 'Phone number is required';
                        isValid = false;
                    }

                    // Password validation
                    if (!this.password) {
                        this.errors.password = 'Password is required';
                        isValid = false;
                    } else if (this.password.length < 8) {
                        this.errors.password = 'Password must be at least 8 characters';
                        isValid = false;
                    }

                    // Password confirmation validation
                    if (!this.password_confirmation) {
                        this.errors.password_confirmation = 'Please confirm your password';
                        isValid = false;
                    } else if (this.password !== this.password_confirmation) {
                        this.errors.password_confirmation = 'Passwords do not match';
                        isValid = false;
                    }

                    return isValid;
                },

                isValidEmail(email) {
                    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    return re.test(email);
                },

                clearError(field) {
                    delete this.errors[field];
                }
            }
        }
    </script>
</body>
</html>
