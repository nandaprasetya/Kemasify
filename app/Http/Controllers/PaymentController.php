<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\MidtransService;
use App\Services\TokenService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtrans,
        private readonly TokenService    $tokenService,
    ) {}

    // ─── Halaman Pricing ──────────────────────────────────────────────────────

    public function pricing()
    {
        $user   = Auth::user();
        $orders = $user->orders()->latest()->take(5)->get();

        return view('payment.pricing', compact('user', 'orders'));
    }

    // ─── Beli Premium ─────────────────────────────────────────────────────────

    public function buyPremium(Request $request)
    {
        $request->validate([
            'months' => ['required', 'integer', 'in:1,3,6,12'],
        ]);

        $user   = Auth::user();
        $months = (int) $request->months;

        // Hitung harga (diskon untuk bulan lebih banyak)
        $pricePerMonth = Order::PREMIUM_PRICE;
        $discount      = match($months) {
            3  => 0.05,  // 5% off
            6  => 0.10,  // 10% off
            12 => 0.20,  // 20% off
            default => 0,
        };
        $baseAmount = $pricePerMonth * $months;
        $amount     = (int) ($baseAmount * (1 - $discount));

        $order = Order::create([
            'user_id'      => $user->id,
            'type'         => 'premium_monthly',
            'quantity'     => $months,
            'amount'       => $amount,
            'token_amount' => 0,
            'status'       => 'pending',
            'order_id'     => Order::generateOrderId($user->id),
            'expired_at'   => now()->addHours(24),
        ]);

        try {
            $snapToken = $this->midtrans->createSnapToken($order);

            return response()->json([
                'success'    => true,
                'snap_token' => $snapToken,
                'order_id'   => $order->order_id,
                'amount'     => $amount,
            ]);
        } catch (\Exception $e) {
            $order->update(['status' => 'failed']);
            Log::error('PaymentController: buyPremium failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Beli Token ───────────────────────────────────────────────────────────

    public function buyTokens(Request $request)
    {
        $request->validate([
            'token_amount' => [
                'required', 'integer',
                'min:' . Order::TOKEN_MIN_PURCHASE,
                'max:' . Order::TOKEN_MAX_PURCHASE,
            ],
        ]);

        $user        = Auth::user();
        $tokenAmount = (int) $request->token_amount;
        $amount      = $tokenAmount * Order::TOKEN_PRICE;

        $order = Order::create([
            'user_id'      => $user->id,
            'type'         => 'token_purchase',
            'quantity'     => $tokenAmount,
            'amount'       => $amount,
            'token_amount' => $tokenAmount,
            'status'       => 'pending',
            'order_id'     => Order::generateOrderId($user->id),
            'expired_at'   => now()->addHours(24),
        ]);

        try {
            $snapToken = $this->midtrans->createSnapToken($order);

            return response()->json([
                'success'      => true,
                'snap_token'   => $snapToken,
                'order_id'     => $order->order_id,
                'token_amount' => $tokenAmount,
                'amount'       => $amount,
            ]);
        } catch (\Exception $e) {
            $order->update(['status' => 'failed']);
            Log::error('PaymentController: buyTokens failed', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─── Notification (Webhook dari Midtrans) ─────────────────────────────────

    public function notification(Request $request)
    {
        $payload = $request->all();

        Log::info('Midtrans notification received', ['order_id' => $payload['order_id'] ?? 'unknown']);

        // Verifikasi signature
        if (!$this->midtrans->verifyNotification($payload)) {
            Log::warning('Midtrans: invalid signature', ['payload' => $payload]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $orderId = $payload['order_id'] ?? null;
        $order   = Order::where('order_id', $orderId)->first();

        if (!$order) {
            Log::warning('Midtrans: order not found', ['order_id' => $orderId]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Jangan proses ulang order yang sudah paid
        if ($order->isPaid()) {
            return response()->json(['message' => 'Already processed']);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus       = $payload['fraud_status'] ?? '';

        // Update response Midtrans
        $order->update(['midtrans_response' => $payload]);

        if ($this->midtrans->isTransactionPaid($payload)) {
            $this->handleSuccessfulPayment($order, $payload);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            $order->update(['status' => $transactionStatus === 'expire' ? 'expired' : 'failed']);
            Log::info('Midtrans: payment failed', ['order_id' => $orderId, 'status' => $transactionStatus]);
        }

        return response()->json(['message' => 'OK']);
    }

    // ─── Finish (redirect dari Midtrans Snap) ─────────────────────────────────

    public function finish(Request $request)
    {
        $orderId = $request->get('order_id');
        $order   = null;

        if ($orderId) {
            $order = Order::where('order_id', $orderId)
                ->where('user_id', Auth::id())
                ->first();

            // Cek status ke Midtrans jika order masih pending
            if ($order && $order->isPending()) {
                $status = $this->midtrans->getTransactionStatus($orderId);
                if ($this->midtrans->isTransactionPaid($status)) {
                    $this->handleSuccessfulPayment($order, $status);
                    $order->refresh();
                }
            }
        }

        return view('payment.finish', compact('order'));
    }

    // ─── Riwayat Order ────────────────────────────────────────────────────────

    public function history()
    {
        $user   = Auth::user();
        $orders = $user->orders()->latest()->paginate(15);

        return view('payment.history', compact('user', 'orders'));
    }

    // ─── Cek Status Order (polling dari JS) ───────────────────────────────────

    public function checkStatus(string $orderId)
    {
        $order = Order::where('order_id', $orderId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'error' => 'Order tidak ditemukan'], 404);
        }

        return response()->json([
            'success'       => true,
            'status'        => $order->status,
            'is_paid'       => $order->isPaid(),
            'token_balance' => Auth::user()->fresh()->token_balance,
        ]);
    }

    // ─── Private: Proses pembayaran sukses ────────────────────────────────────

    private function handleSuccessfulPayment(Order $order, array $payload): void
    {
        $order->update([
            'status'         => 'paid',
            'payment_type'   => $payload['payment_type'] ?? null,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'paid_at'        => now(),
        ]);

        $user = $order->user;

        if ($order->isPremiumOrder()) {
            // Upgrade ke premium
            $this->tokenService->upgradeToPremium($user, $order->quantity * 30);
            Log::info('Payment: premium activated', [
                'user_id'  => $user->id,
                'months'   => $order->quantity,
                'order_id' => $order->order_id,
            ]);
        } elseif ($order->isTokenOrder()) {
            // Tambah token
            $user->addTokens(
                amount:      $order->token_amount,
                type:        'purchase',
                description: 'Beli ' . $order->token_amount . ' token (Order: ' . $order->order_id . ')',
            );
            Log::info('Payment: tokens added', [
                'user_id'      => $user->id,
                'token_amount' => $order->token_amount,
                'order_id'     => $order->order_id,
            ]);
        }
    }
}