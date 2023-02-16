<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet\Resolver;

interface BlindIndexTransformationResolverInterface
{
    public function resolve(array $identifiers): array;
}
