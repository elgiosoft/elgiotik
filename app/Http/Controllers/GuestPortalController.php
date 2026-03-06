<?php

namespace App\Http\Controllers;

use App\Models\Router;
use App\Models\BandwidthPlan;
use App\Models\Voucher;
use App\Models\HotspotUser;
use App\Models\Transaction;
use App\Services\MikroTikService;
use ElgioPay\SDK\ElgioPayClient;
use ElgioPay\SDK\ElgioPayException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GuestPortalController extends Controller
{
    /**
     * Show the hotspot portal page
     */
    public function portal(string $routerHash)
    {
        $router = Router::where('router_hash', $routerHash)->firstOrFail();

        return view('guest.portal', compact('router'));
    }

    /**
     * Get available vouchers (plans) for a router
     */
    public function plans(string $routerHash)
    {
        $router = Router::where('router_hash', $routerHash)->firstOrFail();

        // Get active vouchers for this router that have capacity
        $vouchers = Voucher::where('router_id', $router->id)
            ->where('status', 'active')
            ->where('bandwidth_plan_id', '!=', 0)
            ->with('bandwidthPlan')
            ->orderBy('price', 'asc')
            ->get()
            ->map(function ($voucher) {
                $plan = $voucher->bandwidthPlan;
                return [
                    'voucher_hash' => $voucher->voucher_hash,
                    'name' => $plan->name,
                    'price' => (float) $voucher->price,
                    'currency' => 'XAF',
                    'download_speed' => $plan->download_speed,
                    'upload_speed' => $plan->upload_speed,
                    'validity' => $plan->getFormattedValidity(),
                    'data_limit' => $plan->getFormattedDataLimit(),
                    'description' => $plan->description,
                    'available_slots' => $voucher->getRemainingCapacity(),
                ];
            });

        return response()->json([
            'success' => true,
            'plans' => $vouchers,
            'router' => [
                'name' => $router->name,
                'location' => $router->location,
            ]
        ]);
    }

    /**
     * Process payment for a voucher
     */
    public function pay(Request $request, string $voucherHash)
    {
        $request->validate([
            'phone_number' => ['required', 'string', 'max:20'],
        ]);

        $voucher = Voucher::where('voucher_hash', $voucherHash)->firstOrFail();
        $router = $voucher->router;
        $plan = $voucher->bandwidthPlan;

        // Check if voucher has capacity
        if (!$voucher->hasCapacity()) {
            return response()->json([
                'success' => false,
                'message' => 'This voucher has reached its capacity. Please select another plan.',
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Generate unique reference
            $reference = 'HT-' . strtoupper(Str::random(8)) . '-' . time();

            // Initialize ElgioPay client
            $elgioPay = new ElgioPayClient();

            // Prepare payment data
            $paymentData = [
                'amount' => $voucher->price, // Convert to minimum currency unit
                'customer_phone' => $request->phone_number,
                'payment_method' => 'mtn_mobile_money', // Default to MTN, can be extended
                'currency' => 'XAF',
                'reference' => $reference,
                'description' => $router->name . ' - ' . $plan->name,
                'callback_url' => route('guest.payment-callback'),
                'metadata' => [
                    'voucher_id' => $voucher->id,
                    'voucher_hash' => $voucher->voucher_hash,
                    'plan_id' => $plan->id,
                    'router_id' => $router->id,
                    'router_hash' => $router->router_hash,
                ],
            ];

            // Initiate payment via ElgioPay
            $paymentResponse = $elgioPay->initiatePayment($paymentData);

            // Create transaction record
            $transaction = Transaction::create([
                'transaction_id' => $paymentResponse['transaction_id'],
                'bandwidth_plan_id' => $plan->id,
                'voucher_id' => $voucher->id,
                'amount' => $voucher->price,
                'currency' => 'XAF',
                'payment_method' => 'mtn_mobile_money',
                'customer_phone' => $request->phone_number,
                'status' => $paymentResponse['status'],
                'reference' => $reference,
                'description' => $router->name . ' - ' . $plan->name,
                'payment_url' => $paymentResponse['payment_url'] ?? null,
                'metadata' => [
                    'voucher_id' => $voucher->id,
                    'voucher_hash' => $voucher->voucher_hash,
                    'plan_id' => $plan->id,
                    'router_id' => $router->id,
                    'router_hash' => $router->router_hash,
                    'payment_response' => $paymentResponse,
                ],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'transaction_id' => $transaction->transaction_id,
                'status' => $transaction->status,
            ]);

        } catch (ElgioPayException $e) {
            DB::rollBack();
            Log::error('ElgioPay Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment initiation failed: ' . $e->getMessage(),
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Payment Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Check payment status
     */
    public function paymentStatus(Request $request)
    {
        $transactionId = $request->transaction_id;
        $transaction = Transaction::where('transaction_id', $transactionId)->firstOrFail();

        // Check status from ElgioPay if not completed or failed
        if (!$transaction->isCompleted() && !$transaction->isFailed()) {
            try {
                $elgioPay = new ElgioPayClient();
                $statusResponse = $elgioPay->getPaymentStatus($transactionId);

                // Update transaction status
                $transaction->update(['status' => $statusResponse['status']]);

                // If payment completed, generate hotspot user
                if ($statusResponse['status'] === 'completed') {
                    $hotspotUser = $this->generateHotspotUser($transaction);
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

        // Add credentials if completed
        if ($transaction->isCompleted()) {
            $hotspotUser = HotspotUser::where('voucher_id', $transaction->voucher_id)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($hotspotUser) {
                $response['credentials'] = [
                    'username' => $hotspotUser->username,
                    'password' => $hotspotUser->password,
                ];
            }
        }

        // Add failure reason if failed
        if ($transaction->isFailed()) {
            $response['failure_reason'] = $transaction->failure_reason;
        }

        return response()->json($response);
    }

    /**
     * Handle payment callback/webhook from ElgioPay
     */
    public function paymentCallback(Request $request)
    {
        try {
            $data = $request->all();
            Log::info('Guest Payment Callback Received:', $data);

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

            // If payment completed and no voucher exists, generate hotspot user
            if ($status === 'completed' && !$transaction->voucher) {
                DB::transaction(function () use ($transaction) {
                    $this->generateHotspotUser($transaction);
                });
            }

            // If payment failed, mark as failed
            if (in_array($status, ['failed', 'cancelled', 'expired'])) {
                $transaction->markAsFailed($data['failure_reason'] ?? 'Payment was not completed');
            }

            return response()->json(['success' => true, 'message' => 'Callback processed']);

        } catch (\Exception $e) {
            Log::error('Guest Payment Callback Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Generate hotspot user after successful payment
     * Try to create new user on router, or fallback to existing synced unpaid user
     */
    private function generateHotspotUser(Transaction $transaction): HotspotUser
    {
        // Get voucher from transaction
        $voucherId = $transaction->voucher_id ?? $transaction->metadata['voucher_id'] ?? null;
        $voucher = Voucher::find($voucherId);

        if (!$voucher) {
            throw new \Exception('Voucher not found');
        }

        $router = $voucher->router;

        if (!$router) {
            throw new \Exception('Router not available');
        }

        // Try to create new hotspot user on router
        try {
            // Generate unique hotspot credentials
            $username = 'user_' . strtolower(Str::random(8));
            $password = Str::random(12);

            // Create hotspot user in database
            $hotspotUser = HotspotUser::create([
                'voucher_id' => $voucher->id,
                'router_id' => $voucher->router_id,
                'bandwidth_plan_id' => $transaction->bandwidth_plan_id,
                'username' => $username,
                'password' => $password,
                'status' => 'pending',
                'synced_to_router' => false,
                'sold_by' => null,
                'sold_at' => now(),
            ]);

            // Try to create user on MikroTik router
            $mikrotik = new MikroTikService($router);
            $success = $mikrotik->createHotspotUser(
                $username,
                $password,
                $voucher->mikrotik_profile_id ?? $transaction->bandwidthPlan->name,
                $transaction->id
            );

            if ($success) {
                $hotspotUser->markAsSynced($username);

                // Send SMS with credentials
                $this->sendCredentialsSMS($transaction->customer_phone, $username, $password, $voucher->bandwidthPlan);

                // Credit router wallet
                $router->creditWallet(
                    $transaction->amount,
                    $transaction->id,
                    $hotspotUser->id,
                    'Payment from guest portal',
                    ['plan_name' => $voucher->bandwidthPlan->name]
                );

                // Update voucher users_generated count
                $voucher->incrementUsersGenerated();

                // Transaction is already linked to voucher
                $transaction->markAsCompleted();

                return $hotspotUser;
            }

        } catch (\Exception $e) {
            Log::warning('Failed to create new hotspot user: ' . $e->getMessage());
        }

        // Fallback: Use existing synced unpaid user from the same voucher
        $existingUser = HotspotUser::where('voucher_id', $voucher->id)
            ->where('synced_to_router', true)
            ->whereNull('sold_at')
            ->orderBy('created_at', 'asc')
            ->first();

        if ($existingUser) {
            // Mark as sold
            $existingUser->update(['sold_at' => now(), 'transaction_id'=>$transaction->id]);

            // Send SMS with credentials
            $this->sendCredentialsSMS($transaction->customer_phone, $existingUser->username, $existingUser->password, $voucher->bandwidthPlan);

            // Credit router wallet
            $router->creditWallet(
                $transaction->amount,
                $transaction->id,
                $existingUser->id,
                'Payment from guest portal (existing user)',
                ['plan_name' => $voucher->bandwidthPlan->name]
            );

            // Transaction is already linked to voucher
            $transaction->markAsCompleted();

            return $existingUser;
        }

        throw new \Exception('Failed to create hotspot user and no existing users available from this voucher');
    }

    /**
     * Send SMS with hotspot credentials to customer
     */
    private function sendCredentialsSMS(string $phoneNumber, string $username, string $password, BandwidthPlan $plan): void
    {
        try {
            $elgioPay = new ElgioPayClient();

            $message = "Thanks for purchasing:\n"
                . "U: {$username}\n"
                . "P: {$password}\n"
                . "Plan: {$plan->name}\n"
                . "Speed: {$plan->download_speed}/{$plan->upload_speed}\n";

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
}
