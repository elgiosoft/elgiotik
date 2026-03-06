@extends('layouts.app')

@section('title', $router->name . ' - Router Details')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center">
                    <a href="{{ route('routers.index') }}" class="mr-4 text-gray-400 hover:text-gray-600 transition">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">{{ $router->name }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $router->location ?? 'No location specified' }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <!-- Test Connection -->
                    <form action="{{ route('routers.testConnection', $router) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Test Connection
                        </button>
                    </form>

                    <!-- Sync -->
                    <form action="{{ route('routers.syncUsers', $router) }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Sync Data
                        </button>
                    </form>

                    <!-- Edit -->
                    <a href="{{ route('routers.edit', $router) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit Router
                    </a>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-5 mb-6">
            <!-- Status Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            @if($router->status === 'online')
                            <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            @elseif($router->status === 'offline')
                            <div class="h-12 w-12 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </div>
                            @else
                            <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            @endif
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Connection Status</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900 capitalize">{{ $router->status }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Online Users Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Online Users</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ $router->getOnlineUsersCount() }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Total Hotspot Users Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Total Users</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ $router->hotspotUsers()->count() }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Vouchers Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-full bg-indigo-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Active Vouchers</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ $router->vouchers()->where('status', 'active')->count() }}</div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Wallet Balance Card -->
            <div class="bg-white overflow-hidden shadow rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center">
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">Wallet Balance</dt>
                                <dd class="flex items-baseline">
                                    <div class="text-2xl font-semibold text-gray-900">{{ number_format($router->wallet_balance, 0) }} <span class="text-sm text-gray-500">XAF</span></div>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Tabs -->
        <div class="mt-6 bg-white shadow rounded-lg" x-data="{ activeTab: 'info' }">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6" aria-label="Tabs">
                    <button
                        @click="activeTab = 'info'"
                        :class="activeTab === 'info' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Info
                    </button>
                    <button
                        @click="activeTab = 'users'"
                        :class="activeTab === 'users' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Online Users
                    </button>
                    <button
                        @click="activeTab = 'sessions'"
                        :class="activeTab === 'sessions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Recent Sessions
                    </button>
                    <button
                        @click="activeTab = 'vouchers'"
                        :class="activeTab === 'vouchers' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Vouchers
                    </button>
                    <button
                        @click="activeTab = 'transactions'"
                        :class="activeTab === 'transactions' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Transactions
                    </button>
                    <button
                        @click="activeTab = 'portal'"
                        :class="activeTab === 'portal' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition"
                    >
                        Portal
                    </button>
                </nav>
            </div>

            <!-- Info Tab -->
            <div x-show="activeTab === 'info'" class="px-6 py-5">
                <!-- Router Details Grid -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
                    <!-- Connection Information -->
                    <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Connection Information</h3>
                        <dl class="space-y-4">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                                <dd class="text-sm text-gray-900 font-mono">{{ $router->ip_address }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">API Port</dt>
                                <dd class="text-sm text-gray-900 font-mono">{{ $router->api_port }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Username</dt>
                                <dd class="text-sm text-gray-900 font-mono">{{ $router->username }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Connection String</dt>
                                <dd class="text-sm text-gray-900 font-mono">{{ $router->getConnectionString() }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Active Status</dt>
                                <dd class="text-sm">
                                    @if($router->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                    @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactive
                                    </span>
                                    @endif
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Router Details -->
                    <div class="bg-gray-50 rounded-lg p-5 border border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Router Details</h3>
                        <dl class="space-y-4">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Router Name</dt>
                                <dd class="text-sm text-gray-900">{{ $router->name }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Location</dt>
                                <dd class="text-sm text-gray-900">{{ $router->location ?? 'Not specified' }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Created At</dt>
                                <dd class="text-sm text-gray-900">{{ $router->created_at->format('M d, Y h:i A') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                                <dd class="text-sm text-gray-900">{{ $router->updated_at->format('M d, Y h:i A') }}</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Last Seen</dt>
                                <dd class="text-sm text-gray-900">{{ $router->last_seen_at ? $router->last_seen_at->format('M d, Y h:i A') : 'Never' }}</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                        <!-- Description -->
        @if($router->description)
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Description</h3>
            </div>
            <div class="px-6 py-5">
                <p class="text-sm text-gray-700">{{ $router->description }}</p>
            </div>
        </div>
        @endif

                <!-- VPN Configuration -->
                <div class="bg-gray-50 rounded-lg border border-gray-200 p-5">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <div class=" items-center justify-between">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">VPN Configuration</h3>
                            @if($router->vpn_enabled)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                VPN Enabled
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                VPN Disabled
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="px-5 py-5">
                        @if($router->vpn_enabled)
                            <!-- VPN Details -->
                            <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2 mb-6">
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">RouterOS Version</dt>
                                    <dd class="text-sm text-gray-900 font-mono">{{ $router->routeros_version ?? 'Unknown' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">VPN Type</dt>
                                    <dd class="text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $router->vpn_type === 'wireguard' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $router->vpn_type_name }}
                                        </span>
                                    </dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">VPN IP Address</dt>
                                    <dd class="text-sm text-gray-900 font-mono">{{ $router->vpn_ip ?? 'Not assigned' }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">VPN Port</dt>
                                    <dd class="text-sm text-gray-900 font-mono">{{ $router->vpn_listen_port ?? ($router->vpn_type === 'wireguard' ? 51820 : 1194) }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Server Endpoint</dt>
                                    <dd class="text-sm text-gray-900 font-mono">{{ $router->vpn_endpoint ?? config('mikrotik.vpn.server_endpoint') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-sm font-medium text-gray-500">Last Handshake</dt>
                                    <dd class="text-sm text-gray-900">
                                        @if($router->vpn_last_handshake)
                                            {{ $router->vpn_last_handshake->diffForHumans() }}
                                            @if($router->hasRecentHandshake())
                                                <span class="ml-2 text-green-600">●</span>
                                            @else
                                                <span class="ml-2 text-red-600">●</span>
                                            @endif
                                        @else
                                            <span class="text-gray-400">Never</span>
                                        @endif
                                    </dd>
                                </div>
                                <div class="flex justify-between sm:col-span-2">
                                    <dt class="text-sm font-medium text-gray-500">Public Key</dt>
                                    <dd class="text-sm text-gray-900 font-mono break-all">{{ Str::limit($router->vpn_public_key, 40) ?? 'Not generated' }}</dd>
                                </div>
                            </dl>

                            <!-- VPN Actions -->
                            <div class="flex flex-wrap gap-3 pt-4 border-t border-gray-200">
                                <!-- Download VPN Script -->
                                @if($router->vpn_config_script)
                                <a href="{{ route('routers.downloadVpnScript', $router) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Download VPN Script
                                </a>
                                @endif

                                <!-- Regenerate VPN -->
                                <form action="{{ route('routers.regenerateVpn', $router) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to regenerate VPN keys? You will need to re-import the new script on the MikroTik router.');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                        <svg class="-ml-1 mr-2 h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                        Regenerate VPN Keys
                                    </button>
                                </form>

                                <!-- Disable VPN -->
                                <form action="{{ route('routers.disableVpn', $router) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to disable VPN for this router?');">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition">
                                        <svg class="-ml-1 mr-2 h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                        Disable VPN
                                    </button>
                                </form>
                            </div>

                            <!-- VPN Setup Instructions -->
                            <div class="mt-6 pt-4 border-t border-gray-200">
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Setup Instructions</h4>
                                <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                                    <li>Download the VPN script using the button above</li>
                                    <li>Upload the script to your MikroTik router via WinBox or WebFig</li>
                                    <li>Open Terminal and run: <code class="px-2 py-1 bg-gray-100 rounded text-xs font-mono">/import file-name.rsc</code></li>
                                    <li>Wait 30 seconds for VPN connection to establish</li>
                                    <li>Update this router's IP address to: <code class="px-2 py-1 bg-gray-100 rounded text-xs font-mono">{{ $router->vpn_ip }}</code></li>
                                    <li>Test connection using the "Test Connection" button above</li>
                                </ol>

                                <!-- Manual Commands Section -->
                                @if($router->vpn_config_script)
                                <div class="mt-6" x-data="{ showCommands: false }">
                                    <button @click="showCommands = !showCommands" class="flex items-center justify-between w-full px-4 py-3 bg-gray-100 hover:bg-gray-200 rounded-lg transition text-left">
                                        <span class="text-sm font-medium text-gray-900">Manual Setup Commands</span>
                                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{'rotate-180': showCommands}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                        </svg>
                                    </button>

                                    <div x-show="showCommands" x-collapse>
                                        <div class="mt-3 bg-gray-900 rounded-lg p-4 relative">
                                            <button onclick="copyCommands()" class="absolute top-2 right-2 px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white text-xs rounded transition">
                                                Copy All
                                            </button>
                                            <pre id="vpn-commands" class="text-xs text-gray-100 overflow-x-auto font-mono whitespace-pre-wrap break-all pr-20">{{ $router->vpn_config_script }}</pre>
                                        </div>
                                        <p class="mt-2 text-xs text-gray-500">
                                            <strong>Alternative Setup:</strong> Copy these commands and paste them directly into your MikroTik terminal instead of uploading a file.
                                        </p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        @else
                            <!-- VPN Not Enabled -->
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">VPN Not Enabled</h3>
                                <p class="mt-1 text-sm text-gray-500 max-w-md mx-auto">
                                    Enable VPN to securely connect this router to ElgioTik over an encrypted tunnel.
                                    @if($router->routeros_version)
                                        @if($router->supportsWireGuard())
                                            This router supports <strong>WireGuard VPN</strong> (fast, modern).
                                        @else
                                            This router will use <strong>OpenVPN</strong> (compatible with RouterOS 6.x).
                                        @endif
                                    @endif
                                    VPN automatically generates keys, assigns IP, and creates ready-to-use configuration.
                                </p>
                                <div class="mt-6">
                                    <form action="{{ route('routers.enableVpn', $router) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                            Enable VPN
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- Online Users Tab -->
            <div x-show="activeTab === 'users'" class="px-6 py-5">
                @if($router->hotspotUsers()->where('is_online', true)->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">MAC Address</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Login Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($router->hotspotUsers()->where('is_online', true)->limit(10)->get() as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->username }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $user->mac_address ?? 'N/A' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500 font-mono">{{ $user->ip_address ?? 'N/A' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->login_time ? $user->login_time->diffForHumans() : 'N/A' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No online users</h3>
                    <p class="mt-1 text-sm text-gray-500">There are currently no users connected to this router.</p>
                </div>
                @endif
            </div>

            <!-- Recent Sessions Tab -->
            <div x-show="activeTab === 'sessions'" x-cloak class="px-6 py-5">
                @if($router->userSessions()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Download</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($router->userSessions()->latest()->limit(10)->get() as $session)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-900">{{ $session->username }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $session->duration_formatted ?? 'N/A' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $session->upload_formatted ?? 'N/A' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $session->download_formatted ?? 'N/A' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $session->start_time->format('M d, h:i A') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No session history</h3>
                    <p class="mt-1 text-sm text-gray-500">There are no recorded sessions for this router yet.</p>
                </div>
                @endif
            </div>

            <!-- Vouchers Tab -->
            <div x-show="activeTab === 'vouchers'" x-cloak class="px-6 py-5">
                <div class="flex justify-between items-center mb-4">
                    <div class="text-sm text-gray-600">
                        {{ $router->vouchers()->count() }} voucher profiles total
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('routers.vouchers.create', $router) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Voucher Profile
                        </a>
                        <a href="{{ route('routers.vouchers.index', $router) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                            View All Vouchers
                        </a>
                    </div>
                </div>

                @if($router->vouchers()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bandwidth Plan</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacity</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Users Generated</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($router->vouchers()->latest()->limit(10)->get() as $voucher)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-4 whitespace-nowrap text-sm font-mono text-gray-900">#{{ $voucher->id }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $voucher->bandwidthPlan->name ?? 'N/A' }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $voucher->user_capacity }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $voucher->users_generated }} / {{ $voucher->user_capacity }}
                                    @if($voucher->users_generated >= $voucher->user_capacity)
                                        <span class="ml-1 text-red-600">(Full)</span>
                                    @endif
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $voucher->status === 'active' ? 'bg-green-100 text-green-800' : ($voucher->status === 'inactive' ? 'bg-gray-100 text-gray-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($voucher->status) }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                                    <a href="{{ route('routers.vouchers.show', [$router, $voucher]) }}" class="text-blue-600 hover:text-blue-900">View</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($router->vouchers()->count() > 10)
                    <div class="mt-4 text-center">
                        <a href="{{ route('routers.vouchers.index', $router) }}" class="text-sm text-blue-600 hover:text-blue-800">
                            View all {{ $router->vouchers()->count() }} vouchers →
                        </a>
                    </div>
                @endif
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No voucher profiles yet</h3>
                    <p class="mt-1 text-sm text-gray-500 max-w-md mx-auto">
                        Create a voucher profile to generate hotspot users for this router. Each profile is linked to a bandwidth plan and can generate multiple users.
                    </p>
                    <div class="mt-6">
                        <a href="{{ route('routers.vouchers.create', $router) }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Your First Voucher Profile
                        </a>
                    </div>
                </div>
                @endif
            </div>

            <!-- Transactions Tab -->
            <div x-show="activeTab === 'transactions'" x-cloak class="px-6 py-5" x-data="{ showWithdrawModal: false, withdrawAmount: {{ $router->wallet_balance }}, phoneNumber: '{{ auth()->user()->phone_number ?? '' }}' }">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Wallet Transactions</h3>
                        <p class="text-sm text-gray-500 mt-1">Current balance: <span class="font-semibold text-green-600">{{ number_format($router->wallet_balance, 0) }} XAF</span></p>
                    </div>
                    <button @click="showWithdrawModal = true" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition">
                        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Withdraw Balance
                    </button>
                </div>

                <!-- Withdrawal Modal -->
                <div x-show="showWithdrawModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Background overlay -->
                        <div x-show="showWithdrawModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showWithdrawModal = false"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <!-- Modal panel -->
                        <div x-show="showWithdrawModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                            <div>
                                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="mt-3 text-center sm:mt-5">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Withdraw Balance
                                    </h3>
                                    <div class="mt-2">
                                        <p class="text-sm text-gray-500">
                                            Available balance: <span class="font-semibold text-green-600">{{ number_format($router->wallet_balance, 0) }} XAF</span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <form action="{{ route('routers.withdraw', $router) }}" method="POST" class="mt-5 sm:mt-6 space-y-4">
                                @csrf
                                <!-- Amount Field -->
                                <div>
                                    <label for="amount" class="block text-sm font-medium text-gray-700">Amount (XAF)</label>
                                    <input type="number" name="amount" id="amount" x-model="withdrawAmount" min="1" max="{{ $router->wallet_balance }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                    <p class="mt-1 text-xs text-gray-500">Maximum: {{ number_format($router->wallet_balance, 0) }} XAF</p>
                                </div>

                                <!-- Phone Number Field -->
                                <div>
                                    <label for="phone_number" class="block text-sm font-medium text-gray-700">Phone Number (MTN Mobile Money)</label>
                                    <input type="tel" name="phone_number" id="phone_number" x-model="phoneNumber" required placeholder="237XXXXXXXXX" pattern="237[0-9]{9}" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 sm:text-sm">
                                    <p class="mt-1 text-xs text-gray-500">Format: 237XXXXXXXXX (Cameroon MTN number)</p>
                                </div>

                                <!-- Buttons -->
                                <div class="mt-5 sm:mt-6 sm:grid sm:grid-cols-2 sm:gap-3 sm:grid-flow-row-dense">
                                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:col-start-2 sm:text-sm">
                                        Withdraw
                                    </button>
                                    <button type="button" @click="showWithdrawModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:col-start-1 sm:text-sm">
                                        Cancel
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                @if($router->routerTransactions()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                <th class="px-3 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                <th class="px-3 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                <th class="px-3 py-3 bg-gray-50 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance After</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($router->routerTransactions()->latest()->limit(50)->get() as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">{{ $transaction->created_at->format('M d, Y h:i A') }}</td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $transaction->type === 'credit' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-900">
                                    {{ $transaction->description }}
                                    @if($transaction->hotspotUser)
                                    <div class="text-xs text-gray-500">User: {{ $transaction->hotspotUser->username }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-right font-medium {{ $transaction->type === 'credit' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transaction->type === 'credit' ? '+' : '-' }}{{ number_format($transaction->amount, 0) }} XAF
                                </td>
                                <td class="px-3 py-4 whitespace-nowrap text-sm text-right text-gray-900">{{ number_format($transaction->balance_after, 0) }} XAF</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No transactions yet</h3>
                    <p class="mt-1 text-sm text-gray-500">Transactions will appear here when payments are made through the guest portal.</p>
                </div>
                @endif
            </div>

            <!-- Portal Tab -->
            <div x-show="activeTab === 'portal'" x-cloak class="px-6 py-5">
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-medium text-blue-900 mb-2">Guest Portal</h3>
                    <p class="text-sm text-blue-700 mb-4">This is your customized hotspot portal page where guests can login or purchase internet plans.</p>

                    <div class="bg-white rounded-lg p-4 mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Portal URL</label>
                        <div class="flex items-center">
                            <input type="text" readonly value="{{ url('/guest/' . $router->router_hash . '/portal') }}" class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md bg-gray-50 text-sm font-mono">
                            <button onclick="copyToClipboard('{{ url('/guest/' . $router->router_hash . '/portal') }}')" class="px-4 py-2 bg-blue-600 text-white rounded-r-md hover:bg-blue-700 text-sm font-medium">Copy</button>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <a href="{{ url('/guest/' . $router->router_hash . '/portal') }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Open Portal
                        </a>
                        <a href="{{ route('routers.downloadPortal', $router) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Download HTML
                        </a>
                    </div>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">MikroTik Hotspot Setup</h4>
                    <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                        <li>Download the portal HTML file using the button above</li>
                        <li>Login to your MikroTik router via WinBox or WebFig</li>
                        <li>Go to <strong>Files</strong> and upload the HTML file to: <code class="px-2 py-1 bg-gray-100 rounded text-xs font-mono">hotspot/login.html</code></li>
                        <li>Your guests will now see this custom portal when connecting to the hotspot</li>
                        <li>They can login with credentials or purchase new plans directly from the portal</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Portal URL copied to clipboard!');
    }, function(err) {
        alert('Failed to copy URL');
    });
}

function copyCommands() {
    const commandsElement = document.getElementById('vpn-commands');
    const text = commandsElement.textContent;

    navigator.clipboard.writeText(text).then(function() {
        // Change button text temporarily
        const button = event.target;
        const originalText = button.textContent;
        button.textContent = 'Copied!';
        button.classList.add('bg-green-600');
        button.classList.remove('bg-gray-700');

        setTimeout(function() {
            button.textContent = originalText;
            button.classList.remove('bg-green-600');
            button.classList.add('bg-gray-700');
        }, 2000);
    }, function(err) {
        alert('Failed to copy commands');
    });
}
</script>

@endsection
