<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Hydrator;

use ParadiseSecurity\Component\DataSentry\Request\RequestInterface;
use ParagonIE\CipherSweet\Constants;
use ParagonIE\CipherSweet\TypeEncodingTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

use function get_debug_type;
use function in_array;
use function is_string;

class PropertyHydrator implements PropertyHydratorInterface
{
    use TypeEncodingTrait;

    private PropertyAccessorInterface $propertyAccessor;

    public const ALLOWED_TYPE_CASTS = [
        Constants::TYPE_BOOLEAN,
        Constants::TYPE_FLOAT,
        Constants::TYPE_INT,
    ];

    public function __construct(PropertyAccessorInterface $propertyAccessor = null)
    {
        $this->propertyAccessor = $propertyAccessor ?? PropertyAccess::createPropertyAccessor();
    }

    public function extract(RequestInterface $request): ?string
    {
        $value = $request->getPropertyValue();

        if (is_string($value)) {
            return $value;
        }

        $entity = $request->getEntity();
        $propertyName = $request->getMappedTypedProperty();

        $value = $this->propertyAccessor->getValue($entity, $propertyName);

        $type = get_debug_type($value);

        if (!in_array($type, self::ALLOWED_TYPE_CASTS)) {
            return null;
        }

        $defaultValue = $this->getDefaultValueForType($value, $type);

        $this->propertyAccessor->setValue($entity, $propertyName, $defaultValue);

        return $this->convertToString($value, $type);
    }

    public function hydrate(RequestInterface $request): void
    {
        $value = $request->getPropertyValue();

        $entity = $request->getEntity();
        $propertyName = $request->getMappedTypedProperty();

        $originalValue = $this->propertyAccessor->getValue($entity, $propertyName);

        $type = get_debug_type($originalValue);

        if (!in_array($type, self::ALLOWED_TYPE_CASTS)) {
            return;
        }

        $value = $this->convertFromString($value, $type);

        $this->propertyAccessor->setValue($entity, $propertyName, $value);
    }

    private function getDefaultValueForType(int|string|float|bool $value, string $type): int|string|float|bool
    {
        switch ($type) {
            case Constants::TYPE_BOOLEAN:
                $defaultValue = $this->convertToString((bool) $value, $type);
                break;
            case Constants::TYPE_FLOAT:
                $defaultValue = $this->convertToString((float) 0.00, $type);
                break;
            case Constants::TYPE_INT:
                $defaultValue = $this->convertToString((int) 0, $type);
                break;
            default:
                $defaultValue = (string) $value;
                break;
        }

        return $this->convertFromString($defaultValue, $type);
    }
}
