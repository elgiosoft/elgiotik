<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Router;
use App\Models\BandwidthPlan;
use App\Models\HotspotUser;
use App\Models\Transaction;
use App\Services\MikroTikService;
use ElgioPay\SDK\ElgioPayClient;
use ElgioPay\SDK\ElgioPayException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Laravel\Telescope\Telescope;

class PortalController extends Controller
{
    /**
     * Display the captive portal homepage.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get available bandwidth plans for display
        $bandwidthPlans = BandwidthPlan::where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();

        return view('portal.index', compact('bandwidthPlans'));
    }

    /**
     * Activate a voucher and create hotspot user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activateVoucher(Request $request)
    {
        $request->validate([
            'voucher_code' => ['required', 'string', 'exists:vouchers,code'],
        ]);

        $voucherCode = strtoupper(trim($request->voucher_code));

        // Find the voucher
        $voucher = Voucher::where('code', $voucherCode)->first();

        // Validate voucher status
        if (!$voucher) {
            return back()->withErrors(['voucher_code' => 'Invalid voucher code.'])->withInput();
        }

        if ($voucher->status !== 'active') {
            return back()->withErrors(['voucher_code' => 'This voucher has already been used or is no longer valid.'])->withInput();
        }

        try {
            DB::beginTransaction();

            // Generate username and password for hotspot
            $username = 'user_' . Str::random(8);
            $password = Str::random(12);

            // Get router and bandwidth plan
            $router = $voucher->router;
            $bandwidthPlan = $voucher->bandwidthPlan;

            if (!$router || !$bandwidthPlan) {
                throw new \Exception('Invalid voucher configuration. Please contact support.');
            }

            // Create hotspot user on MikroTik
            $mikrotik = new MikroTikService($router);
            $created = $mikrotik->createHotspotUser(
                $username,
                $password,
                $bandwidthPlan->name
            );

            if (!$created) {
                throw new \Exception('Failed to create hotspot user. Please try again.');
            }

            // Calculate expiration date
            $expiresAt = now();
            if ($bandwidthPlan->validity_days > 0) {
                $expiresAt = $expiresAt->addDays($bandwidthPlan->validity_days);
            } elseif ($bandwidthPlan->validity_hours > 0) {
                $expiresAt = $expiresAt->addHours($bandwidthPlan->validity_hours);
            } else {
                $expiresAt = $expiresAt->addDays(30); // Default 30 days
            }

            // Create hotspot user record
            $hotspotUser = HotspotUser::create([
                'voucher_id' => $voucher->id,
                'router_id' => $router->id,
                'bandwidth_plan_id' => $bandwidthPlan->id,
                'username' => $username,
                'password' => $password,
                'status' => 'active',
                'expires_at' => $expiresAt,
            ]);

            // Update voucher status
            $voucher->update([
                'status' => 'used',
                'activated_at' => now(),
                'expires_at' => $expiresAt,
            ]);

            DB::commit();

            // Redirect to success page with credentials
            return redirect()->route('portal.success', [
                'username' => $username,
                'password' => $password,
                'voucher_code' => $voucherCode,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['voucher_code' => 'Activation failed: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Show mobile money payment page.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showPayment(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', 'exists:bandwidth_plans,id'],
        ]);

        $bandwidthPlan = BandwidthPlan::findOrFail($request->plan_id);

        return view('portal.payment', compact('bandwidthPlan'));
    }

    /**
     * Process mobile money payment and generate voucher.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function processPayment(Request $request)
    {
        // Tag this request for Telescope monitoring
        Telescope::tag(function () use ($request) {
            return ['payment-initiation', 'elgiopay', $request->provider ?? 'unknown'];
        });

        $request->validate([
            'plan_id' => ['required', 'exists:bandwidth_plans,id'],
            'phone_number' => ['required', 'string', 'max:20'],
            'provider' => ['required', 'in:mtn,orange'],
        ]);

        $bandwidthPlan = BandwidthPlan::findOrFail($request->plan_id);
        $phoneNumber = $request->phone_number;
        $provider = $request->provider;

        try {
            DB::beginTransaction();

            // Get default router
            $router = Router::where('status', 'online')->first();
            if (!$router) {
                throw new \Exception('No active router available. Please contact support.');
            }

            // Generate unique reference
            $reference = 'VN-' . strtoupper(Str::random(8)) . '-' . time();

            // Initialize ElgioPay client
            $elgioPay = new ElgioPayClient();

            // Prepare payment data
            $paymentData = [
                'amount' => $bandwidthPlan->price,
                'customer_phone' => $phoneNumber,
                'payment_method' => $provider === 'mtn' ? 'mtn_mobile_money' : 'orange_money',
                'currency' => 'XAF',
                'reference' => $reference,
                'description' => 'VillageNet - ' . $bandwidthPlan->name,
                'callback_url' => route('portal.payment-callback'),
                'metadata' => [
                    'plan_id' => $bandwidthPlan->id,
                    'router_id' => $router->id,
                ],
            ];

            // Initiate payment via ElgioPay
            $paymentResponse = $elgioPay->initiatePayment($paymentData);

            // Create transaction record
            $transaction = Transaction::create([
                'transaction_id' => $paymentResponse['transaction_id'],
                'bandwidth_plan_id' => $bandwidthPlan->id,
                'amount' => $bandwidthPlan->price,
                'currency' => 'XAF',
                'payment_method' => $provider === 'mtn' ? 'mtn_mobile_money' : 'orange_money',
                'customer_phone' => $phoneNumber,
                'status' => $paymentResponse['status'],
                'reference' => $reference,
                'description' => 'VillageNet - ' . $bandwidthPlan->name,
                'payment_url' => $paymentResponse['payment_url'] ?? null,
                'metadata' => [
                    'plan_id' => $bandwidthPlan->id,
                    'router_id' => $router->id,
                    'payment_response' => $paymentResponse,
                ],
            ]);

            DB::commit();

            // Return JSON for AJAX requests
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment initiated successfully',
                    'transaction_id' => $transaction->transaction_id,
                    'status' => $transaction->status,
                ]);
            }

            // Redirect to payment processing/waiting page for regular requests
            return redirect()->route('portal.payment-status', [
                'transaction_id' => $transaction->transaction_id,
            ]);

        } catch (ElgioPayException $e) {
            DB::rollBack();
            Log::error('ElgioPay Error: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment initiation failed: ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['payment' => 'Payment initiation failed: ' . $e->getMessage()])->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment Error: ' . $e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment failed: ' . $e->getMessage(),
                ], 422);
            }

            return back()->withErrors(['payment' => 'Payment failed: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Check payment status and show result.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function paymentStatus(Request $request)
    {
        // Tag this request for Telescope monitoring
        Telescope::tag(function () use ($request) {
            return ['payment-status', 'elgiopay', $request->transaction_id ?? 'unknown'];
        });

        $transactionId = $request->transaction_id;
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

        // For AJAX requests, always return JSON status
        if ($request->expectsJson() || $request->ajax() || $request->has('ajax')) {
            // Check status from ElgioPay if not completed or failed
            if (!$transaction->isCompleted() && !$transaction->isFailed()) {
                try {
                    $elgioPay = new ElgioPayClient();
                    $statusResponse = $elgioPay->getPaymentStatus($transactionId);

                    // Update transaction status
                    $transaction->update(['status' => $statusResponse['status']]);

                    // If payment completed, generate voucher
                    if ($statusResponse['status'] === 'completed' && !$transaction->voucher) {
                        $this->generateVoucherFromTransaction($transaction);
                        $transaction->refresh();
                    }
                } catch (ElgioPayException $e) {
                    Log::error('Payment Status Check Error: ' . $e->getMessage());
                }
            }

            $response = [
                'transaction_id' => $transaction->transaction_id,
                'status' => $transaction->status,
                'customer_phone' => $transaction->customer_phone,
                'amount' => $transaction->amount,
                'currency' => $transaction->currency,
                'payment_method' => $transaction->payment_method,
            ];

            // Add redirect URL if completed
            if ($transaction->isCompleted() && $transaction->voucher) {
                $response['redirect_url'] = route('portal.payment-success', [
                    'voucher_code' => $transaction->voucher->code,
                    'plan_name' => $transaction->bandwidthPlan->name,
                    'amount' => $transaction->amount,
                    'provider' => $transaction->payment_method === 'mtn_mobile_money' ? 'mtn' : 'orange',
                    'phone' => $transaction->customer_phone,
                ]);
            }

            // Add failure reason if failed
            if ($transaction->isFailed()) {
                $response['failure_reason'] = $transaction->failure_reason;
            }

            return response()->json($response);
        }

        // If already completed, redirect to success
        if ($transaction->isCompleted() && $transaction->voucher) {
            return redirect()->route('portal.payment-success', [
                'voucher_code' => $transaction->voucher->code,
                'plan_name' => $transaction->bandwidthPlan->name,
                'amount' => $transaction->amount,
                'provider' => $transaction->payment_method === 'mtn_mobile_money' ? 'mtn' : 'orange',
                'phone' => $transaction->customer_phone,
            ]);
        }

        // Check status from ElgioPay
        try {
            $elgioPay = new ElgioPayClient();
            $statusResponse = $elgioPay->getPaymentStatus($transactionId);

            // Update transaction status
            $transaction->update(['status' => $statusResponse['status']]);

            // If payment completed, generate voucher
            if ($statusResponse['status'] === 'completed' && !$transaction->voucher) {
                $this->generateVoucherFromTransaction($transaction);
            }

            return view('portal.payment-status', [
                'transaction' => $transaction,
                'statusResponse' => $statusResponse,
            ]);

        } catch (ElgioPayException $e) {
            Log::error('Payment Status Check Error: ' . $e->getMessage());
            return view('portal.payment-status', [
                'transaction' => $transaction,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Generate hotspot user after successful payment and send SMS.
     *
     * @param  Transaction  $transaction
     * @return HotspotUser
     */
    private function generateVoucherFromTransaction(Transaction $transaction)
    {
        // Get router from metadata
        $routerId = $transaction->metadata['router_id'] ?? Router::where('status', 'online')->first()->id;
        $router = Router::find($routerId);

        if (!$router) {
            throw new \Exception('No router available');
        }

        // Find or create voucher profile for this bandwidth plan
        $voucher = Voucher::firstOrCreate([
            'bandwidth_plan_id' => $transaction->bandwidth_plan_id,
            'router_id' => $routerId,
            'status' => 'active',
        ], [
            'price' => $transaction->amount,
            'user_capacity' => 1000, // Default capacity
            'users_generated' => 0,
            'notes' => 'Auto-created for self-service purchases',
        ]);

        // Generate unique hotspot credentials
        $username = 'user_' . strtolower(Str::random(8));
        $password = Str::random(12);

        // Create hotspot user
        $hotspotUser = HotspotUser::create([
            'voucher_id' => $voucher->id,
            'router_id' => $routerId,
            'bandwidth_plan_id' => $transaction->bandwidth_plan_id,
            'username' => $username,
            'password' => $password,
            'status' => 'pending',
            'synced_to_router' => false,
            'sold_by' => null,
            'sold_at' => now(),
        ]);

        // Try to create user on MikroTik router
        try {
            $mikrotik = new MikroTikService($router);
            $success = $mikrotik->createHotspotUser(
                $username,
                $password,
                $voucher->mikrotik_profile_id ?? $transaction->bandwidthPlan->name
            );

            if ($success) {
                $hotspotUser->markAsSynced($username);
            }
        } catch (\Exception $e) {
            Log::warning('Failed to sync hotspot user to router: ' . $e->getMessage());
        }

        // Send SMS with credentials
        $this->sendCredentialsSMS($transaction->customer_phone, $username, $password, $transaction->bandwidthPlan);

        // Update voucher users_generated count
        $voucher->incrementUsersGenerated();

        // Link to transaction
        $transaction->update(['voucher_id' => $voucher->id]);
        $transaction->markAsCompleted();

        return $hotspotUser;
    }

