<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Attribute;

use Attribute;
use ParadiseSecurity\Component\DataSentry\Exception\AttributeException;

/**
 * This attribute is used to migrate encryption of an entity property.
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
final class MigrateEncryption implements AttributeInterface
{
    public AttributeInterface $migration;

    public function __construct(AttributeInterface $migration)
    {
        if (!($migration instanceof Encrypted)) {
            throw AttributeException::invalidType(Encrypted::class, $migration);
        }

        $this->migration = $migration;
    }
}
