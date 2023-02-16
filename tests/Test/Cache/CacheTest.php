<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Test\Cache;

use PHPUnit\Framework\TestCase;
use ParadiseSecurity\Component\DataSentry\Cache\Cache;
use ParadiseSecurity\Component\DataSentry\Cache\CacheAdapterInterface;
use ParadiseSecurity\Component\DataSentry\Cache\CacheInterface;
use ParadiseSecurity\Component\DataSentry\Cache\CacheableItemInterface;
use ParadiseSecurity\Component\DataSentry\Cache\Symfony\SymfonyCachePoolAdapter;
use ParadiseSecurity\Component\DataSentry\Test\Model\FakeModel;
use ParadiseSecurity\Component\DataSentry\Test\SimpleEncryptionTrait;
use ParadiseSecurity\Component\DataSentry\Test\UtilityTrait;

use function spl_object_hash;

final class CacheTest extends TestCase
{
    use SimpleEncryptionTrait;
    use UtilityTrait;

    public function testClassDefaultsToSymfonyCachePoolAdapter()
    {
        $cache = $this->createCache();
        $this->assertCacheAdapterIsInstanceOfCacheAdapter($cache);
    }

    public function testClassAcceptsCustomSymfonyCachePoolAdapter()
    {
        /** @var SymfonyCachePoolAdapter $cacheAdapter */
        $cacheAdapter = $this->createSymfonyCachePoolAdapter();
        $cache = $this->createCache($cacheAdapter);
        $this->assertCacheAdapterIsInstanceOfCacheAdapter($cache);
    }

    public function testCacheCanCreateCacheableItem()
    {
        $name = 'Frank';

        $fake = $this->createFakeModel($name);
        $cache = $this->createCache();

        $config = [
            'entity_hash' => spl_object_hash($fake),
            'name' => 'name',
            'type' => CacheableItemInterface::DECRYPTED_PROPERTY_TYPE,
            'original_value' => $name,
        ];

        $item = $cache->createCachedPropertyItem($config);

        $this->assertInstanceOf(CacheableItemInterface::class, $item);
        $this->assertEquals($name, $item->getOriginalValue());
        $this->assertEquals('name', $item->getName());
        $this->assertEquals(spl_object_hash($fake), $item->getEntityHash());
        $this->assertEquals(CacheableItemInterface::DECRYPTED_PROPERTY_TYPE, $item->getType());

        $encrypted = $this->encryptOriginalValue($name);
        $config['transformed_value'] = $encrypted;
        $item->setTransformedValue($encrypted);

        $this->assertEquals($encrypted, $item->getTransformedValue());

        $value = $this->decryptEncryptedValue($encrypted);

        $this->assertSame($value, $item->getOriginalValue());
    }

    public function testIfCacheCanBeUsedSucessfully()
    {
        $name = 'Jimmy';

        $encrypted = $this->encryptOriginalValue($name);

        $fake = $this->createFakeModel($encrypted);
        $cache = $this->createCache();

        $config = [
            'entity_hash' => spl_object_hash($fake),
            'name' => 'name',
            'type' => CacheableItemInterface::ENCRYPTED_PROPERTY_TYPE,
            'original_value' => $encrypted,
        ];

        $item = $cache->createCachedPropertyItem($config);

        $cache->storeUnCachedItem($config, $item);

        $this->assertTrue($cache->isItemCached($config));

        $cachedItem = $cache->getCachedItem($config);

        $this->assertEqualsCanonicalizing($item, $cachedItem);

        $originalValue = $cachedItem->getOriginalValue();

        $value = $this->decryptEncryptedValue($originalValue);

        $this->assertSame($name, $value);
    }

    private function assertCacheAdapterIsInstanceOfCacheAdapter(CacheInterface $cache): void
    {
        $this->assertPrivatePropertyIsInstanceOf(
            $cache,
            Cache::class,
            'cacheAdapter',
            CacheAdapterInterface::class,
        );
    }

    private function createCache(CacheAdapterInterface $cacheAdapter = null): CacheInterface
    {
        return new Cache($cacheAdapter);
    }

    private function createSymfonyCachePoolAdapter(): CacheAdapterInterface
    {
        return new SymfonyCachePoolAdapter();
    }

    private function createFakeModel(string $name = null): object
    {
        $fake = new FakeModel();
        $fake->setName($name);
        return $fake;
    }
}
