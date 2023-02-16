<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Request;

class EncryptionRequest extends AbstractRequest
{
    public function __construct()
    {
        $this->type = RequestInterface::ENCRYPTION_REQUEST_TYPE;

        parent::__construct();
    }
}
