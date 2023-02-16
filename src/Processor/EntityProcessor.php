<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Processor;

use ParadiseSecurity\Component\DataSentry\Attribute\AttributeInterface;
use ParadiseSecurity\Component\DataSentry\Cache\CachedKeyIdentifierTrait;
use ParadiseSecurity\Component\DataSentry\Encryptor\EncryptorInterface;
use ParadiseSecurity\Component\DataSentry\Factory\RequestFactoryInterface;
use ParadiseSecurity\Component\DataSentry\Factory\SubRequestFactoryInterface;
use ParadiseSecurity\Component\DataSentry\Handler\IndexingHandlerInterface;
use ParadiseSecurity\Component\DataSentry\Hydrator\PropertyHydratorInterface;
use ParadiseSecurity\Component\DataSentry\Metadata\MetadataCache;
use ParadiseSecurity\Component\DataSentry\Metadata\MetadataCacheInterface;
use ParadiseSecurity\Component\DataSentry\Metadata\MetadataCollectorInterface;
use ParadiseSecurity\Component\DataSentry\Model\EncryptableInterface;
use ParadiseSecurity\Component\DataSentry\Request\RequestInterface;
use ParadiseSecurity\Component\DataSentry\Request\RequestStackInterface;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\NameConverter\NameConverterInterface;

use function is_callable;
use function is_null;
use function is_string;
use function spl_object_hash;

class EntityProcessor implements EntityProcessorInterface
{
    use CachedKeyIdentifierTrait;

    protected NameConverterInterface $normalizer;

    protected MetadataCacheInterface $metadata;

    public function __construct(
        private MetadataCollectorInterface $collector,
        private RequestStackInterface $requestStack,
        private RequestFactoryInterface $requestFactory,
        private SubRequestFactoryInterface $subRequestFactory,
        private IndexingHandlerInterface $indexingHandler,
        private PropertyHydratorInterface $propertyHydrator,
    ) {
        $this->normalizer = new CamelCaseToSnakeCaseNameConverter();
        $this->metadata = new MetadataCache();
    }

    /**
     * Handles the encryption / decryption process of an entity through a single method. Returns true if the process completes successfully.
     *
     * @param EncryptableInterface $entity
     * @param string $type
     * @return bool
     */
    public function process(
        EncryptableInterface $entity,
        string $type,
    ): bool {
        $this->buildRequestStack($entity, $type);

        return $this->processProperties();
    }

    /**
     * Handles the encryption process.
     *
     * @param EncryptableInterface $entity
     * @return bool
     */
    public function encrypt(EncryptableInterface $entity): bool
    {
        return $this->process($entity, RequestInterface::ENCRYPTION_REQUEST_TYPE);
    }

    /**
     * Handles the decryption process.
     *
     * @param EncryptableInterface $entity
     * @return bool
     */
    public function decrypt(EncryptableInterface $entity): bool
    {
        return $this->process($entity, RequestInterface::DECRYPTION_REQUEST_TYPE);
    }

    public function cleanup(): void
    {
        $this->metadata->clear();

        $this->requestStack->clear();
    }

    public function metadata(): MetadataCacheInterface
    {
        return $this->metadata;
    }

    /**
     * Builds the request stack of encryption / decryption requests that will be processed.
     *
     * @param EncryptableInterface $entity
     * @param string $requestType
     * @return void
     */
    private function buildRequestStack(
        EncryptableInterface $entity,
        string $requestType,
    ): void {
        $this->requestStack->clear();

        $hash = spl_object_hash($entity);

        if (!$this->metadata->has($hash)) {
            $this->collector->collect($entity, $this->metadata);
        }

        $metadata = $this->metadata->find($hash);

        if (is_null($metadata)) {
            return;
        }

        foreach ($metadata->getProperties() as $name => $property) {
            $attributes = $metadata->getMainPropertyAttributes($name);

            if (empty($attributes)) {
                continue;
            }

            $request = $this->requestFactory->createForReflectionProperty(
                $entity,
                $property,
                $requestType,
                $attributes,
            );

            $this->requestStack->push($request);
        }

        foreach ($metadata->getProperties() as $name => $property) {
            $attributes = $metadata->getSubPropertyAttributes($name);

            if (empty($attributes)) {
                continue;
            }

            $map = $metadata->getMap($name);

            if (is_null($map)) {
                continue;
            }

            $mainRequest = $this->requestStack->find($map);

            if (is_null($mainRequest)) {
                continue;
            }

            $request = $this->subRequestFactory->createWithRequest(
                $entity,
                $mainRequest,
                $property,
                $attributes,
            );
        }
    }

