<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WalletTransactionResource;
use App\Models\PaymentTransaction;
use App\Services\ARB\ArbGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function __construct(private readonly ArbGateway $arb)
    {
    }

    public function show(Request $request): JsonResponse
    {
        $customer = $request->user();
        $txs = $customer->walletTransactions()
            ->latest('id')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => [
                'balance' => (float) $customer->wallet_balance,
                'currency' => 'SAR',
                'transactions' => WalletTransactionResource::collection($txs),
            ],
        ]);
    }

    /**
     * Start a real card top-up via the ARB/Neoleap gateway. The wallet is NOT
     * credited here — only after the bank confirms capture (PaymentController).
     */
    public function topUp(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:5000'],
            'payment_method' => ['nullable', 'string', 'in:card,apple_pay'],
        ]);

        $customer = $request->user();

        if (! $this->arb->isConfigured()) {
            return response()->json([
                'message' => 'Card top-up is not available yet.',
                'errors' => ['payment' => ['Payment gateway not configured.']],
            ], 422);
        }

        $trackId = 'TU-'.$customer->id.'-'.Str::upper(Str::random(10));

        $payment = PaymentTransaction::create([
            'customer_id' => $customer->id,
            'appointment_id' => null,
            'gateway' => 'arb',
            'purpose' => 'wallet_topup',
            'action' => 'purchase',
            'status' => PaymentTransaction::STATUS_PENDING,
            'amount' => (float) $data['amount'],
            'currency' => 'SAR',
            'track_id' => $trackId,
        ]);

        try {
            $token = $this->arb->createPurchaseToken([
                'amount' => (float) $data['amount'],
                'track_id' => $trackId,
                'response_url' => $this->callbackUrl('callback'),
                'error_url' => $this->callbackUrl('error'),
                'lang' => $customer->preferred_language,
                'customer_ip' => $request->ip(),
                'udf1' => 'wallet_topup',
            ]);
        } catch (\Throwable $e) {
            $payment->update([
                'status' => PaymentTransaction::STATUS_FAILED,
                'error_text' => mb_substr($e->getMessage(), 0, 250),
            ]);
            Log::warning('ARB top-up token generation failed', [
                'payment' => $payment->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Could not start card payment. Please try again.',
            ], 502);
        }

        $payment->update(['payment_id' => $token['payment_id']]);

        return response()->json([
            'data' => [
                'status' => 'pending',
                'amount' => (float) $data['amount'],
                'currency' => 'SAR',
                'payment_page_url' => $token['payment_url'],
            ],
        ], 201);
    }

    private function callbackUrl(string $type): string
    {
        $base = rtrim((string) config('services.arb.callback_base'), '/');

        return "{$base}/api/v1/payments/arb/{$type}";
    }
}
