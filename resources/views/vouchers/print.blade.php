<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Voucher - {{ $voucher->code }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            body {
                margin: 0;
                padding: 20mm;
            }

            .no-print {
                display: none !important;
            }

            .print-voucher {
                page-break-after: always;
                page-break-inside: avoid;
            }

            .print-voucher:last-child {
                page-break-after: auto;
            }

            @page {
                size: A4;
                margin: 0;
            }
        }

        .voucher-card {
            border: 2px dashed #cbd5e0;
            border-radius: 8px;
        }

        .voucher-perforation {
            border-top: 2px dashed #e2e8f0;
            position: relative;
        }

        .voucher-perforation::before,
        .voucher-perforation::after {
            content: '';
            position: absolute;
            top: -8px;
            width: 16px;
            height: 16px;
            background: white;
            border: 2px dashed #cbd5e0;
            border-radius: 50%;
        }

        .voucher-perforation::before {
            left: -8px;
        }

        .voucher-perforation::after {
            right: -8px;
        }
    </style>
</head>
<body class="bg-white">
    <!-- Print Button (hidden when printing) -->
    <div class="no-print fixed top-4 right-4 z-50">
        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white rounded-lg shadow-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Print Voucher
        </button>
    </div>

    <!-- Voucher Card -->
    <div class="print-voucher max-w-2xl mx-auto p-8">
        <div class="voucher-card bg-white p-8">
            <!-- Header -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">WiFi Voucher</h1>
                <p class="text-sm text-gray-600">Internet Access Code</p>
            </div>

            <!-- Company Info (if available) -->
            <div class="text-center mb-8">
                <h2 class="text-xl font-semibold text-gray-800">{{ config('app.name', 'ElgioTik') }}</h2>
                <p class="text-sm text-gray-600 mt-1">Hotspot Network Access</p>
            </div>

            <!-- Voucher Code Section -->
            <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-lg p-6 mb-6">
                <div class="text-center">
                    <p class="text-sm font-medium text-gray-700 mb-2">Your Access Code</p>
                    <div class="bg-white rounded-lg p-4 border-2 border-indigo-200">
                        <p class="text-3xl font-bold font-mono text-indigo-600 tracking-wider">{{ $voucher->code }}</p>
                    </div>
                </div>
            </div>

            <!-- QR Code -->
            <div class="flex justify-center mb-6">
                <div class="bg-white p-4 rounded-lg border-2 border-gray-300">
                    <div id="qrcode"></div>
                </div>
            </div>
            <p class="text-center text-xs text-gray-500 mb-6">Scan QR code to copy voucher code</p>

            <!-- Perforation Line -->
            <div class="voucher-perforation my-6"></div>

            <!-- Voucher Details -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Plan</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $voucher->bandwidthPlan->name ?? 'N/A' }}</p>
                </div>
                @if($voucher->bandwidthPlan->speed ?? null)
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Speed</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">{{ $voucher->bandwidthPlan->speed }}</p>
                </div>
                @endif
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Price</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">${{ number_format($voucher->price, 2) }}</p>
                </div>
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase">Valid Until</p>
                    <p class="text-sm font-semibold text-gray-900 mt-1">
                        @if($voucher->expires_at)
                            {{ $voucher->expires_at->format('M d, Y') }}
                        @else
                            See Plan Details
                        @endif
                    </p>
                </div>
            </div>

            <!-- Instructions -->
            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">How to Connect:</h3>
                <ol class="text-xs text-gray-700 space-y-2">
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center bg-indigo-100 text-indigo-600 rounded-full mr-2 text-xs font-bold">1</span>
                        <span>Connect to the WiFi network</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center bg-indigo-100 text-indigo-600 rounded-full mr-2 text-xs font-bold">2</span>
                        <span>Open your web browser</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center bg-indigo-100 text-indigo-600 rounded-full mr-2 text-xs font-bold">3</span>
                        <span>Enter the voucher code above when prompted</span>
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 flex items-center justify-center bg-indigo-100 text-indigo-600 rounded-full mr-2 text-xs font-bold">4</span>
                        <span>Enjoy your internet access!</span>
                    </li>
                </ol>
            </div>

            <!-- Terms & Conditions -->
            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-xs font-semibold text-gray-900 mb-2">Terms & Conditions:</h4>
                <ul class="text-xs text-gray-600 space-y-1">
                    <li>" Voucher is valid for single use only</li>
                    <li>" No refunds or exchanges</li>
                    <li>" Internet speed may vary based on network conditions</li>
                    <li>" Fair usage policy applies</li>
                    <li>" Keep this voucher safe and secure</li>
                </ul>
            </div>

            <!-- Footer -->
            <div class="mt-6 pt-4 border-t border-gray-200 text-center">
                <p class="text-xs text-gray-500">
                    For support, please contact your network administrator
                </p>
                <p class="text-xs text-gray-400 mt-1">
                    Generated on {{ now()->format('F d, Y \a\t H:i') }}
                </p>
            </div>
        </div>

        <!-- Cut Here Line -->
        <div class="text-center mt-8 no-print">
            <p class="text-sm text-gray-500 italic"> - - - - - Cut along the dotted line - - - - - </p>
        </div>
    </div>

    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new QRCode(document.getElementById("qrcode"), {
                text: "{{ $voucher->code }}",
                width: 150,
                height: 150,
                colorDark: "#000000",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
        });

        // Auto-print dialog when page loads (optional)
        // Uncomment the line below if you want the print dialog to open automatically
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
