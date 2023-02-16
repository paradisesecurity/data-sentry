<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Test;

use ReflectionClass;
use ReflectionProperty;

trait UtilityTrait
{
    protected function assertPrivatePropertyIsInstanceOf(
        object $entity,
        string $className,
        string $propertyName,
        string $interfaceClassName,
    ): void {
        $property = $this->getPrivateProperty($className, $propertyName);
        $this->assertInstanceOf($interfaceClassName, $property->getValue($entity));
    }

    protected function getPrivateProperty(
        string $className,
        string $propertyName,
    ): ReflectionProperty {
        $reflector = new ReflectionClass($className);
        $property = $reflector->getProperty($propertyName);
        $property->setAccessible(true);
        return $property;
    }
}
