<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Attribute;

interface AttributeInterface
{
    public const BLIND_INDEX_ATTRIBUTE = BlindIndex::class;

    public const ENCRYPTED_ATTRIBUTE = Encrypted::class;

    public const INDEXED_ATTRIBUTE = Indexed::class;

    public const MIGRATE_ENCRYPTION_ATTRIBUTE = MigrateEncryption::class;

    public const REQUEST_TYPES = [
        self::ENCRYPTED_ATTRIBUTE,
        self::INDEXED_ATTRIBUTE,
        self::MIGRATE_ENCRYPTION_ATTRIBUTE,
    ];

    public const SUBREQUEST_TYPES = [
        self::BLIND_INDEX_ATTRIBUTE,
    ];
}
