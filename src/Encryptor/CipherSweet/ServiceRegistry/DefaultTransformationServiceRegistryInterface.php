<?php

declare(strict_types=1);

namespace ParadiseSecurity\Component\DataSentry\Encryptor\CipherSweet\ServiceRegistry;

use ParagonIE\CipherSweet\Transformation\AlphaCharactersOnly;
use ParagonIE\CipherSweet\Transformation\AlphaNumericCharactersOnly;
use ParagonIE\CipherSweet\Transformation\Compound;
use ParagonIE\CipherSweet\Transformation\DigitsOnly;
use ParagonIE\CipherSweet\Transformation\FirstCharacter;
use ParagonIE\CipherSweet\Transformation\LastFourDigits;
use ParagonIE\CipherSweet\Transformation\Lowercase;

interface DefaultTransformationServiceRegistryInterface
{
    public const ALPHA_CHARACTERS_ONLY = AlphaCharactersOnly::class;

    public const ALPHA_NUMERIC_CHARACTERS_ONLY = AlphaNumericCharactersOnly::class;

    public const COMPOUND = Compound::class;

    public const DIGITS_ONLY = DigitsOnly::class;

    public const FIRST_CHARACTER = FirstCharacter::class;

    public const LAST_FOUR_DIGITS = LastFourDigits::class;

    public const LOWERCASE = Lowercase::class;

    public const DEFAULT_TRANSFORMATIONS = [
        'alpha_characters_only' => self::ALPHA_CHARACTERS_ONLY, 'alpha_numeric_characters_only' => self::ALPHA_NUMERIC_CHARACTERS_ONLY,
        'compound' => self::COMPOUND,
        'digits_only' => self::DIGITS_ONLY,
        'first_character' => self::FIRST_CHARACTER,
        'last_four_digits' => self::LAST_FOUR_DIGITS,
        'lowercase' => self::LOWERCASE,
    ];
}
