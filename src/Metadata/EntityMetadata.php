<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Metadata;

class EntityMetadata implements EntityMetadataInterface
{
    private string $hash;

    private array $properties = [];

    private array $mainAttributes = [];

    private array $subAttributes = [];

    private array $mapping = [];

    public function getHash(): string
    {
        return $this->hash;
    }

    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }

    public function getProperties(): array
    {
        return $this->properties;
    }

    public function setProperties(array $properties): void
    {
        $this->properties = $properties;
    }

    public function getMainAttributes(): array
    {
        return $this->mainAttributes;
    }

    public function setMainAttributes(array $mainAttributes): void
    {
        $this->mainAttributes = $mainAttributes;
    }

    public function getSubAttributes(): array
    {
        return $this->subAttributes;
    }

    public function setSubAttributes(array $subAttributes): void
    {
        $this->subAttributes = $subAttributes;
    }

    public function getMapping(): array
    {
        return $this->mapping;
    }

    public function setMapping(array $mapping): void
    {
        $this->mapping = $mapping;
    }

    public function getMap(string $name): ?string
    {
        if (isset($this->mapping[$name])) {
            return $this->mapping[$name];
        }

        return null;
    }

    public function getMainPropertyAttributes(string $name): array
    {
        return $this->getPropertyAttributes($name, $this->mainAttributes);
    }

    public function getSubPropertyAttributes(string $name): array
    {
        return $this->getPropertyAttributes($name, $this->subAttributes);
    }

    private function getPropertyAttributes(string $name, array $attributes): array
    {
        $collection = [];

        foreach ($attributes as $propertyName => $propertyAttributes) {
            if ($propertyName === $name) {
                return $propertyAttributes;
            }
        };

        return $collection;
    }
}
