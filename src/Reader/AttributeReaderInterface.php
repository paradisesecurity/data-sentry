<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Reader;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

interface AttributeReaderInterface
{
    public function getClassAttributes(ReflectionClass $class): array;

    public function getMethodAttributes(ReflectionMethod $method): array;

    public function getPropertyAttributes(ReflectionProperty $property): array;

    public function getPropertyAttribute(ReflectionProperty $property, $attributeName);

    public function getPropertyAttributeCollection(ReflectionProperty $property, string $attributeName): RepeatableAttributeCollection;
}
