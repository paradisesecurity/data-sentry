<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Model;

trait IndexedEntityTrait
{
    protected int $id;

    protected string $name;

    protected EncryptableInterface $parent;

    protected string $indexBi;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getParent(): EncryptableInterface
    {
        return $this->parent;
    }

    public function setParent(?EncryptableInterface $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getIndexBi(): string
    {
        return $this->indexBi;
    }

    public function setIndexBi(string $indexBi): self
    {
        $this->indexBi = $indexBi;

        return $this;
    }
}