    /**
     * Processes the request stack of entity properties.
     *
     * @return boolean
     */
    private function processProperties(): bool
    {
        if ($this->requestStack->count() === 0) {
            return true;
        }

        while ($this->requestStack->count() > 0) {
            $request = $this->requestStack->pop();

            $value = $this->getStringOrNullPropertyValue($request);

            if (is_null($value)) {
                continue;
            }

            switch ($request->getType()) {
                case RequestInterface::DECRYPTION_REQUEST_TYPE:
                    $value = $this->handleDecryptOperation($request);
                    break;

                default:
                    $value = $this->handleEncryptOperation($request);
                    break;
            }

            if (null !== $value) {
                $this->handleSuccessfulRequest($request, $value);
            }
        }

        return $this->requestStack->count() === 0;
    }

    private function handleSuccessfulRequest(RequestInterface $request, string $value): void
    {
        $request->getReflectionProperty()->setValue($request->getEntity(), $value);

        $encryptor = $request->getEncryptor(RequestInterface::MAIN_ENCRYPTOR_TYPE);

        if (is_null($encryptor)) {
            return;
        }

        if (!$this->isValueEncrypted($encryptor, $value) && $request->doMappedTypedProperty()) {
            $this->propertyHydrator->hydrate($request);

            $request->getReflectionProperty()->setValue($request->getEntity(), null);
        }
    }

    /**
     * Encrypts the plaintext data using the chosen encryptor.
     *
     * @param RequestInterface $request
     * @return mixed|string|null
     *
     * @throws \ParadiseSecurity\Component\CipherSweet\Exception\UndefinedGeneratorException
     * @throws \ReflectionException
     */
    private function handleEncryptOperation(RequestInterface $request): ?string
    {
        $value = $this->getStringOrNullPropertyValue($request);

        if ($request->doForceEncrypt()) {
            return $this->encryptAndReturnEncryptedValue($request);
        }

        $oldValue = $this->getOriginalPropertyValue($request, $value);

        $encryptor = $request->getEncryptor(RequestInterface::MAIN_ENCRYPTOR_TYPE);

        if (!is_null($encryptor)) {
            if ($this->isValueEncrypted($encryptor, $oldValue) && $oldValue === $value) {
                return $oldValue;
            }
        }

        if (null === $oldValue && null === $value) {
            return $oldValue;
        }

        $request->getReflectionProperty()->setValue($request->getEntity(), $oldValue);

        return $this->encryptAndReturnEncryptedValue($request);
    }

    /**
     * Decrypts the encrypted data using the chosen encryptor.
     *
     * @param RequestInterface $request
     * @return string
     */
    private function handleDecryptOperation(RequestInterface $request): ?string
    {
        $value = $request->getPropertyValue();

        $value = $this->getOriginalPropertyValue($request, $value);

        $encryptor = $request->getEncryptor(RequestInterface::MAIN_ENCRYPTOR_TYPE);

        if (!is_null($encryptor)) {
            if ($this->isValueEncrypted($encryptor, $value)) {
                $response = $encryptor->decrypt($request);
                return $response->getValue();
            }
        }

        if (!$request->doMigrateEncryption()) {
            return $value;
        }

        $previousEncryptor = $request->getEncryptor(RequestInterface::PREVIOUS_ENCRYPTOR_TYPE);

        if (!is_null($previousEncryptor)) {
            if ($this->isValueEncrypted($previousEncryptor, $value)) {
                $response = $previousEncryptor->decrypt($request);
                return $response->getValue();
            }
        }

        return $value;
    }

