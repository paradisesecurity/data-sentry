<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Request;

use Doctrine\Common\Collections\ArrayCollection;
use ParadiseSecurity\Component\DataSentry\Trait\EntityPropertyTrait;
use ParadiseSecurity\Component\DataSentry\Trait\TypeTrait;
use ParadiseSecurity\Component\DataSentry\Attribute\AttributeInterface;

abstract class AbstractSubRequest implements SubRequestInterface
{
    use TypeTrait;
    use EntityPropertyTrait;
    use AttributesAwareTrait {
        addAttribute as traitAddAttribute;
    }

    protected ?RequestInterface $parent;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

    public function __clone()
    {
        $this->attributes = new ArrayCollection();
    }

    public function getParent(): ?RequestInterface
    {
        return $this->parent;
    }

    public function setParent(?RequestInterface $parent): void
    {
        $this->parent = $parent;
    }

    public function addAttribute(AttributeInterface $attribute): void
    {
        $this->processAttribute($attribute);

        $this->traitAddAttribute($attribute);
    }

    protected function processAttribute(AttributeInterface $attribute): void
    {
        $class = get_class($attribute);

        if ($class === AttributeInterface::BLIND_INDEX_ATTRIBUTE) {
            $this->type = SubRequestInterface::BLIND_INDEX_SUBREQUEST_TYPE;
        }
    }
}
