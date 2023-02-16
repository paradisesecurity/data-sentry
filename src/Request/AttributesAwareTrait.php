<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Request;

use Doctrine\Common\Collections\Collection;
use ParadiseSecurity\Component\DataSentry\Attribute\AttributeInterface;

trait AttributesAwareTrait
{
    /**
     * @var Collection|AttributeInterface[]
     */
    protected Collection $attributes;

    public function hasAttributes(): bool
    {
        return $this->attributes->isEmpty() === false;
    }

    public function getAttributes(): Collection
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes): void
    {
        foreach ($attributes as $attribute) {
            if (!($attribute instanceof AttributeInterface)) {
                continue;
            }

            $this->addAttribute($attribute);
        }
    }

    public function addAttribute(AttributeInterface $attribute): void
    {
        if (!$this->hasAttribute($attribute)) {
            $this->attributes->add($attribute);
        }
    }

    public function removeAttribute(AttributeInterface $attribute): void
    {
        if ($this->hasAttribute($attribute)) {
            $this->attributes->removeElement($attribute);
        }
    }

    public function hasAttribute(AttributeInterface $attribute): bool
    {
        return $this->attributes->contains($attribute);
    }

    public function getAttributesByClassName(string $className): array
    {
        $attributes = [];

        foreach ($this->attributes->toArray() as $attribute) {
            if ($attribute instanceof $className) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    public function getAttributeByClassName(string $className): ?AttributeInterface
    {
        $attributes = $this->getAttributesByClassName($className);

        if (empty($attributes)) {
            return null;
        }

        if (count($attributes) === 1) {
            return $attributes[0];
        }

        return null;
    }
}
