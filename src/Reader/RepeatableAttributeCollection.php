<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Reader;

use ArrayObject;

/**
 * @template-extends ArrayObject<int, T>
 * @template T of Annotation
 */
final class RepeatableAttributeCollection extends ArrayObject
{
}
