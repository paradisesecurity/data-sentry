<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Cache;

use ParadiseSecurity\Component\DataSentry\Cache\CacheAdapterInterface;
use ParadiseSecurity\Component\DataSentry\Cache\Symfony\SymfonyCachePoolAdapter;

class Cache implements CacheInterface
{
    use CachedKeyIdentifierTrait;

    private CacheAdapterInterface $cacheAdapter;

    public function __construct(
        CacheAdapterInterface $cacheAdapter = null,
    ) {
        if (is_null($cacheAdapter)) {
            $cacheAdapter = new SymfonyCachePoolAdapter();
        }
        $this->cacheAdapter = $cacheAdapter;
    }

    public function getCachedKeyIdentifier(array $map): string
    {
        return $this->createCachedKeyIdentifier(
            $map['entity_hash'],
            $map['name'],
            $map['type'],
            $map['original_value'],
        );
    }

    public function isItemCached(array $map): bool
    {
        $key = $this->getCachedKeyIdentifier($map);

        return $this->cacheAdapter->hasItem($key);
    }

    public function getCachedItem(array $map): CacheableItemInterface
    {
        $key = $this->getCachedKeyIdentifier($map);

        $cacheItem = $this->cacheAdapter->get($key);

        if (is_null($cacheItem)) {
            $cacheItem = $this->createCachedPropertyItem($map);
        }

        return $cacheItem;
    }

    public function storeUnCachedItem(
        array $map,
        CacheableItemInterface $cacheItem,
    ): void {
        $key = $this->getCachedKeyIdentifier($map);

        $this->cacheAdapter->save($key, $cacheItem);
    }

    public function createCachedPropertyItem(array $map): CacheableItemInterface
    {
        $cacheItem = new CachedPropertyItem();
        $cacheItem
            ->setEntityHash($map['entity_hash'])
            ->setName($map['name'])
            ->setType($map['type'])
            ->setOriginalValue($map['original_value'])
        ;
        return $cacheItem;
    }
}
