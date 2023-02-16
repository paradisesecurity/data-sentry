<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Encryptor;

interface EncryptorInterface
{
    public const DEFAULT_FILTER_BITS = 32;

    public const DEFAULT_BLOOM_FILTER_BITS = 32;
}
