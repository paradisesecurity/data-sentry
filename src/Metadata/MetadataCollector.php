<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Metadata;

use ParadiseSecurity\Component\DataSentry\Attribute\AttributeInterface;
use ParadiseSecurity\Component\DataSentry\Model\EncryptableInterface;
use ParadiseSecurity\Component\DataSentry\Reader\AttributeReaderInterface;
use ReflectionClass;

use function get_class;
use function in_array;
use function spl_object_hash;

class MetadataCollector implements MetadataCollectorInterface
{
    public function __construct(private AttributeReaderInterface $reader)
    {
    }

    public function collect(EncryptableInterface $entity, MetadataCacheInterface $cache): void
    {
        $className = get_class($entity);

        $properties = $this->collectReflectionProperties($className);
        if (empty($properties)) {
            return;
        }

        $mainTypes = AttributeInterface::REQUEST_TYPES;
        $mainAttributes = $this->collectPropertyAttributes($properties, $mainTypes);
        if (empty($mainAttributes)) {
            return;
        }

        $subTypes = AttributeInterface::SUBREQUEST_TYPES;
        $subAttributes = $this->collectPropertyAttributes($properties, $subTypes);
        $map = $this->generateAttributesMapping($subAttributes);

        $hash = spl_object_hash($entity);

        $metadata = new EntityMetadata();
        $metadata->setHash($hash);
        $metadata->setProperties($properties);
        $metadata->setMainAttributes($mainAttributes);
        $metadata->setSubAttributes($subAttributes);
        $metadata->setMapping($map);

        $cache->add($metadata);
    }

    private function collectReflectionProperties(string $className): array
    {
        $class = new ReflectionClass($className);

        $properties = [];

        foreach ($class->getProperties() as $property) {
            $property->setAccessible(true);

            $properties[$property->getName()] = $property;
        }

        return $properties;
    }

    private function collectPropertyAttributes(array $properties, array $types): array
    {
        $collection = [];

        foreach ($properties as $property) {
            $attributes = $this->reader->getPropertyAttributes($property);
            $attributes = $this->collectAttributes($attributes, $types);

            if (empty($attributes)) {
                continue;
            }

            $collection[$property->getName()] = $attributes;
        }

        return $collection;
    }

    private function collectAttributes(array $attributes, array $allowed): array
    {
        if (empty($attributes)) {
            return $attributes;
        }

        $list = [];

        foreach ($attributes as $attribute) {
            $className = get_class($attribute);

            if (in_array($className, $allowed)) {
                $list[] = $attribute;
            }
        }

        return $list;
    }

    private function generateAttributesMapping(array $collection): array
    {
        if (empty($collection)) {
            return $collection;
        }

        $map = [];

        foreach ($collection as $name => $attributes) {
            foreach ($attributes as $attribute) {
                if (isset($attribute->indexOf)) {
                    $map[$name] = $attribute->indexOf;
                }
            }
        }

        return $map;
    }
}
