<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Encryptor;

interface EncryptorAdapterInterface
{
    public const CIPHERSWEET_ADAPTER_NAME = 'ciphersweet';

    public const HALITE_ADAPTER_NAME = 'halite';
}
