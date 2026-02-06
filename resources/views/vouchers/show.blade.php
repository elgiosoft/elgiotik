@extends('layouts.app')

@section('title', 'Voucher Details')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">
    <!-- Header -->
    <div>
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('vouchers.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Voucher Details</h1>
                    <p class="mt-1 text-sm text-gray-600">View complete information about this voucher</p>
                </div>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('vouchers.print', $voucher) }}" target="_blank" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print
                </a>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Voucher Information -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Voucher Information</h2>
                </div>
                <div class="px-6 py-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Voucher Code</dt>
                            <dd class="mt-1 flex items-center" x-data="{ copied: false }">
                                <span class="font-mono text-lg font-bold text-gray-900">{{ $voucher->code }}</span>
                                <button @click="navigator.clipboard.writeText('{{ $voucher->code }}'); copied = true; setTimeout(() => copied = false, 2000)" class="ml-2 text-gray-400 hover:text-gray-600">
                                    <svg x-show="!copied" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <svg x-show="copied" x-cloak class="h-5 w-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if($voucher->status === 'active')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <span class="w-2 h-2 mr-1.5 bg-green-400 rounded-full"></span>
                                        Active
                                    </span>
                                @elseif($voucher->status === 'used')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                        <span class="w-2 h-2 mr-1.5 bg-blue-400 rounded-full"></span>
                                        Used
                                    </span>
                                @elseif($voucher->status === 'expired')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        <span class="w-2 h-2 mr-1.5 bg-red-400 rounded-full"></span>
                                        Expired
                                    </span>
                                @elseif($voucher->status === 'disabled')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        <span class="w-2 h-2 mr-1.5 bg-gray-400 rounded-full"></span>
                                        Disabled
                                    </span>
                                @endif
                            </dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Bandwidth Plan</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $voucher->bandwidthPlan->name ?? 'N/A' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Router</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $voucher->router->name ?? 'N/A' }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Price</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">${{ number_format($voucher->price, 2) }}</dd>
                        </div>

                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $voucher->created_at->format('M d, Y H:i') }}</dd>
                        </div>

                        @if($voucher->activated_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Activated At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $voucher->activated_at->format('M d, Y H:i') }}</dd>
                            </div>
                        @endif

                        @if($voucher->expires_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Expires At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $voucher->expires_at->format('M d, Y H:i') }}
                                    @if($voucher->expires_at->isFuture())
                                        <span class="text-xs text-gray-500">({{ $voucher->expires_at->diffForHumans() }})</span>
                                    @endif
                                </dd>
                            </div>
                        @endif

                        @if($voucher->sold_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Sold At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $voucher->sold_at->format('M d, Y H:i') }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Sold By</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $voucher->soldBy->name ?? 'N/A' }}</dd>
                            </div>
                        @endif

                        @if($voucher->customer_id)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Customer</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $voucher->customer->name ?? 'N/A' }}</dd>
                            </div>
                        @endif

                        @if($voucher->mac_address)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">MAC Address</dt>
                                <dd class="mt-1 text-sm font-mono text-gray-900">{{ $voucher->mac_address }}</dd>
                            </div>
                        @endif

                        @if($voucher->notes)
                            <div class="sm:col-span-2">
                                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $voucher->notes }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <!-- Usage History -->
            @if($voucher->hotspotUsers->count() > 0)
                <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <h2 class="text-lg font-semibold text-gray-900">Usage History</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Username</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Connected</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disconnected</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($voucher->hotspotUsers as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $user->username }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->connected_at?->format('M d, Y H:i') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->disconnected_at?->format('M d, Y H:i') ?? 'Active' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            @if($user->disconnected_at)
                                                {{ $user->connected_at->diffForHumans($user->disconnected_at, true) }}
                                            @else
                                                {{ $user->connected_at->diffForHumans(null, true) }}
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Actions -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
                </div>
                <div class="px-6 py-4">
                    <div class="flex flex-wrap gap-3">
                        @if($voucher->status === 'active' && !$voucher->sold_at)
                            <form action="{{ route('vouchers.sell', $voucher) }}" method="POST">
                                @csrf
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Mark as Sold
                                </button>
                            </form>
                        @endif

                        @if($voucher->status === 'active' && !$voucher->activated_at)
                            <form action="{{ route('vouchers.activate', $voucher) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Activate
                                </button>
                            </form>
                        @endif

                        @if($voucher->status === 'active')
                            <form action="{{ route('vouchers.disable', $voucher) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                    Disable
                                </button>
                            </form>
                        @elseif($voucher->status === 'disabled')
                            <form action="{{ route('vouchers.enable', $voucher) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Enable
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('vouchers.destroy', $voucher) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this voucher? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">
                                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete Voucher
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <!-- QR Code -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">QR Code</h2>
                </div>
                <div class="px-6 py-6">
                    <div class="flex flex-col items-center">
                        <div class="bg-white p-4 rounded-lg border-2 border-gray-200">
                            <div id="qrcode"></div>
                        </div>
                        <p class="mt-4 text-sm text-gray-500 text-center">Scan to copy voucher code</p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h2 class="text-lg font-semibold text-gray-900">Quick Stats</h2>
                </div>
                <div class="px-6 py-4 space-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Times Used</dt>
                        <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $voucher->hotspotUsers->count() }}</dd>
                    </div>

                    @if($voucher->activated_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Active Since</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $voucher->activated_at->diffForHumans() }}</dd>
                        </div>
                    @endif

                    @if($voucher->expires_at && $voucher->expires_at->isFuture())
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Time Remaining</dt>
                            <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $voucher->expires_at->diffForHumans(null, true) }}</dd>
                        </div>
                    @endif

                    <div>
                        <dt class="text-sm font-medium text-gray-500">Is Sold</dt>
                        <dd class="mt-1">
                            @if($voucher->sold_at)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Yes
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    No
                                </span>
                            @endif
                        </dd>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    new QRCode(document.getElementById("qrcode"), {
        text: "{{ $voucher->code }}",
        width: 200,
        height: 200,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H
    });
});
</script>
@endpush
