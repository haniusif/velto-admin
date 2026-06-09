<?php

namespace Tests\Unit;

use App\Services\ARB\ArbCrypto;
use App\Services\ARB\ArbGateway;
use PHPUnit\Framework\TestCase;

class ArbCryptoTest extends TestCase
{
    private const KEY = '0123456789abcdef0123456789abcdef'; // 32 bytes → AES-256

    public function test_encrypt_then_decrypt_round_trips(): void
    {
        $crypto = new ArbCrypto(self::KEY);

        $plain = json_encode([[
            'amt' => '114.00',
            'action' => '1',
            'currencyCode' => '682',
            'trackId' => '42',
            'responseURL' => 'https://admin.velto.sa/api/v1/payments/arb/callback',
        ]]);

        $trandata = $crypto->encrypt($plain);

        $this->assertSame($plain, $crypto->decrypt($trandata));
    }

    public function test_trandata_is_urlencoded_hex(): void
    {
        $crypto = new ArbCrypto(self::KEY);

        $trandata = $crypto->encrypt('{"a":"b/c:d"}');

        // urlencode of a hex string is a no-op, so the wire value must be pure hex.
        $this->assertMatchesRegularExpression('/^[0-9a-f]+$/', $trandata);
        // AES block size is 16 bytes → ciphertext length is a multiple of 32 hex chars.
        $this->assertSame(0, strlen($trandata) % 32);
    }

    public function test_round_trips_payload_with_special_characters(): void
    {
        $crypto = new ArbCrypto(self::KEY);
        $plain = '{"note":"100% clean — Olaya/Riyadh","amt":"50.00"}';

        $this->assertSame($plain, $crypto->decrypt($crypto->encrypt($plain)));
    }

    public function test_gateway_parses_a_captured_final_response(): void
    {
        $crypto = new ArbCrypto(self::KEY);
        $gateway = new ArbGateway($crypto, [
            'tranportal_id' => 'TID',
            'tranportal_password' => 'PW',
            'resource_key' => self::KEY,
            'pg_url' => 'https://example.test/pg',
            'currency_code' => '682',
        ]);

        // Mimic ARB's encrypted final response (single-element array envelope).
        $finalPlain = json_encode([[
            'paymentId' => '100201931620827468',
            'result' => 'CAPTURED',
            'transId' => '201935166561122',
            'ref' => '512345678901',
            'trackId' => '42',
            'amt' => '114.00',
        ]]);
        $trandata = $crypto->encrypt($finalPlain);

        $parsed = $gateway->parseFinalResponse($trandata);

        $this->assertTrue($parsed['captured']);
        $this->assertSame('201935166561122', $parsed['trans_id']);
        $this->assertSame('42', $parsed['track_id']);
        $this->assertSame('CAPTURED', $parsed['result']);
    }
}
