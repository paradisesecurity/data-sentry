<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Response;

interface ResponseInterface
{
    public const ENCRYPTED_RESPONSE_TYPE = 'encrypted';

    public const DECRYPTED_RESPONSE_TYPE = 'decrypted';
}