    /**
     * Send SMS with hotspot credentials to customer.
     *
     * @param  string  $phoneNumber
     * @param  string  $username
     * @param  string  $password
     * @param  BandwidthPlan  $plan
     * @return void
     */
    private function sendCredentialsSMS(string $phoneNumber, string $username, string $password, BandwidthPlan $plan): void
    {
        try {
            $elgioPay = new ElgioPayClient();

            $message = "Your WiFi Access:\n"
                . "Username: {$username}\n"
                . "Password: {$password}\n"
                . "Plan: {$plan->name}\n"
                . "Speed: {$plan->download_speed}/{$plan->upload_speed}\n"
                . "Connect to WiFi and login with these credentials.";

            $result = $elgioPay->sendSMS($phoneNumber, $message);

            Log::info('SMS sent successfully', [
                'phone' => $phoneNumber,
                'username' => $username,
                'result' => $result,
            ]);
        } catch (ElgioPayException $e) {
            Log::error('Failed to send SMS: ' . $e->getMessage(), [
                'phone' => $phoneNumber,
                'username' => $username,
            ]);
            // Don't throw exception - SMS failure shouldn't block the purchase
        }
    }

    /**
     * Handle payment callback/webhook from ElgioPay.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentCallback(Request $request)
    {
        // Tag this webhook request for Telescope monitoring
        Telescope::tag(function () use ($request) {
            return ['payment-webhook', 'elgiopay', $request->input('status', 'unknown')];
        });

        try {
            $data = $request->all();
            Log::info('Payment Callback Received:', $data);

            $transactionId = $data['transaction_id'] ?? null;
            $status = $data['status'] ?? null;

            if (!$transactionId || !$status) {
                return response()->json(['error' => 'Invalid callback data'], 400);
            }

            // Find transaction
            $transaction = Transaction::where('transaction_id', $transactionId)->first();
            if (!$transaction) {
                return response()->json(['error' => 'Transaction not found'], 404);
            }

            // Update transaction status
            $transaction->update(['status' => $status]);

            // If payment completed and no voucher exists, generate one
            if ($status === 'completed' && !$transaction->voucher) {
                DB::transaction(function () use ($transaction) {
                    $this->generateVoucherFromTransaction($transaction);
                });
            }

            // If payment failed, mark as failed
            if (in_array($status, ['failed', 'cancelled', 'expired'])) {
                $transaction->markAsFailed($data['failure_reason'] ?? 'Payment was not completed');
            }

            return response()->json(['success' => true, 'message' => 'Callback processed']);

        } catch (\Exception $e) {
            Log::error('Payment Callback Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show payment success page with voucher.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function paymentSuccess(Request $request)
    {
        return view('portal.payment-success', [
            'voucherCode' => $request->voucher_code,
            'planName' => $request->plan_name,
            'amount' => $request->amount,
            'provider' => $request->provider,
            'phone' => $request->phone,
        ]);
    }

    /**
     * Show activation success page with credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function success(Request $request)
    {
        return view('portal.success', [
            'username' => $request->username,
            'password' => $request->password,
            'voucherCode' => $request->voucher_code,
        ]);
    }
}
