<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\ARB\ArbGateway;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Payments\PaymentSettler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

/**
 * Public ARB/Neoleap callbacks. These are hit by the bank, not the app, so
 * they carry no customer token. The app's WebView watches for navigation to
 * `/payments/arb/done` to know the flow finished, then re-fetches the booking.
 */
class PaymentController extends Controller
{
    public function __construct(
        private readonly ArbGateway $arb,
        private readonly NotificationDispatcher $notifications,
        private readonly PaymentSettler $settler,
    ) {
    }

    /** responseURL — final redirect for a completed (success/declined) transaction. */
    public function callback(Request $request): Response
    {
        $status = $this->handleRedirect($request);

        return $this->doneRedirect($status);
    }

    /** errorURL — gateway/processing error before a result was reached. */
    public function error(Request $request): Response
    {
        $this->handleRedirect($request, forceFailure: true);

        return $this->doneRedirect('failed');
    }

    /** Webhook — server-to-server notification (reliable channel). Idempotent. */
    public function webhook(Request $request): JsonResponse
    {
        $trandata = $request->input('trandata')
            ?? data_get($request->input('payLoad'), 'trandata');

        try {
            if ($trandata) {
                $this->settler->apply($this->arb->parseFinalResponse($trandata));
            } else {
                // Some webhook payloads are plain JSON (payLoad + result).
                $this->applyFromWebhookPayload($request->all());
            }
        } catch (\Throwable $e) {
            Log::warning('ARB webhook processing failed', ['error' => $e->getMessage()]);

            return response()->json(['received' => true]); // ack to avoid retries storm
        }

        return response()->json(['received' => true]);
    }

    /** Terminal page the in-app WebView detects to close and refresh. */
    public function done(Request $request): Response
    {
        $status = $request->query('status', 'unknown');

        return response(
            "<!doctype html><html><head><meta charset=\"utf-8\"><title>Payment {$status}</title></head>".
            "<body>Payment {$status}. You can return to the app.</body></html>"
        )->header('Content-Type', 'text/html');
    }

    // --- internals -------------------------------------------------------

    private function handleRedirect(Request $request, bool $forceFailure = false): string
    {
        $trandata = $request->input('trandata');

        try {
            if (! $trandata) {
                return 'failed';
            }
            $parsed = $this->arb->parseFinalResponse($trandata);
            if ($forceFailure) {
                $parsed['captured'] = false;
            }
            $this->settler->apply($parsed);

            return $parsed['captured'] ? 'success' : 'failed';
        } catch (\Throwable $e) {
            Log::warning('ARB callback processing failed', ['error' => $e->getMessage()]);

            return 'failed';
        }
    }



    private function applyFromWebhookPayload(array $payload): void
    {
        $row = data_get($payload, 'payLoad', $payload);
        $result = data_get($payload, 'result.result') ?? data_get($payload, 'result');

        $this->settler->apply([
            'payment_id' => $row['paymentId'] ?? null,
            'track_id' => $row['trackId'] ?? null,
            'trans_id' => $row['transId'] ?? null,
            'ref' => $row['ref'] ?? null,
            'result' => is_string($result) ? $result : null,
            'amt' => $row['amt'] ?? null,
            'captured' => in_array($result, ['CAPTURED', 'APPROVED'], true),
            'raw' => $payload,
        ]);
    }


    private function doneRedirect(string $status): Response
    {
        $base = rtrim((string) config('services.arb.callback_base'), '/');

        return redirect("{$base}/api/v1/payments/arb/done?status={$status}");
    }
}
