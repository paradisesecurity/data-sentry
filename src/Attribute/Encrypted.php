<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Attribute;

use Attribute;
use ParadiseSecurity\Component\DataSentry\Encryptor\EncryptorInterface;

/**
 * This attribute is used to encrypt an entity property.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Encrypted implements AttributeInterface
{
    public function __construct(
        public ?string $encryptor = null,
        public ?string $additionalAuthenticationData = null,
        public int $filterBits = EncryptorInterface::DEFAULT_FILTER_BITS,
        public bool $forceEncrypt = false,
        public ?string $mappedTypedProperty = null,
    ) {
    }
}
