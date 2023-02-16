<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Request;

interface RequestInterface
{
    public const ENCRYPTION_REQUEST_TYPE = 'encryption';

    public const DECRYPTION_REQUEST_TYPE = 'decryption';

    public const MAIN_ENCRYPTOR_TYPE = 'main';

    public const PREVIOUS_ENCRYPTOR_TYPE = 'previous';
}
