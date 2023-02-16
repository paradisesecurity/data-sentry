<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Cache;

use function hash_final;
use function hash_init;
use function hash_update;

trait CachedKeyIdentifierTrait
{
    public function createCachedKeyIdentifier(
        string $entityHash,
        string $propertyName,
        string $propertyType,
        mixed $originalPropertyValue,
    ): string {
        return $this->hashData($entityHash, $propertyName, $propertyType, $originalPropertyValue);
    }

    protected function hashData(
        string $entityHash,
        string $propertyName,
        string $propertyType,
        mixed $originalPropertyValue,
    ): string {
        $hmac = hash_init('sha256', HASH_HMAC, $entityHash);
        hash_update($hmac, $propertyName);
        hash_update($hmac, $propertyType);
        hash_update($hmac, $originalPropertyValue);
        return hash_final($hmac);
    }
}
