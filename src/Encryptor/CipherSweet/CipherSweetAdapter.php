<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet;

use ParadiseSecurity\Component\DataSentry\Attribute\AttributeInterface;
use ParadiseSecurity\Component\DataSentry\Cache\CacheableItemInterface;
use ParadiseSecurity\Component\DataSentry\Encryptor\AbstractEncryptorAdapter;
use ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet\Resolver\BlindIndexTransformationResolverInterface;
use ParadiseSecurity\Component\DataSentry\Encryptor\EncryptorAdapterInterface;
use ParadiseSecurity\Component\DataSentry\Request\RequestInterface;
use ParadiseSecurity\Component\DataSentry\Request\SubRequestInterface;
use ParagonIE\CipherSweet\BlindIndex;
use ParagonIE\CipherSweet\CipherSweet;
use ParagonIE\CipherSweet\EncryptedField;
use ParagonIE\CipherSweet\KeyRotation\FieldRotator;

use function str_starts_with;

class CipherSweetAdapter extends AbstractEncryptorAdapter
{
    public function __construct(
        private CipherSweet $ciphersweet,
        private BlindIndexTransformationResolverInterface $resolver,
    ) {
        $this->name = EncryptorAdapterInterface::CIPHERSWEET_ADAPTER_NAME;

        parent::__construct();
    }

    public function isEncrypted(string $value): bool
    {
        return str_starts_with($value, $this->getEncryptionMarker());
    }

    public function getEncryptionMarker(): string
    {
        return $this->ciphersweet->getBackend()->getPrefix();
    }

    public function handleEncryption(
        RequestInterface $request,
        CacheableItemInterface $item,
    ): array {
        $encryptedField = $this->getEncryptedFieldWithBlindIndexes($request, $item);

        [$value, $indexes] = $encryptedField->prepareForStorage($item->getOriginalValue());

        return [
            'property_value' => $value,
            'blind_indexes' => $indexes
        ];
    }

    public function handleDecryption(
        RequestInterface $request,
        CacheableItemInterface $item,
    ): string {
        $encryptedField = $this->createNewEncryptedField(
            $request->getEntityShortName(),
            $this->convertCamelCaseToSnakeCase($item->getName()),
        );

        return $encryptedField->decryptValue($item->getOriginalValue());
    }

    protected function handleKeyRotation(
        RequestInterface $request,
        CacheableItemInterface $cacheItem,
    ): array {
        $oldEncryptedField = $request->getPreviousEncryptor()->createNewEncryptedField(
            $request->getEntityShortName(),
            $this->convertCamelCaseToSnakeCase($cacheItem->getFieldName())
        );

        $newEncryptedField = $this->createNewEncryptedField(
            $request->getEntityShortName(),
            $this->convertCamelCaseToSnakeCase($cacheItem->getFieldName())
        );

        $rotator = new FieldRotator($oldEncryptedField, $newEncryptedField);
        if ($rotator->needsReEncrypt(($ciphertext = $cacheItem->getDecryptedFieldValue()))) {
            return $rotator->prepareForUpdate($ciphertext);
        }
    }

    protected function handleBlindIndex(
        RequestInterface $request,
        CacheableItemInterface $cacheItem,
    ): array {
        $encryptedField = $this->getEncryptedFieldWithBlindIndexes($request, $cacheItem);

        return $encryptedField->getAllBlindIndexes($cacheItem->getDecryptedFieldValue());
    }

    private function getEncryptedFieldWithBlindIndexes(
        RequestInterface $request,
        CacheableItemInterface $cacheItem,
    ): EncryptedField {
        $encryptedField = $this->createNewEncryptedField(
            $request->getEntityShortName(),
            $this->convertCamelCaseToSnakeCase($cacheItem->getName())
        );

        if (!$request->doCreateBlindIndex()) {
            return $encryptedField;
        }

        $subRequest = $request->getSubRequest(SubRequestInterface::BLIND_INDEX_SUBREQUEST_TYPE);

        if (is_null($subRequest)) {
            return $encryptedField;
        }

        $reflectionProperty = $subRequest->getReflectionProperty();

        $blindIndexes = $subRequest->getAttributesByClassName(AttributeInterface::BLIND_INDEX_ATTRIBUTE);

        foreach ($blindIndexes as $index) {
            $encryptedField = $encryptedField->addBlindIndex(
                $this->createNewBlindIndex(
                    $this->convertCamelCaseToSnakeCase($reflectionProperty->getName()),
                    $this->resolver->resolve($index->transformations),
                    $index->bloomFilterBits,
                    $index->fastIndexing,
                    $index->hashConfig,
                )
            );
        }

        return $encryptedField;
    }

    private function createNewEncryptedField(string $className, string $fieldName): EncryptedField
    {
        return new EncryptedField($this->ciphersweet, $className, $fieldName);
    }

    private function createNewBlindIndex(
        string $fieldName,
        array $transformations,
        int $filterBits,
        bool $fastHash,
        array $hashConfig,
    ): BlindIndex {
        return new BlindIndex(
            $fieldName,
            $transformations,
            $filterBits,
            $fastHash,
            $hashConfig
        );
    }
}
