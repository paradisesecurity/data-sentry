<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Attribute;

use Attribute;
use ParadiseSecurity\Component\DataSentry\Encryptor\EncryptorInterface;
use ParagonIE\CipherSweet\Contract\TransformationInterface;
use ParadiseSecurity\Component\DataSentry\Exception\AttributeException;

use function array_values;
use function is_array;

#[Attribute(Attribute::TARGET_PROPERTY)]
class BlindIndex implements AttributeInterface
{
    public array $transformations = [];

    public function __construct(
        public ?string $indexOf = null,
        public int $bloomFilterBits = EncryptorInterface::DEFAULT_BLOOM_FILTER_BITS,
        public bool $fastIndexing = false,
        array $transformations = [],
        public array $hashConfig = [],
    ) {
        if (!is_array($transformations)) {
            $transformations = [$transformations];
        }

        foreach ($transformations as $transformation) {
            if (!($transformation instanceof TransformationInterface)) {
                throw AttributeException::invalidType(TransformationInterface::class, $transformation);
            }
        }

        $this->transformations = array_values($transformations);
    }
}
