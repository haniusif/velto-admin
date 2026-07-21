<?php

namespace App\Services\ARB;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Al Rajhi Bank (Neoleap) payment gateway client — Bank Hosted flow.
 *
 * Reference: ARB REST Integration Guide v1.31, "Bank Hosted Integration" and
 * "Merchant Hosted Transaction Flow (Inquiry, Void, Refund, Capture)".
 *
 * The on-the-wire envelope (single-element JSON array, JSON body) and the
 * URL-encoded-hex AES-256-CBC `trandata` are confirmed working against the
 * Neoleap sandbox (token generated, encrypted response decrypted end-to-end).
 */
class ArbGateway
{
    public const ACTION_PURCHASE = '1';
    public const ACTION_REFUND = '2';
    public const ACTION_VOID = '3';
    public const ACTION_INQUIRY = '8';

    public function __construct(
        private readonly ArbCrypto $crypto,
        private readonly array $config,
    ) {
    }

    public function isConfigured(): bool
    {
        return filled($this->config['tranportal_id'])
            && filled($this->config['tranportal_password'])
            && filled($this->config['resource_key'])
            && filled($this->config['pg_url']);
    }

    /**
     * Generate a Purchase payment token and return the bank-hosted page URL.
     *
     * @return array{payment_id:string, payment_url:string, raw:array}
     */
    public function createPurchaseToken(array $opts): array
    {
        $trandata = [
            'amt' => $this->formatAmount($opts['amount']),
            'action' => self::ACTION_PURCHASE,
            'password' => $this->config['tranportal_password'],
            'id' => $this->config['tranportal_id'],
            'currencyCode' => (string) $this->config['currency_code'],
            'trackId' => (string) $opts['track_id'],
            'responseURL' => $opts['response_url'],
            'errorURL' => $opts['error_url'],
        ];

        if (! empty($opts['lang'])) {
            // Doc specifies 'AR' for Arabic; 'EN' for the English payment page.
            $trandata['langid'] = $opts['lang'] === 'ar' ? 'AR' : 'EN';
        }
        foreach (['udf1', 'udf2', 'udf3', 'udf4', 'udf5'] as $udf) {
            if (! empty($opts[$udf])) {
                $trandata[$udf] = (string) $opts[$udf];
            }
        }

        $response = $this->send($this->config['pg_url'], [
            'id' => $this->config['tranportal_id'],
            'trandata' => $this->crypto->encrypt($this->encodePayload($trandata)),
            'responseURL' => $opts['response_url'],
            'errorURL' => $opts['error_url'],
        ], $opts['customer_ip'] ?? null);

        $row = $this->firstRow($response);

        if (($row['status'] ?? null) !== '1' || empty($row['result'])) {
            throw new ArbException(
                'ARB token generation failed: '.($row['error'] ?? '?').' '.($row['errorText'] ?? '')
            );
        }

        // result = "paymentId:paymentPageURL"
        [$paymentId, $pageUrl] = explode(':', $row['result'], 2);

        return [
            'payment_id' => $paymentId,
            'payment_url' => $pageUrl.'?PaymentID='.$paymentId,
            'raw' => $row,
        ];
    }

    /**
     * Decrypt and normalise the final (redirect/webhook) transaction response.
     *
     * @return array{payment_id:?string, result:?string, trans_id:?string, ref:?string, track_id:?string, amt:?string, captured:bool, raw:array}
     */
    public function parseFinalResponse(string $trandata): array
    {
        $plain = $this->crypto->decrypt($trandata);
        $row = $this->firstRow($this->decodePayloadEnvelope($plain));

        $result = $row['result'] ?? null;

        return [
            'payment_id' => $row['paymentId'] ?? null,
            'result' => $result,
            'trans_id' => $row['transId'] ?? null,
            'ref' => $row['ref'] ?? null,
            'track_id' => $row['trackId'] ?? null,
            'amt' => $row['amt'] ?? null,
            // CAPTURED = purchase success, APPROVED = authorization success.
            'captured' => in_array($result, ['CAPTURED', 'APPROVED'], true),
            'raw' => $row,
        ];
    }

