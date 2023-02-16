<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Test;

use ParadiseSecurity\Component\DataSentry\Model\EncryptableInterface;
use ParadiseSecurity\Component\DataSentry\Test\Model\FakeModel;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

trait FakeModelTrait
{
    protected NameConverterInterface $normalizer;

    public function setUp(): void
    {
        $this->normalizer = new CamelCaseToSnakeCaseNameConverter();
    }

    protected function getDecryptedFakeModelConfig(): array
    {
        return [
            'name' => 'David',
            'account_number' => '786FTR32',
            'account_number_bi' => '12eee4ca',
            'secret_number' => 22,
            'secret_number_encrypted' => null,
        ];
    }

    protected function getEncryptedFakeModelConfig(): array
    {
        return [
            'name' => 'fips:BHvcfJdgauBeV6z2GADJwYE1hz307O7evIhy5wAoM_Dk4Ch6Eoxc_tW0_RGK_A8xigOFVsbBvfPv2w-BuyfI_iBSqJZ2ca2tKpXLZ9UzJ1jAt94JUO1V4hrtPQkbG2OjTPom--8=',
            'account_number' => 'brng:P-A-RaEkm73zkyqN4yrwqLTXVjYJ87roO4OvtMGy-EbbiNxEAxiBkTHEZUynWIAg82QuQo0-T6A2BH1eaItzTw==',
            'account_number_bi' => '12eee4ca',
            'secret_number' => 0,
            'secret_number_encrypted' => 'brng:Q9Gzw9Ar7ZmqSmVDtT7NNZ_f3Xz5AEi1ZdihsXNI8GC9_4mm7EZDsgatk3OyU7WMI1WnQr3Q0izG5pRkftaTdg==',
        ];
    }

    protected function createFakeModel(array $config): EncryptableInterface
    {
        $fake = new FakeModel();

        foreach ($config as $name => $property) {
            $setter = $this->convertSnakeCaseToCamelCase(sprintf('set_%s', $name));
            if (is_callable([$fake, $setter])) {
                $fake->$setter($property);
            }
        }

        return $fake;
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
