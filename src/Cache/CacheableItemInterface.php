<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Cache;

interface CacheableItemInterface
{
    public const ENCRYPTED_PROPERTY_TYPE = 'encrypted';

    public const DECRYPTED_PROPERTY_TYPE = 'decrypted';
}
