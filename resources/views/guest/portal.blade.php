<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WiFi Hotspot Portal - {{ $router->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 900px;
            width: 100%;
            overflow: hidden;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .header p {
            opacity: 0.9;
            font-size: 14px;
        }

        .tabs {
            display: flex;
            border-bottom: 2px solid #e5e7eb;
        }

        .tab {
            flex: 1;
            padding: 20px;
            text-align: center;
            background: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #6b7280;
            transition: all 0.3s;
        }

        .tab.active {
            color: #667eea;
            border-bottom: 3px solid #667eea;
        }

        .tab:hover {
            background: #f9fafb;
        }

        .content {
            padding: 30px;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-form {
            max-width: 400px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"],
        input[type="tel"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
        }

        input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .plan-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 24px;
            transition: all 0.3s;
            cursor: pointer;
            position: relative;
        }

        .plan-card:hover {
            border-color: #667eea;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.15);
            transform: translateY(-5px);
        }

        .plan-card.selected {
            border-color: #667eea;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
        }

        .plan-name {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 12px;
        }

        .plan-price {
            font-size: 32px;
            font-weight: 800;
            color: #667eea;
            margin-bottom: 16px;
        }

        .plan-price .currency {
            font-size: 16px;
            font-weight: 600;
        }

        .plan-features {
            list-style: none;
            margin-bottom: 16px;
        }

        .plan-features li {
            padding: 8px 0;
            color: #6b7280;
            font-size: 14px;
            display: flex;
            align-items: center;
        }

        .plan-features li:before {
            content: "✓";
            color: #10b981;
            font-weight: bold;
            margin-right: 8px;
        }

        .loader {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 40px auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .alert {
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #10b981;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #3b82f6;
        }

        .payment-section {
            display: none;
            animation: fadeIn 0.3s;
        }

        .payment-section.active {
            display: block;
        }

        .credentials-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
        }

        .credentials-box h3 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        .credential-item {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }

        .credential-label {
            font-size: 12px;
            opacity: 0.9;
            margin-bottom: 5px;
        }

        .credential-value {
            font-size: 20px;
            font-weight: 700;
            font-family: monospace;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin: 10px 0;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-failed {
            background: #fee2e2;
            color: #991b1b;
        }

        .hidden {
            display: none !important;
        }

        @media (max-width: 768px) {
            .plans-grid {
                grid-template-columns: 1fr;
            }

            .header h1 {
                font-size: 22px;
            }

            .content {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ $router->name }}</h1>
            <p>{{ $router->location ?? 'WiFi Hotspot' }}</p>
        </div>

        <div class="tabs">
            <button class="tab active" onclick="switchTab('login')">Login</button>
            <button class="tab" onclick="switchTab('buy')">Buy Internet</button>
        </div>

        <div class="content">
            <!-- Login Tab -->
            <div id="login-tab" class="tab-content active">
                <form class="login-form" id="loginForm" method="post" action="$(if chap-id)http://$(hostname)/login?username=$(username)&password=$(password)$(else)http://$(hostname)/login?username=$(username)&password=$(password)$(endif)" name="sendin">
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required placeholder="Enter your username">
                    </div>
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Enter your password">
                    </div>
                    <button type="submit" class="btn btn-primary">Connect</button>
                </form>
            </div>

            <!-- Buy Internet Tab -->
            <div id="buy-tab" class="tab-content">
                <div id="plans-container">
                    <div class="loader"></div>
                </div>

                <div id="payment-section" class="payment-section">
                    <div id="selected-plan-info" style="margin-bottom: 20px;"></div>

                    <form id="paymentForm" class="login-form">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" required placeholder="237XXXXXXXXX">
                        </div>
                        <button type="submit" class="btn btn-primary" id="payBtn">Pay Now</button>
                        <button type="button" class="btn btn-primary" onclick="backToPlans()" style="margin-top: 10px; background: #6b7280;">Back to Plans</button>
                    </form>
                </div>

                <div id="status-section" class="payment-section">
                    <div id="status-container"></div>
                </div>

                <div id="credentials-section" class="payment-section">
                    <div class="credentials-box">
                        <h3>🎉 Payment Successful!</h3>
                        <p style="margin-bottom: 20px;">Your WiFi credentials are ready</p>
                        <div class="credential-item">
                            <div class="credential-label">Username</div>
                            <div class="credential-value" id="cred-username">-</div>
                        </div>
                        <div class="credential-item">
                            <div class="credential-label">Password</div>
                            <div class="credential-value" id="cred-password">-</div>
                        </div>
                        <p style="margin-top: 20px; font-size: 14px; opacity: 0.9;">
                            Use these credentials to login above or connect automatically.
                        </p>
                        <button type="button" class="btn btn-primary" onclick="autoLogin()" style="margin-top: 20px; background: rgba(255,255,255,0.2);">
                            Login Automatically
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const ROUTER_HASH = '{{ $router->router_hash }}';
        const BASE_URL = '{{ url('/') }}';
        let selectedPlan = null;
        let currentTransactionId = null;
        let statusCheckInterval = null;
        let credentials = null;

        // Switch between tabs
        function switchTab(tab) {
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));

            if (tab === 'login') {
                document.querySelectorAll('.tab')[0].classList.add('active');
                document.getElementById('login-tab').classList.add('active');
            } else {
                document.querySelectorAll('.tab')[1].classList.add('active');
                document.getElementById('buy-tab').classList.add('active');
                loadPlans();
            }
        }

        // Load plans from server
        async function loadPlans() {
            try {
                const response = await fetch(`${BASE_URL}/guest/routers/${ROUTER_HASH}/plans`);
                const data = await response.json();

                if (data.success && data.plans.length > 0) {
                    displayPlans(data.plans);
                } else {
                    showError('No plans available at the moment');
                }
            } catch (error) {
                console.error('Error loading plans:', error);
                showError('Failed to load plans. Please try again.');
            }
        }

        // Display plans
        function displayPlans(plans) {
            const container = document.getElementById('plans-container');
            container.innerHTML = '<div class="plans-grid"></div>';
            const grid = container.querySelector('.plans-grid');

            plans.forEach(plan => {
                const card = document.createElement('div');
                card.className = 'plan-card';
                card.onclick = () => selectPlan(plan);
                card.innerHTML = `
                    <div class="plan-name">${plan.name}</div>
                    <div class="plan-price">
                        ${Math.floor(plan.price)} <span class="currency">${plan.currency}</span>
                    </div>
                    <ul class="plan-features">
                        <li>Speed: ${plan.download_speed}/${plan.upload_speed}</li>
                        <li>Validity: ${plan.validity}</li>
                        <li>Data: ${plan.data_limit}</li>
                        ${plan.available_slots ? `<li>${plan.available_slots} slots available</li>` : ''}
                    </ul>
                `;
                grid.appendChild(card);
            });
        }

        // Select a plan
        function selectPlan(plan) {
            selectedPlan = plan;

            // Hide plans, show payment form
            document.getElementById('plans-container').style.display = 'none';
            document.getElementById('payment-section').classList.add('active');

            // Show selected plan info
            document.getElementById('selected-plan-info').innerHTML = `
                <div class="alert alert-info">
                    <strong>${plan.name}</strong> - ${Math.floor(plan.price)} ${plan.currency}<br>
                    Speed: ${plan.download_speed}/${plan.upload_speed} | Validity: ${plan.validity}
                </div>
            `;
        }

        // Back to plans
        function backToPlans() {
            document.getElementById('plans-container').style.display = 'block';
            document.getElementById('payment-section').classList.remove('active');
            document.getElementById('status-section').classList.remove('active');
            selectedPlan = null;
        }

        // Handle payment form submission
        document.getElementById('paymentForm').addEventListener('submit', async (e) => {
            e.preventDefault();

            const phone = document.getElementById('phone').value;
            const payBtn = document.getElementById('payBtn');

            payBtn.disabled = true;
            payBtn.textContent = 'Processing...';

            try {
                const response = await fetch(`${BASE_URL}/guest/vouchers/${selectedPlan.voucher_hash}/pay`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        phone_number: phone
                    })
                });

                const data = await response.json();

                if (data.success) {
                    currentTransactionId = data.transaction_id;
                    showPaymentStatus();
                    startStatusPolling();
                } else {
                    showError(data.message || 'Payment initiation failed');
                    payBtn.disabled = false;
                    payBtn.textContent = 'Pay Now';
                }
            } catch (error) {
                console.error('Payment error:', error);
                showError('Payment failed. Please try again.');
                payBtn.disabled = false;
                payBtn.textContent = 'Pay Now';
            }
        });

        // Show payment status section
        function showPaymentStatus() {
            document.getElementById('payment-section').classList.remove('active');
            document.getElementById('status-section').classList.add('active');
            document.getElementById('status-container').innerHTML = `
                <div style="text-align: center;">
                    <div class="loader"></div>
                    <p style="margin-top: 20px; color: #6b7280;">
                        Processing your payment...<br>
                        Please check your phone and enter your PIN to confirm.
                    </p>
                    <span class="status-badge status-pending">Waiting for payment</span>
                </div>
            `;
        }

        // Start polling payment status
        function startStatusPolling() {
            statusCheckInterval = setInterval(checkPaymentStatus, 3000);
            checkPaymentStatus(); // Check immediately
        }

        // Check payment status
        async function checkPaymentStatus() {
            try {
                const response = await fetch(`${BASE_URL}/guest/payment-status?transaction_id=${currentTransactionId}`);
                const data = await response.json();

                if (data.status === 'completed') {
                    clearInterval(statusCheckInterval);
                    credentials = data.credentials;
                    showSuccessWithCredentials(data.credentials);
                } else if (data.status === 'failed' || data.status === 'cancelled' || data.status === 'expired') {
                    clearInterval(statusCheckInterval);
                    showPaymentFailed(data.failure_reason || 'Payment was not completed');
                } else if (data.status === 'pending') {
                    // Continue polling
                }
            } catch (error) {
                console.error('Status check error:', error);
            }
        }

        // Show payment failed
        function showPaymentFailed(reason) {
            document.getElementById('status-container').innerHTML = `
                <div style="text-align: center; padding: 30px;">
                    <div style="font-size: 64px; margin-bottom: 20px;">❌</div>
                    <h3 style="color: #991b1b; margin-bottom: 10px;">Payment Failed</h3>
                    <p style="color: #6b7280; margin-bottom: 20px;">${reason}</p>
                    <span class="status-badge status-failed">Failed</span>
                    <br><br>
                    <button type="button" class="btn btn-primary" onclick="backToPlans()" style="max-width: 300px;">
                        Try Again
                    </button>
                </div>
            `;
        }

        // Show success with credentials
        function showSuccessWithCredentials(creds) {
            document.getElementById('status-section').classList.remove('active');
            document.getElementById('credentials-section').classList.add('active');
            document.getElementById('cred-username').textContent = creds.username;
            document.getElementById('cred-password').textContent = creds.password;
        }

        // Auto login with credentials
        function autoLogin() {
            if (credentials) {
                document.getElementById('username').value = credentials.username;
                document.getElementById('password').value = credentials.password;
                switchTab('login');
                // Auto-submit after a brief delay
                setTimeout(() => {
                    document.getElementById('loginForm').submit();
                }, 500);
            }
        }

        // Show error message
        function showError(message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = 'alert alert-error';
            alertDiv.textContent = message;

            const container = document.getElementById('buy-tab');
            container.insertBefore(alertDiv, container.firstChild);

            setTimeout(() => alertDiv.remove(), 5000);
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            // Check if on buy tab initially
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('tab') === 'buy') {
                switchTab('buy');
            }
        });
    </script>
</body>
</html>
