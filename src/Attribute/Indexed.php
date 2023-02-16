<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Attribute;

use ParadiseSecurity\Component\DataSentry\Encryptor\EncryptorInterface;
use Attribute;

/**
 * The Encrypted class handles the @IndexableField attribute.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IndexableField implements AttributeInterface
{
    public function __construct(
        public string $indexesEntityClass,
        public string $valuePreprocessMethod,
        public array $indexesGenerationMethods = [],
        public bool $autoRefresh = true,
        public bool $fastIndexing = EncryptorInterface::DEFAULT_FAST_INDEXING,
    ) {
    }
}
