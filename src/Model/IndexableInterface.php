<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Model;

interface IndexableInterface
{
    public function getId(): ?int;

    public function getName(): string;

    public function setName(string $name): self;

    public function getParent(): EncryptableInterface;

    public function setParent(?EncryptableInterface $parent): self;

    public function getIndexBi(): string;

    public function setIndexBi(string $indexBi): self;
}
