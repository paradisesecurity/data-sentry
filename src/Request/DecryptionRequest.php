<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Request;

class DecryptionRequest extends AbstractRequest
{
    public function __construct()
    {
        $this->type = RequestInterface::DECRYPTION_REQUEST_TYPE;

        parent::__construct();
    }
}
