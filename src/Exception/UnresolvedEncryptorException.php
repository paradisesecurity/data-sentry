<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Exception;

use function sprintf;

final class UnresolvedEncryptorException extends \Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('The supplied name "%s" could not be resolved to a matching encryptor!', $name));
    }
}
