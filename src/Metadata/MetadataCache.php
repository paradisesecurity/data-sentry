<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Metadata;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class MetadataCache implements MetadataCacheInterface
{
    /**
     * @var Collection|EntityMetadataInterface[]
     */
    private Collection $cache;

    public function __construct()
    {
        $this->cache = new ArrayCollection();
    }

    public function add(EntityMetadataInterface $metadata)
    {
        $this->addMetadata($metadata);
    }

    public function has(string $hash): bool
    {
        foreach ($this->cache->toArray() as $metadata) {
            if ($hash === $metadata->getHash()) {
                return true;
            }
        }

        return false;
    }

    public function clear(): void
    {
        $this->cache->clear();
    }

    public function remove(string $hash): void
    {
        foreach ($this->cache->toArray() as $metadata) {
            if ($hash === $metadata->getHash()) {
                $this->removeMetadata($metadata);
            }
        }
    }

    public function find(string $hash): ?EntityMetadataInterface
    {
        foreach ($this->cache->toArray() as $metadata) {
            if ($hash === $metadata->getHash()) {
                return $metadata;
            }
        }

        return null;
    }

    public function all(): Collection
    {
        return $this->cache;
    }

    private function addMetadata(EntityMetadataInterface $metadata): void
    {
        if (!$this->hasMetadata($metadata)) {
            $this->cache->add($metadata);
        }
    }

    private function removeMetadata(EntityMetadataInterface $metadata): void
    {
        if ($this->hasMetadata($metadata)) {
            $this->cache->removeElement($metadata);
        }
    }

    private function hasMetadata(EntityMetadataInterface $metadata): bool
    {
        return $this->cache->contains($metadata);
    }
}
