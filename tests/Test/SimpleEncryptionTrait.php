<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Test;

use const SODIUM_CRYPTO_SECRETBOX_NONCEBYTES;

trait SimpleEncryptionTrait
{
    private string $secretKey;

    private string $nonce;

    public function setUp(): void
    {
        $this->secretKey = sodium_crypto_stream_xchacha20_keygen();
        $this->nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
    }

    protected function encryptOriginalValue(mixed $value): string
    {
        return $this->useEncryptionMethod(
            $value,
            $this->nonce,
            $this->secretKey
        );
    }

    protected function decryptEncryptedValue(string $encrypted): mixed
    {
        return $this->useEncryptionMethod(
            $encrypted,
            $this->nonce,
            $this->secretKey
        );
    }

    protected function useEncryptionMethod(mixed $value, string $nonce, string $secretKey): mixed
    {
        return sodium_crypto_stream_xchacha20_xor(
            $value,
            $nonce,
            $secretKey
        );
    }
}
