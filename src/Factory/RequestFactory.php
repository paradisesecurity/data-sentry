<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Factory;

use ParadiseSecurity\Component\DataSentry\Attribute\AttributeInterface;
use ParadiseSecurity\Component\DataSentry\Model\EncryptableInterface;
use ParadiseSecurity\Component\DataSentry\Request\DecryptionRequest;
use ParadiseSecurity\Component\DataSentry\Request\EncryptionRequest;
use ParadiseSecurity\Component\DataSentry\Request\RequestInterface;
use ParadiseSecurity\Component\DataSentry\Resolver\EncryptorResolverInterface;
use ReflectionProperty;
use ParadiseSecurity\Component\DataSentry\Exception\UnresolvedEncryptorException;

final class RequestFactory implements RequestFactoryInterface
{
    public function __construct(private EncryptorResolverInterface $resolver)
    {
    }

    public function createForReflectionProperty(
        EncryptableInterface $entity,
        ReflectionProperty $reflectionProperty,
        string $requestType,
        array $attributes,
    ): RequestInterface {
        $request = $this->createNewRequest($requestType);

        $request
            ->setEntity($entity)
            ->setReflectionProperty($reflectionProperty)
        ;

        foreach ($attributes as $attribute) {
            $request->addAttribute($attribute);
        }

        $this->resolveEncryptors($request);

        return $request;
    }

    public function resolveEncryptors(RequestInterface $request): void
    {
        if ($request->doMigrateEncryption()) {
            $className = AttributeInterface::MIGRATE_ENCRYPTION_ATTRIBUTE;

            $attribute = $request->getAttributeByClassName($className);

            $encrypted = $attribute->migration;

            $this->processEncryptedAttribute($request, RequestInterface::PREVIOUS_ENCRYPTOR_TYPE, $encrypted);
        }

        $className = AttributeInterface::ENCRYPTED_ATTRIBUTE;

        $attribute = $request->getAttributeByClassName($className);

        $this->processEncryptedAttribute($request, RequestInterface::MAIN_ENCRYPTOR_TYPE, $attribute);
    }

    public function processEncryptedAttribute(RequestInterface $request, string $type, ?AttributeInterface $attribute): void
    {
        if (is_null($attribute)) {
            return;
        };

        if (!isset($attribute->encryptor)) {
            return;
        }

        $name = $attribute->encryptor;

        try {
            $encryptor = $this->resolver->resolve($name);
        } catch (UnresolvedEncryptorException $exception) {
            return;
        }

        $request->addEncryptor($encryptor, $name, $type);
    }

    private function createNewRequest(string $type): RequestInterface
    {
        $method = sprintf('create%sRequest', ucfirst(strtolower($type)));

        if (!is_callable([$this, $method], true)) {
            throw new \BadMethodCallException(sprintf('The required method `%s` does not exist for %s', $type, get_class($this)));
        }

        return $this->$method();
    }

    private function createEncryptionRequest(): RequestInterface
    {
        return new EncryptionRequest();
    }

    private function createDecryptionRequest(): RequestInterface
    {
        return new DecryptionRequest();
    }
}
