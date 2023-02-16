<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Generator;

class ValueEndingByGenerator implements GeneratorInterface
{
    /**
     * @param string $value
     * @return array
     */
    public function generate(string $value): array
    {
        $possibleValues = [];

        for ($i=1, $len = mb_strlen($value); $i <= $len; $i++) {
            $possibleValues[] = mb_substr($value, -$i);
        }

        return $possibleValues;
    }

    public function getType(): string
    {
        return 'value_ending_by';
    }
}
