<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Encryptor;

use ParadiseSecurity\Component\DataSentry\Cache\Cache;
use ParadiseSecurity\Component\DataSentry\Cache\CacheInterface;
use ParadiseSecurity\Component\DataSentry\Cache\CacheableItemInterface;
use ParadiseSecurity\Component\DataSentry\Request\RequestInterface;
use ParadiseSecurity\Component\DataSentry\Response\DecryptedResponse;
use ParadiseSecurity\Component\DataSentry\Response\EncryptedResponse;
use ParadiseSecurity\Component\DataSentry\Response\ResponseInterface;

use function get_class;
use function is_callable;
use function is_string;
use function sprintf;
use function strtolower;
use function ucfirst;

class Encryptor implements EncryptorInterface
{
    private CacheInterface $cache;

    public function __construct(
        private EncryptorAdapterInterface $encryptorAdapter,
        CacheInterface $cache = null,
    ) {
        if (is_null($cache)) {
            $cache = new Cache();
        }
        $this->cache = $cache;
    }

    public function getEncryptorName(): string
    {
        return $this->encryptorAdapter->getName();
    }

    public function isEncrypted(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return $this->encryptorAdapter->isEncrypted($value);
    }

    public function isItemCached(array $map): bool
    {
        return $this->cache->isItemCached($map);
    }

    public function getOriginalPropertyValueOfCachedItem(array $map): string
    {
        $cacheItem = $this->cache->getCachedItem($map);

        return $cacheItem->getOriginalValue();
    }

    public function encrypt(RequestInterface $request): ResponseInterface
    {
        $map = $request->getPropertyMapping();
        $item = $this->cache->getCachedItem($map);

        $transformedValue = $item->getTransformedValue();

        if ($this->isEncrypted($transformedValue)) {
            return $this->createResponse(ResponseInterface::ENCRYPTED_RESPONSE_TYPE, $item, $request);
        }

        return $this->doEncrypt($request, $item);
    }

    public function decrypt(RequestInterface $request): ResponseInterface
    {
        $map = $request->getPropertyMapping();
        $item = $this->cache->getCachedItem($map);

        $transformedValue = $item->getTransformedValue();

        if (is_string($transformedValue)) {
            return $this->createResponse(ResponseInterface::DECRYPTED_RESPONSE_TYPE, $item, $request);
        }

        return $this->doDecrypt($request, $item);
    }

    private function doEncrypt(
        RequestInterface $request,
        CacheableItemInterface $item,
    ): ResponseInterface {
        try {
            $data = $this->encryptorAdapter->handleEncryption($request, $item);

            $item->setTransformedValue($data['property_value']);
            unset($data['property_value']);
            $item->setExtraData($data);

            $map = $request->getPropertyMapping();
            $this->cache->storeUnCachedItem($map, $item);
        } catch (\Exception $e) {
            /*
            $this->logger->critical(
                sprintf('An error occurred while encrypting field `%s` for entity `%s`.', $request->getPropertyName(), $request->getEntityClassName()),
                ['exception' => $e, 'message' => $e->getMessage()],
            );
            */
        }

        return $this->createResponse(ResponseInterface::ENCRYPTED_RESPONSE_TYPE, $item, $request);
    }

    private function doDecrypt(
        RequestInterface $request,
        CacheableItemInterface $item,
    ): ResponseInterface {
        try {
            $value = $this->encryptorAdapter->handleDecryption($request, $item);

            $item->setTransformedValue($value);

            $map = $request->getPropertyMapping();
            $this->cache->storeUnCachedItem($map, $item);
        } catch (\Exception $e) {
            /*
            $this->logger->warning(
                sprintf('An error occurred while decrypting field `%s` for entity `%s`.', $request->getFieldName(), $request->getEntityClassName()),
                ['exception' => $e, 'message' => $e->getMessage()],
            );
            */
        }

        return $this->createResponse(ResponseInterface::DECRYPTED_RESPONSE_TYPE, $item, $request);
    }

    private function createResponse(string $type, CacheableItemInterface $item, RequestInterface $request): ResponseInterface
    {
        $method = sprintf('create%sResponse', ucfirst(strtolower($type)));

        if (!is_callable([$this, $method], true)) {
            throw new \BadMethodCallException(sprintf('The required method `%s` does not exist for %s', $type, get_class($this)));
        }

        $response = $this->$method();
        $response
            ->setEntity($request->getEntity())
            ->setReflectionProperty($request->getReflectionProperty())
            ->setValue($item->getTransformedValue())
            ->setExtraData($item->getExtraData())
        ;

        return $response;
    }

    private function createEncryptedResponse(): ResponseInterface
    {
        return new EncryptedResponse();
    }

    private function createDecryptedResponse(): ResponseInterface
    {
        return new DecryptedResponse();
    }
}
