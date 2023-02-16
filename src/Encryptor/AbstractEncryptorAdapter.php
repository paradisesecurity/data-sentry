<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Encryptor;

use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

abstract class AbstractEncryptorAdapter implements EncryptorAdapterInterface
{
    protected string $name;

    protected NameConverterInterface $normalizer;

    public function __construct()
    {
        $this->normalizer = new CamelCaseToSnakeCaseNameConverter();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    protected function convertSnakeCaseToCamelCase(string $string): string
    {
        return $this->normalizer->denormalize($string);
    }

    protected function convertCamelCaseToSnakeCase(string $string): string
    {
        return $this->normalizer->normalize($string);
    }
}
