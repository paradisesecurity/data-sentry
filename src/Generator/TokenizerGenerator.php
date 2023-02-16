<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Generator;

class TokenizerGenerator implements GeneratorInterface
{
    /**
     * @param string $string
     * @return array
     */
    public function generate(string $string): array
    {
        $string = trim(preg_replace(array('/[^a-zA-Z0-9\-]/', '/\s+/'), ' ', $string));
        return explode(' ', $string);
    }

    public function getType(): string
    {
        return 'tokenizer';
    }
}
