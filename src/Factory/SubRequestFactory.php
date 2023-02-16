<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Factory;

use ParadiseSecurity\Component\DataSentry\Model\EncryptableInterface;
use ParadiseSecurity\Component\DataSentry\Request\RequestInterface;
use ParadiseSecurity\Component\DataSentry\Request\SubRequest;
use ParadiseSecurity\Component\DataSentry\Request\SubRequestInterface;
use ReflectionProperty;

final class SubRequestFactory implements SubRequestFactoryInterface
{
    public function __construct()
    {
    }

    public function createWithRequest(
        EncryptableInterface $entity,
        RequestInterface $mainRequest,
        ReflectionProperty $reflectionProperty,
        array $attributes,
    ): SubRequestInterface {
        $request = $this->createNewSubRequest();

        $request
            ->setEntity($entity)
            ->setReflectionProperty($reflectionProperty)
        ;

        foreach ($attributes as $attribute) {
            $request->addAttribute($attribute);
        }

        $mainRequest->addSubRequest($request);

        return $request;
    }

    private function createNewSubRequest(): SubRequestInterface
    {
        return new SubRequest();
    }
}
