<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Test\Cache\Symfony;

use PHPUnit\Framework\TestCase;
use ParadiseSecurity\Component\DataSentry\Cache\CacheAdapterInterface;
use ParadiseSecurity\Component\DataSentry\Cache\Symfony\SymfonyCachePoolAdapter;
use ParadiseSecurity\Component\DataSentry\Test\UtilityTrait;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

final class SymfonyCachePoolAdapterTest extends TestCase
{
    use UtilityTrait;

    public function testClassDefaultsToSymfonyArrayAdapter()
    {
        $cachePool = $this->createSymfonyCachePoolAdapter();
        $this->assertCacheIsInstanceOfArrayAdapter($cachePool);
    }

    public function testClassAcceptsCustomSymfonyArrayAdapter()
    {
        /** @var ArrayAdapter $cache */
        $cache = $this->createCachePool();
        $cachePool = $this->createSymfonyCachePoolAdapter($cache);
        $this->assertCacheIsInstanceOfArrayAdapter($cachePool);
    }

    private function assertCacheIsInstanceOfArrayAdapter(CacheAdapterInterface $cachePool)
    {
        $this->assertPrivatePropertyIsInstanceOf(
            $cachePool,
            SymfonyCachePoolAdapter::class,
            'cache',
            ArrayAdapter::class,
        );
    }

    private function createSymfonyCachePoolAdapter(CacheItemPoolInterface $cache = null): CacheAdapterInterface
    {
        return new SymfonyCachePoolAdapter($cache);
    }

    private function createCachePool(int $defaultLifetime = 0): CacheItemPoolInterface
    {
        return new ArrayAdapter($defaultLifetime);
    }
}
