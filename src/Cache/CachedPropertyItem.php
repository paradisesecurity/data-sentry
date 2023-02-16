<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Cache;

class CachedPropertyItem implements CacheableItemInterface
{
    use CachedKeyIdentifierTrait;

    protected ?string $entityHash = null;

    protected ?string $name = null;

    protected ?string $type = null;

    protected ?string $originalValue = null;

    protected ?string $transformedValue = null;

    protected array $extraData = [];

    public function __construct()
    {
    }

    public function getEntityHash(): string
    {
        return $this->entityHash;
    }

    public function setEntityHash(string $entityHash): self
    {
        $this->entityHash = $entityHash;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOriginalValue(): ?string
    {
        return $this->originalValue;
    }

    public function setOriginalValue(?string $originalValue): self
    {
        $this->originalValue = $originalValue;

        return $this;
    }

    public function getTransformedValue(): ?string
    {
        return $this->transformedValue;
    }

    public function setTransformedValue(?string $transformedValue): self
    {
        $this->transformedValue = $transformedValue;

        return $this;
    }

    public function getExtraData(): array
    {
        return $this->extraData;
    }

    public function setExtraData(array $extraData): self
    {
        $this->extraData = $extraData;

        return $this;
    }

    public function hasData(string $key): bool
    {
        return isset($this->extraData[$key]);
    }

    public function getData(string $key): mixed
    {
        if ($this->hasData($key)) {
            return $this->extraData[$key];
        }

        return null;
    }

    public function addData(string $key, mixed $value): self
    {
        $this->extraData[$key] = $value;

        return $this;
    }

    public function getCachedKeyIdentifier(): string
    {
        return $this->createCachedKeyIdentifier(
            $this->entityHash,
            $this->name,
            $this->type,
            $this->originalValue
        );
    }
}
