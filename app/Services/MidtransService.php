<?php

namespace App\Services;

use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MidtransService
{
    private string $serverKey;
    private string $clientKey;
    private bool   $isProduction;
    private string $snapUrl;
    private string $apiUrl;

    public function __construct()
    {
        $this->serverKey    = config('services.midtrans.server_key');
        $this->clientKey    = config('services.midtrans.client_key');
        $this->isProduction = config('services.midtrans.is_production', false);
        $this->snapUrl      = $this->isProduction
            ? 'https://app.midtrans.com/snap/v1/transactions'
            : 'https://app.sandbox.midtrans.com/snap/v1/transactions';
        $this->apiUrl       = $this->isProduction
            ? 'https://api.midtrans.com/v2'
            : 'https://api.sandbox.midtrans.com/v2';
    }

    /**
     * Buat Snap token untuk order baru
     */
    public function createSnapToken(Order $order): string
    {
        $user = $order->user;

        $payload = [
            'transaction_details' => [
                'order_id'     => $order->order_id,
                'gross_amount' => $order->amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email'      => $user->email,
            ],
            'item_details' => [
                [
                    'id'       => $order->type,
                    'price'    => $order->amount,
                    'quantity' => 1,
                    'name'     => $order->getTypeLabel(),
                ],
            ],
            'callbacks' => [
                'finish' => route('payment.finish'),
            ],
        ];

        Log::info('Midtrans: creating snap token', ['order_id' => $order->order_id]);

        $response = Http::withBasicAuth($this->serverKey, '')
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post($this->snapUrl, $payload);

        if ($response->failed()) {
            $error = $response->json('error_messages', [$response->body()]);
            Log::error('Midtrans: snap token failed', ['error' => $error]);
            throw new \Exception('Midtrans error: ' . implode(', ', (array) $error));
        }

        $snapToken = $response->json('token');

        // Simpan snap token ke order
        $order->update(['snap_token' => $snapToken]);

        Log::info('Midtrans: snap token created', ['token' => $snapToken]);

        return $snapToken;
    }

    /**
     * Verifikasi notifikasi dari Midtrans (webhook)
     */
    public function verifyNotification(array $payload): bool
    {
        $orderId       = $payload['order_id'] ?? '';
        $statusCode    = $payload['status_code'] ?? '';
        $grossAmount   = $payload['gross_amount'] ?? '';
        $serverKey     = $this->serverKey;

        $signatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return $signatureKey === ($payload['signature_key'] ?? '');
    }

    /**
     * Ambil status transaksi dari Midtrans
     */
    public function getTransactionStatus(string $orderId): array
    {
        $response = Http::withBasicAuth($this->serverKey, '')
            ->get("{$this->apiUrl}/{$orderId}/status");

        return $response->json() ?? [];
    }

    /**
     * Tentukan apakah transaksi sudah lunas berdasarkan response Midtrans
     */
    public function isTransactionPaid(array $payload): bool
    {
        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus       = $payload['fraud_status'] ?? '';
        $paymentType       = $payload['payment_type'] ?? '';

        // Settlement = transfer bank, kartu kredit (sudah dikonfirmasi)
        if ($transactionStatus === 'settlement') return true;

        // Capture = kartu kredit (capture langsung)
        if ($transactionStatus === 'capture') {
            return $fraudStatus === 'accept';
        }

        // pending = menunggu (belum lunas)
        // deny, expire, cancel = gagal
        return false;
    }

    public function getClientKey(): string
    {
        return $this->clientKey;
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    public function getSnapJsUrl(): string
    {
        return $this->isProduction
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }
}