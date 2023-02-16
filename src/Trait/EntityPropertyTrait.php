<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Trait;

use ParadiseSecurity\Component\DataSentry\Model\EncryptableInterface;
use ReflectionProperty;

trait EntityPropertyTrait
{
    protected EncryptableInterface $entity;

    protected ReflectionProperty $reflectionProperty;

    public function getEntity(): EncryptableInterface
    {
        return $this->entity;
    }

    public function setEntity(EncryptableInterface $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getReflectionProperty(): ReflectionProperty
    {
        return $this->reflectionProperty;
    }

    public function setReflectionProperty(ReflectionProperty $reflectionProperty): self
    {
        $this->reflectionProperty = $reflectionProperty;

        return $this;
    }

    public function getEntityHash(): string
    {
        return spl_object_hash($this->entity);
    }

    public function getEntityClassName(): string
    {
        return get_class($this->entity);
    }

    public function getEntityShortName(): string
    {
        return $this->reflectionProperty->getDeclaringClass()->getShortName();
    }

    public function getPropertyName(): string
    {
        return $this->reflectionProperty->getName();
    }

    public function getPropertyValue(): mixed
    {
        return $this->reflectionProperty->getValue($this->entity);
    }
}
