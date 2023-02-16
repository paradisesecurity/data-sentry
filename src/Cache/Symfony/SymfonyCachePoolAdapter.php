<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Cache\Symfony;

use ParadiseSecurity\Component\DataSentry\Cache\AbstractCacheAdapter;
use ParadiseSecurity\Component\DataSentry\Cache\CacheableItemInterface;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Contracts\Cache\CacheInterface;

class SymfonyCachePoolAdapter extends AbstractCacheAdapter
{
    private CacheItemPoolInterface $cache;

    public function __construct(
        CacheInterface $cache = null,
    ) {
        if (is_null($cache)) {
            $cache = new ArrayAdapter();
        }
        $this->cache = $cache;
    }

    public function hasItem(string $key): bool
    {
        return $this->cache->hasItem($key);
    }

    public function save(string $key, CacheableItemInterface $cacheableItem): void
    {
        $cacheItem = $this->getItem($key);

        $cacheItem->set($cacheableItem);

        $this->cache->save($cacheItem);
    }

    public function get(string $key): ?CacheableItemInterface
    {
        $cacheItem = $this->getItem($key);

        if (!$cacheItem->isHit()) {
            return null;
        }

        return $cacheItem->get();
    }

    public function getItem(string $key): CacheItemInterface
    {
        return $this->cache->getItem($key);
    }

    public function getItems(array $keys): iterable
    {
        return $this->cache->getItems($keys);
    }

    public function saveDeferred(CacheItemInterface $cacheItem, CacheableItemInterface $cacheableItem): void
    {
        $cacheItem->set($cacheableItem);

        $this->cache->saveDeferred($cacheItem);
    }

    public function commit(): void
    {
        $this->cache->commit();
    }

    public function deleteItem(string $key): void
    {
        $this->cache->deleteItem($key);
    }

    public function deleteItems(array $keys): void
    {
        $this->cache->deleteItems($keys);
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    public function prune(): void
    {
        if ($this->cache instanceof PruneableInterface) {
            $this->cache->prune();
        }
    }
}
