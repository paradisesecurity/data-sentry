<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Exception;

use function get_debug_type;
use function sprintf;

/**
 * An AttributeException indicates that something is wrong with the attribute setup.
 */
class AttributeException extends \Exception
{
    /** @param mixed $givenValue */
    public static function invalidType(string $expectdType, $givenValue): self
    {
        return new self(sprintf(
            'Expected %s, but %s was given.',
            $expectdType,
            get_debug_type($givenValue)
        ));
    }
}
