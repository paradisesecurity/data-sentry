<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Response;

class DecryptedResponse extends AbstractResponse
{
    public function __construct()
    {
        $this->type = ResponseInterface::DECRYPTED_RESPONSE_TYPE;
    }
}
