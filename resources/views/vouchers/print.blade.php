<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Vouchers - Profile #{{ $voucher->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: white;
            padding: 20px;
        }

        .no-print {
            margin-bottom: 20px;
            padding: 15px;
            background: #f3f4f6;
            border-radius: 8px;
        }

        .no-print button {
            background: #4f46e5;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            margin-right: 10px;
        }

        .no-print button:hover {
            background: #4338ca;
        }

        .no-print a {
            color: #4f46e5;
            text-decoration: none;
            padding: 10px 20px;
            border: 1px solid #4f46e5;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
        }

        .header-info {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .header-info h1 {
            font-size: 24px;
            color: #111827;
            margin-bottom: 10px;
        }

        .header-info p {
            font-size: 14px;
            color: #6b7280;
        }

        .voucher-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .voucher-card {
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 15px;
            background: #f9fafb;
            page-break-inside: avoid;
        }

        .voucher-card .header {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 12px;
            text-align: center;
        }

        .voucher-card .header .company {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .voucher-card .header .plan-name {
            font-size: 14px;
            opacity: 0.95;
        }

        .voucher-card .credentials {
            background: white;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 10px;
        }

        .voucher-card .credential-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding-bottom: 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        .voucher-card .credential-row:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .voucher-card .credential-label {
            font-size: 11px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
        }

        .voucher-card .credential-value {
            font-size: 16px;
            color: #111827;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }

        .voucher-card .details {
            background: #fef3c7;
            padding: 10px;
            border-radius: 6px;
            font-size: 11px;
            color: #92400e;
            margin-bottom: 10px;
        }

        .voucher-card .details .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .voucher-card .details .detail-item:last-child {
            margin-bottom: 0;
        }

        .voucher-card .details .detail-label {
            font-weight: 500;
        }

        .voucher-card .footer {
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
        }

        .summary {
            margin-top: 30px;
            padding: 20px;
            background: #f3f4f6;
            border-radius: 8px;
            page-break-before: avoid;
        }

        .summary h2 {
            font-size: 18px;
            color: #111827;
            margin-bottom: 15px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }

        .summary-item {
            background: white;
            padding: 15px;
            border-radius: 6px;
            text-align: center;
        }

        .summary-item .label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 5px;
        }

        .summary-item .value {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
        }

        @media print {
            body {
                padding: 10px;
            }

            .no-print {
                display: none;
            }

            .voucher-grid {
                gap: 10px;
            }

            .voucher-card {
                padding: 10px;
                break-inside: avoid;
            }

            @page {
                margin: 10mm;
                size: A4;
            }
        }

        @media (max-width: 768px) {
            .voucher-grid {
                grid-template-columns: 1fr;
            }

            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <!-- Control Buttons (hidden when printing) -->
    <div class="no-print">
        <button onclick="window.print()">Print Vouchers</button>
        <a href="{{ route('routers.vouchers.show', [$router, $voucher]) }}">Back to Profile</a>
    </div>

    <!-- Header Info -->
    <div class="header-info">
        <h1>Hotspot Vouchers</h1>
        <p><strong>Router:</strong> {{ $router->name }} | <strong>Profile:</strong> {{ $voucher->bandwidthPlan->name }} | <strong>Generated:</strong> {{ now()->format('M d, Y h:i A') }}</p>
    </div>

    <!-- Voucher Cards Grid -->
    @if($voucher->hotspotUsers->count() > 0)
        <div class="voucher-grid">
            @foreach($voucher->hotspotUsers as $user)
                <div class="voucher-card">
                    <!-- Card Header -->
                    <div class="header">
                        <div class="company">WiFi Hotspot</div>
                        <div class="plan-name">{{ $voucher->bandwidthPlan->name }}</div>
                    </div>

                    <!-- Credentials -->
                    <div class="credentials">
                        <div class="credential-row">
                            <div>
                                <div class="credential-label">Username</div>
                                <div class="credential-value">{{ $user->username }}</div>
                            </div>
                        </div>
                        <div class="credential-row">
                            <div>
                                <div class="credential-label">Password</div>
                                <div class="credential-value">{{ $user->password }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Plan Details -->
                    <div class="details">
                        <div class="detail-item">
                            <span class="detail-label">Speed:</span>
                            <span>{{ $voucher->bandwidthPlan->download_speed }}/{{ $voucher->bandwidthPlan->upload_speed }}</span>
                        </div>
                        @if($voucher->bandwidthPlan->validity_period)
                            <div class="detail-item">
                                <span class="detail-label">Valid for:</span>
                                <span>{{ $voucher->bandwidthPlan->validity_period }} hours</span>
                            </div>
                        @endif
                        @if($voucher->bandwidthPlan->data_limit)
                            <div class="detail-item">
                                <span class="detail-label">Data limit:</span>
                                <span>{{ $voucher->bandwidthPlan->data_limit }}</span>
                            </div>
                        @endif
                        <div class="detail-item">
                            <span class="detail-label">Price:</span>
                            <span>${{ number_format($voucher->price, 2) }}</span>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        Voucher #{{ $user->id }} | {{ $user->created_at->format('M d, Y') }}
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Summary -->
        <div class="summary">
            <h2>Batch Summary</h2>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="label">Total Vouchers</div>
                    <div class="value">{{ $voucher->hotspotUsers->count() }}</div>
                </div>
                <div class="summary-item">
                    <div class="label">Profile</div>
                    <div class="value" style="font-size: 16px;">{{ $voucher->bandwidthPlan->name }}</div>
                </div>
                <div class="summary-item">
                    <div class="label">Price Each</div>
                    <div class="value">${{ number_format($voucher->price, 2) }}</div>
                </div>
                <div class="summary-item">
                    <div class="label">Total Value</div>
                    <div class="value">${{ number_format($voucher->price * $voucher->hotspotUsers->count(), 2) }}</div>
                </div>
            </div>
        </div>
    @else
        <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
            <svg style="width: 64px; height: 64px; margin: 0 auto 20px; color: #d1d5db;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <h3 style="font-size: 18px; color: #111827; margin-bottom: 10px;">No Users Generated</h3>
            <p style="margin-bottom: 20px;">This profile doesn't have any hotspot users yet.</p>
            <a href="{{ route('routers.vouchers.showGenerateUsers', [$router, $voucher]) }}" style="display: inline-block; background: #4f46e5; color: white; padding: 10px 20px; border-radius: 6px; text-decoration: none;">Generate Users</a>
        </div>
    @endif
</body>
</html>