    /**
     * If the property is mapped to a non string value, this will convert that value into a string.
     *
     * @param RequestInterface $request
     * @return string|null
     */
    private function getStringOrNullPropertyValue(RequestInterface $request): ?string
    {
        if (!$request->doMappedTypedProperty()) {
            return $request->getPropertyValue();
        }

        $value = $this->propertyHydrator->extract($request);

        if (is_string($value)) {
            $request->getReflectionProperty()->setValue($request->getEntity(), $value);
        }

        return $value;
    }

    /**
     * Gets the original decrypted value of the data, either from cache or through a new decryption request.
     *
     * @param RequestInterface $request
     * @param string|null $value
     * @return string|null
     */
    private function getOriginalPropertyValue(RequestInterface $request, ?string $value): ?string
    {
        if (!is_string($value)) {
            return $value;
        }

        $map = $request->getPropertyMapping();

        $encryptor = $request->getEncryptor(RequestInterface::MAIN_ENCRYPTOR_TYPE);

        if (is_null($encryptor)) {
            return $value;
        }

        if ($encryptor->isItemCached($map)) {
            $value = $encryptor->getOriginalPropertyValueOfCachedItem($map);
        }

        return $value;
    }

    /**
     * Returns encrypted data and stores the indexes in the entity.
     *
     * @param RequestInterface $request
     * @return string
     */
    private function encryptAndReturnEncryptedValue(RequestInterface $request): ?string
    {
        $value = $this->storeValue($request);

        $this->storeIndexes($request);

        return $value;
    }

    /**
     * Returns encrypted data from the chosen encryptor and stores the blind index in the entity.
     *
     * @param RequestInterface $request
     * @return string
     */
    private function storeValue(RequestInterface $request): ?string
    {
        if (($value = $request->getPropertyValue()) === '') {
            return '';
        }

        $encryptor = $request->getEncryptor(RequestInterface::MAIN_ENCRYPTOR_TYPE);

        if (is_null($encryptor)) {
            return $value;
        }

        $response = $encryptor->encrypt($request);

        $data = $response->getExtraData();

        if ($request->doCreateBlindIndex() === true && isset($data['blind_indexes'])) {
            foreach ($data['blind_indexes'] as $key => $blindIndexValue) {
                $setter = $this->convertSnakeCaseToCamelCase(sprintf('set_%s', $key));
                if (is_callable([$response->getEntity(), $setter])) {
                    $response->getEntity()->$setter($blindIndexValue);
                }
            }
        }

        return $response->getValue();
    }

    /**
     * Checks if a given value is encrypted using the chosen encryptor.
     *
     * @param EncryptorInterface $encryptor
     * @param null|string $value
     * @return bool
     */
    private function isValueEncrypted(EncryptorInterface $encryptor, ?string $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return $encryptor->isEncrypted($value);
    }

    /**
     * @param RequestInterface $request
     *
     * @throws \ParadiseSecurity\Component\CipherSweet\Exception\UndefinedGeneratorException
     * @throws \ReflectionException
     */
    private function storeIndexes(RequestInterface $request): void
    {
        $attribute = $request->getAttributeByClassName(AttributeInterface::INDEXED_ATTRIBUTE);

        if ($attribute === null) {
            return;
        }

        $autoRefresh = $attribute->autoRefresh ?? false;
        if ($autoRefresh === false) {
            return;
        }

        if (is_string($request->getPropertyValue()) === false) {
            throw new \TypeError("Value is supposed to be of type string in order to build related indexes.");
        }

        // TODO!
        // $this->indexingHandler->handle($request);
    }

    private function convertSnakeCaseToCamelCase(string $string): string
    {
        return $this->normalizer->denormalize($string);
    }

    private function convertCamelCaseToSnakeCase(string $string): string
    {
        return $this->normalizer->normalize($string);
    }
}
