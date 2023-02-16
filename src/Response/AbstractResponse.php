<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Response;

use ParadiseSecurity\Component\DataSentry\Trait\EntityPropertyTrait;
use ParadiseSecurity\Component\DataSentry\Trait\TypeTrait;

abstract class AbstractResponse implements ResponseInterface
{
    use TypeTrait;
    use EntityPropertyTrait;

    protected mixed $value;

    protected array $extraData = [];

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(mixed $value): self
    {
        $this->value = $value;

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

    public function getOriginalValue(): mixed
    {
        return $this->getPropertyValue();
    }
}
