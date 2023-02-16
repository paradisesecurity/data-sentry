<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Trait;

trait TypeTrait
{
    protected string $type;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }
}
