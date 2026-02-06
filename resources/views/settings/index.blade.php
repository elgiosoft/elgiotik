@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex-1 min-w-0">
                <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                    System Settings
                </h2>
                <p class="mt-1 text-sm text-gray-500">
                    Configure application settings and preferences
                </p>
            </div>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" x-data="{ activeTab: 'general' }">
            @csrf
            @method('PUT')

            <!-- Tabs -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                        <button
                            type="button"
                            @click="activeTab = 'general'"
                            :class="activeTab === 'general' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                        >
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                General
                            </div>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'hotspot'"
                            :class="activeTab === 'hotspot' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                        >
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0" />
                                </svg>
                                Hotspot
                            </div>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'billing'"
                            :class="activeTab === 'billing' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                        >
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Billing
                            </div>
                        </button>

                        <button
                            type="button"
                            @click="activeTab = 'system'"
                            :class="activeTab === 'system' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                        >
                            <div class="flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                </svg>
                                System
                            </div>
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- General Settings -->
                    <div x-show="activeTab === 'general'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">General Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- App Name -->
                            <div>
                                <label for="app_name" class="block text-sm font-medium text-gray-700 mb-1">Application Name</label>
                                <input type="text" name="settings[app_name]" id="app_name" value="{{ $settings['general']['app_name']['value'] ?? 'ElgioTik' }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['general']['app_name']['description'] ?? '' }}</p>
                            </div>

                            <!-- Currency -->
                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700 mb-1">Currency Code</label>
                                <input type="text" name="settings[currency]" id="currency" value="{{ $settings['general']['currency']['value'] ?? 'USD' }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['general']['currency']['description'] ?? '' }}</p>
                            </div>

                            <!-- Currency Symbol -->
                            <div>
                                <label for="currency_symbol" class="block text-sm font-medium text-gray-700 mb-1">Currency Symbol</label>
                                <input type="text" name="settings[currency_symbol]" id="currency_symbol" value="{{ $settings['general']['currency_symbol']['value'] ?? '$' }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['general']['currency_symbol']['description'] ?? '' }}</p>
                            </div>

                            <!-- Timezone -->
                            <div>
                                <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                                <input type="text" name="settings[timezone]" id="timezone" value="{{ $settings['general']['timezone']['value'] ?? 'UTC' }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['general']['timezone']['description'] ?? '' }}</p>
                            </div>

                            <!-- Date Format -->
                            <div>
                                <label for="date_format" class="block text-sm font-medium text-gray-700 mb-1">Date Format</label>
                                <input type="text" name="settings[date_format]" id="date_format" value="{{ $settings['general']['date_format']['value'] ?? 'Y-m-d' }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['general']['date_format']['description'] ?? '' }}</p>
                            </div>

                            <!-- Time Format -->
                            <div>
                                <label for="time_format" class="block text-sm font-medium text-gray-700 mb-1">Time Format</label>
                                <input type="text" name="settings[time_format]" id="time_format" value="{{ $settings['general']['time_format']['value'] ?? 'H:i:s' }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['general']['time_format']['description'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hotspot Settings -->
                    <div x-show="activeTab === 'hotspot'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Hotspot Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Voucher Code Prefix -->
                            <div>
                                <label for="voucher_code_prefix" class="block text-sm font-medium text-gray-700 mb-1">Voucher Code Prefix</label>
                                <input type="text" name="settings[voucher_code_prefix]" id="voucher_code_prefix" value="{{ $settings['hotspot']['voucher_code_prefix']['value'] ?? '' }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['hotspot']['voucher_code_prefix']['description'] ?? '' }}</p>
                            </div>

                            <!-- Voucher Code Length -->
                            <div>
                                <label for="voucher_code_length" class="block text-sm font-medium text-gray-700 mb-1">Voucher Code Length</label>
                                <input type="number" name="settings[voucher_code_length]" id="voucher_code_length" value="{{ $settings['hotspot']['voucher_code_length']['value'] ?? 12 }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['hotspot']['voucher_code_length']['description'] ?? '' }}</p>
                            </div>

                            <!-- Default Session Timeout -->
                            <div>
                                <label for="default_session_timeout" class="block text-sm font-medium text-gray-700 mb-1">Default Session Timeout (seconds)</label>
                                <input type="number" name="settings[default_session_timeout]" id="default_session_timeout" value="{{ $settings['hotspot']['default_session_timeout']['value'] ?? 3600 }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['hotspot']['default_session_timeout']['description'] ?? '' }}</p>
                            </div>

                            <!-- Default Idle Timeout -->
                            <div>
                                <label for="default_idle_timeout" class="block text-sm font-medium text-gray-700 mb-1">Default Idle Timeout (seconds)</label>
                                <input type="number" name="settings[default_idle_timeout]" id="default_idle_timeout" value="{{ $settings['hotspot']['default_idle_timeout']['value'] ?? 600 }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['hotspot']['default_idle_timeout']['description'] ?? '' }}</p>
                            </div>

                            <!-- Max Concurrent Sessions -->
                            <div>
                                <label for="max_concurrent_sessions" class="block text-sm font-medium text-gray-700 mb-1">Max Concurrent Sessions</label>
                                <input type="number" name="settings[max_concurrent_sessions]" id="max_concurrent_sessions" value="{{ $settings['hotspot']['max_concurrent_sessions']['value'] ?? 1 }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['hotspot']['max_concurrent_sessions']['description'] ?? '' }}</p>
                            </div>

                            <!-- Auto Disconnect on Expire -->
                            <div class="flex items-center">
                                <input type="hidden" name="settings[auto_disconnect_on_expire]" value="0">
                                <input type="checkbox" name="settings[auto_disconnect_on_expire]" id="auto_disconnect_on_expire" value="1" {{ ($settings['hotspot']['auto_disconnect_on_expire']['value'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="auto_disconnect_on_expire" class="ml-2 block text-sm text-gray-700">
                                    Auto-disconnect users when voucher expires
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Billing Settings -->
                    <div x-show="activeTab === 'billing'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Billing Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Enable Billing -->
                            <div class="flex items-center md:col-span-2">
                                <input type="hidden" name="settings[enable_billing]" value="0">
                                <input type="checkbox" name="settings[enable_billing]" id="enable_billing" value="1" {{ ($settings['billing']['enable_billing']['value'] ?? true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="enable_billing" class="ml-2 block text-sm text-gray-700">
                                    Enable billing and payment features
                                </label>
                            </div>

                            <!-- Tax Rate -->
                            <div>
                                <label for="tax_rate" class="block text-sm font-medium text-gray-700 mb-1">Tax Rate (%)</label>
                                <input type="number" name="settings[tax_rate]" id="tax_rate" value="{{ $settings['billing']['tax_rate']['value'] ?? 0 }}" min="0" max="100" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['billing']['tax_rate']['description'] ?? '' }}</p>
                            </div>

                            <!-- Invoice Prefix -->
                            <div>
                                <label for="invoice_prefix" class="block text-sm font-medium text-gray-700 mb-1">Invoice Prefix</label>
                                <input type="text" name="settings[invoice_prefix]" id="invoice_prefix" value="{{ $settings['billing']['invoice_prefix']['value'] ?? 'INV-' }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['billing']['invoice_prefix']['description'] ?? '' }}</p>
                            </div>

                            <!-- Invoice Number Length -->
                            <div>
                                <label for="invoice_number_length" class="block text-sm font-medium text-gray-700 mb-1">Invoice Number Length</label>
                                <input type="number" name="settings[invoice_number_length]" id="invoice_number_length" value="{{ $settings['billing']['invoice_number_length']['value'] ?? 6 }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['billing']['invoice_number_length']['description'] ?? '' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- System Settings -->
                    <div x-show="activeTab === 'system'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" style="display: none;">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">System Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Enable Email Notifications -->
                            <div class="flex items-center md:col-span-2">
                                <input type="hidden" name="settings[enable_email_notifications]" value="0">
                                <input type="checkbox" name="settings[enable_email_notifications]" id="enable_email_notifications" value="1" {{ ($settings['system']['enable_email_notifications']['value'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="enable_email_notifications" class="ml-2 block text-sm text-gray-700">
                                    Enable email notifications
                                </label>
                            </div>

                            <!-- Enable SMS Notifications -->
                            <div class="flex items-center md:col-span-2">
                                <input type="hidden" name="settings[enable_sms_notifications]" value="0">
                                <input type="checkbox" name="settings[enable_sms_notifications]" id="enable_sms_notifications" value="1" {{ ($settings['system']['enable_sms_notifications']['value'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="enable_sms_notifications" class="ml-2 block text-sm text-gray-700">
                                    Enable SMS notifications
                                </label>
                            </div>

                            <!-- Notification Email -->
                            <div>
                                <label for="notification_email" class="block text-sm font-medium text-gray-700 mb-1">Notification Email</label>
                                <input type="email" name="settings[notification_email]" id="notification_email" value="{{ $settings['system']['notification_email']['value'] ?? '' }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['system']['notification_email']['description'] ?? '' }}</p>
                            </div>

                            <!-- Session Lifetime -->
                            <div>
                                <label for="session_lifetime" class="block text-sm font-medium text-gray-700 mb-1">Session Lifetime (minutes)</label>
                                <input type="number" name="settings[session_lifetime]" id="session_lifetime" value="{{ $settings['system']['session_lifetime']['value'] ?? 120 }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['system']['session_lifetime']['description'] ?? '' }}</p>
                            </div>

                            <!-- Pagination Per Page -->
                            <div>
                                <label for="pagination_per_page" class="block text-sm font-medium text-gray-700 mb-1">Items Per Page</label>
                                <input type="number" name="settings[pagination_per_page]" id="pagination_per_page" value="{{ $settings['system']['pagination_per_page']['value'] ?? 15 }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['system']['pagination_per_page']['description'] ?? '' }}</p>
                            </div>

                            <!-- Enable API -->
                            <div class="flex items-center md:col-span-2">
                                <input type="hidden" name="settings[enable_api]" value="0">
                                <input type="checkbox" name="settings[enable_api]" id="enable_api" value="1" {{ ($settings['system']['enable_api']['value'] ?? true) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="enable_api" class="ml-2 block text-sm text-gray-700">
                                    Enable API endpoints
                                </label>
                            </div>

                            <!-- API Rate Limit -->
                            <div>
                                <label for="api_rate_limit" class="block text-sm font-medium text-gray-700 mb-1">API Rate Limit (per minute)</label>
                                <input type="number" name="settings[api_rate_limit]" id="api_rate_limit" value="{{ $settings['system']['api_rate_limit']['value'] ?? 60 }}" class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-gray-500">{{ $settings['system']['api_rate_limit']['description'] ?? '' }}</p>
                            </div>

                            <!-- Maintenance Mode -->
                            <div class="flex items-center md:col-span-2">
                                <input type="hidden" name="settings[maintenance_mode]" value="0">
                                <input type="checkbox" name="settings[maintenance_mode]" id="maintenance_mode" value="1" {{ ($settings['system']['maintenance_mode']['value'] ?? false) ? 'checked' : '' }} class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                <label for="maintenance_mode" class="ml-2 block text-sm text-gray-700">
                                    <span class="font-medium">Enable maintenance mode</span>
                                    <span class="block text-xs text-gray-500 mt-1">{{ $settings['system']['maintenance_mode']['description'] ?? '' }}</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end space-x-3">
                    <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        Cancel
                    </a>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
