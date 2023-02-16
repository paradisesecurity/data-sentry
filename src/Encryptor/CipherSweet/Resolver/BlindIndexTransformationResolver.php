<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet\Resolver;

use ParadiseSecurity\Component\ServiceRegistry\Registry\ServiceRegistryInterface;
use ParagonIE\CipherSweet\Contract\TransformationInterface;

final class BlindIndexTransformationResolver implements BlindIndexTransformationResolverInterface
{
    public function __construct(private ServiceRegistryInterface $registry)
    {
    }

    public function resolve(array $identifiers): array
    {
        $transformations = [];

        foreach ($identifiers as $identifier) {
            if (is_string($identifier) === false) {
                continue;
            }

            if (!$this->registry->has($identifier)) {
                continue;
            }

            $transformation = $this->registry->get($identifier);

            if ($transformation instanceof TransformationInterface) {
                $transformations[] = $transformation;
            }
        }

        return $transformations;
    }
}
