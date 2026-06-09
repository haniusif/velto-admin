<?php

namespace App\Services\ARB;

/**
 * AES encryption/decryption for the Al Rajhi Bank (Neoleap) payment gateway.
 *
 * Ported from the authoritative PHP sample in the ARB REST Integration Guide
 * v1.31 (§ "Sample Encryption and Decryption Code for PHP"):
 *   - cipher AES-256-CBC, fixed IV "PGKEYENCDECIVSPC", key = Resource Key
 *   - plaintext is URL-encoded, then PKCS5-padded, then encrypted with
 *     OPENSSL_ZERO_PADDING (the padding is applied manually), then the raw
 *     ciphertext is hex-encoded and URL-encoded for transport.
 *   - decryption reverses each step.
 */
class ArbCrypto
{
    private const IV = 'PGKEYENCDECIVSPC';
    private const CIPHER = 'aes-256-cbc';

    public function __construct(private readonly string $resourceKey)
    {
    }

    /** Encrypt a plaintext JSON string into the urlencoded-hex `trandata` value. */
    public function encrypt(string $plaintext): string
    {
        // Doc: "Before encrypting transaction data, data needs to be URL-Encoded."
        $encoded = urlencode($plaintext);
        $padded = $this->pkcs5Pad($encoded);

        $raw = openssl_encrypt(
            $padded,
            self::CIPHER,
            $this->resourceKey,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            self::IV,
        );

        if ($raw === false) {
            throw new ArbException('ARB trandata encryption failed.');
        }

        return urlencode(bin2hex($raw));
    }

    /** Decrypt an urlencoded-hex `trandata` value back into the plaintext JSON string. */
    public function decrypt(string $trandata): string
    {
        $hex = urldecode(trim($trandata));
        $raw = hex2bin($hex);

        if ($raw === false) {
            throw new ArbException('ARB trandata is not valid hex.');
        }

        $decrypted = openssl_decrypt(
            $raw,
            self::CIPHER,
            $this->resourceKey,
            OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING,
            self::IV,
        );

        if ($decrypted === false) {
            throw new ArbException('ARB trandata decryption failed.');
        }

        return urldecode($this->pkcs5Unpad($decrypted));
    }

    private function pkcs5Pad(string $text): string
    {
        $blockSize = 16;
        $pad = $blockSize - (strlen($text) % $blockSize);

        return $text.str_repeat(chr($pad), $pad);
    }

    private function pkcs5Unpad(string $text): string
    {
        if ($text === '') {
            return $text;
        }

        $pad = ord($text[strlen($text) - 1]);
        if ($pad < 1 || $pad > 16 || $pad > strlen($text)) {
            return $text; // not padded / corrupt — return as-is
        }

        return substr($text, 0, -$pad);
    }
}