    /**
     * Refund a previously captured transaction by its transId (full or partial).
     *
     * @return array{success:bool, result:?string, raw:array}
     */
    public function refund(string $transId, float $amount, ?string $trackId = null): array
    {
        $trandata = [
            'id' => $this->config['tranportal_id'],
            'password' => $this->config['tranportal_password'],
            'action' => self::ACTION_REFUND,
            'amt' => $this->formatAmount($amount),
            'currencyCode' => (string) $this->config['currency_code'],
            'udf5' => 'TRANID', // we reference the original by its transId
            'transId' => $transId,
        ];
        if ($trackId !== null) {
            $trandata['trackId'] = $trackId;
        }

        $endpoint = $this->config['admin_url'] ?: $this->config['pg_url'];

        $response = $this->send($endpoint, [
            'id' => $this->config['tranportal_id'],
            'trandata' => $this->crypto->encrypt($this->encodePayload($trandata)),
        ], null);

        $plainRow = $this->decryptResponseRow($response);
        $result = $plainRow['result'] ?? null;

        return [
            'success' => in_array($result, ['CAPTURED', 'APPROVED', 'SUCCESS'], true),
            'result' => $result,
            'raw' => $plainRow,
        ];
    }

    /**
     * Authoritative transaction status inquiry (action 8). Ask Neoleap directly
     * for the real state instead of trusting the browser redirect's `status`.
     * Reference a transaction by transId / paymentId / trackId.
     *
     * @return array{found:bool, captured:bool, result:?string, trans_id:?string, ref:?string, payment_id:?string, track_id:?string, amt:?string, raw:array}
     */
    public function inquire(array $opts): array
    {
        $trandata = [
            'id' => $this->config['tranportal_id'],
            'password' => $this->config['tranportal_password'],
            'action' => self::ACTION_INQUIRY,
            'currencyCode' => (string) $this->config['currency_code'],
        ];
        if (! empty($opts['trans_id'])) {
            $trandata['transId'] = (string) $opts['trans_id'];
            $trandata['udf5'] = 'TRANID';
        }
        if (! empty($opts['payment_id'])) {
            $trandata['paymentId'] = (string) $opts['payment_id'];
        }
        if (! empty($opts['track_id'])) {
            $trandata['trackId'] = (string) $opts['track_id'];
        }

        $endpoint = $this->config['admin_url'] ?: $this->config['pg_url'];

        $response = $this->send($endpoint, [
            'id' => $this->config['tranportal_id'],
            'trandata' => $this->crypto->encrypt($this->encodePayload($trandata)),
        ], null);

        $row = $this->decryptResponseRow($response);
        $result = $row['result'] ?? null;

        return [
            'found' => filled($result),
            'captured' => in_array($result, ['CAPTURED', 'APPROVED'], true),
            'result' => $result,
            'trans_id' => $row['transId'] ?? null,
            'ref' => $row['ref'] ?? null,
            'payment_id' => $row['paymentId'] ?? null,
            'track_id' => $row['trackId'] ?? null,
            'amt' => $row['amt'] ?? null,
            'raw' => $row,
        ];
    }

    // --- internals -------------------------------------------------------

    private function send(string $url, array $body, ?string $customerIp): array
    {
        $request = Http::asJson()->acceptJson()->timeout(30);

        // v1.31 mandatory: customer IP must be first in X-FORWARDED-FOR or the
        // gateway declines on risk assessment.
        if ($customerIp) {
            $request = $request->withHeaders(['X-FORWARDED-FOR' => $customerIp]);
        }

        // Doc samples wrap the request object in a single-element JSON array.
        $response = $request->post($url, [$body]);

        if (! $response->successful()) {
            Log::warning('ARB PG HTTP error', ['status' => $response->status(), 'url' => $url]);
            throw new ArbException('ARB gateway HTTP '.$response->status());
        }

        return $response->json() ?? [];
    }

    /** Initial responses are plain JSON; pick the first row of the array envelope. */
    private function firstRow(array $payload): array
    {
        return array_is_list($payload) ? ($payload[0] ?? []) : $payload;
    }

    /** A refund/void response returns encrypted trandata; decrypt then read row. */
    private function decryptResponseRow(array $response): array
    {
        $row = $this->firstRow($response);
        if (! empty($row['trandata'])) {
            $plain = $this->crypto->decrypt($row['trandata']);

            return $this->firstRow($this->decodePayloadEnvelope($plain));
        }

        return $row;
    }

    private function encodePayload(array $trandata): string
    {
        // Doc plain-trandata samples use a single-element array envelope.
        return json_encode([$trandata], JSON_UNESCAPED_SLASHES);
    }

    private function decodePayloadEnvelope(string $plain): array
    {
        return json_decode($plain, true) ?? [];
    }

    private function formatAmount(float $amount): string
    {
        return number_format($amount, 2, '.', '');
    }
}
