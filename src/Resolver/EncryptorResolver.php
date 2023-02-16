<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Resolver;

use ParadiseSecurity\Component\DataSentry\Encryptor\EncryptorInterface;
use ParadiseSecurity\Component\DataSentry\Exception\UnresolvedEncryptorException;
use ParadiseSecurity\Component\ServiceRegistry\Registry\ServiceRegistryInterface;

final class EncryptorResolver implements EncryptorResolverInterface
{
    public function __construct(private ServiceRegistryInterface $registry)
    {
    }

    public function resolve(string $encryptor): EncryptorInterface
    {
        if ($this->registry->has($encryptor)) {
            return $this->registry->get($encryptor);
        }

        throw new UnresolvedEncryptorException($encryptor);
    }
}
