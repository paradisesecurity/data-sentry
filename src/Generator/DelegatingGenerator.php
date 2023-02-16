<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Generator;

use ParadiseSecurity\Component\DataSentry\Attribute\AttributeInterface;
use ParadiseSecurity\Component\DataSentry\Exception\UndefinedGeneratorException;
use ParadiseSecurity\Component\DataSentry\Model\EncryptableInterface;
use ParadiseSecurity\Component\ServiceRegistry\Registry\ServiceRegistryInterface;

final class DelegatingGenerator implements DelegatingGeneratorInterface
{
    public function __construct(private ServiceRegistryInterface $registry)
    {
    }

    public function generate(EncryptableInterface $entity, string $value, array $methods): array
    {
        if (empty($methods)) {
            throw new UndefinedGeneratorException(sprintf("You must define a generator method for %s", AttributeInterface::INDEXED_ATTRIBUTE));
        }

        $possibleValuesAr = [$value];

        foreach ($methods as $method) {
            if (!$this->registry->has($method)) {
                throw new UndefinedGeneratorException(sprintf("No generator found for method %s", $method));
            }

            $generator = $this->registry->get($method);

            if (!($generator instanceof GeneratorInterface)) {
                throw new \TypeError(sprintf("The generator is not an instance of %s", GeneratorInterface::class));
            }

            $possibleValues = $generator->generate($value);
            array_push($possibleValuesAr, ...$possibleValues);
        }

        return $possibleValuesAr;
    }
}
