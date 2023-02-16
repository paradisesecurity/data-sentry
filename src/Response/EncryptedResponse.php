<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Response;

class EncryptedResponse extends AbstractResponse
{
    public function __construct()
    {
        $this->type = ResponseInterface::ENCRYPTED_RESPONSE_TYPE;
    }
}
