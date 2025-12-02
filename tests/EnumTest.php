<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\PhoneNumber\PhoneNumberFormat;
use Cline\PhoneNumber\PhoneNumberParseErrorType;
use Cline\PhoneNumber\PhoneNumberType;
use libphonenumber\NumberParseException;

/**
 * @param  class-string<BackedEnum>  $enumClass
 * @return array<string, int|string>
 */
function enumToMap(string $enumClass): array
{
    $values = [];

    foreach ($enumClass::cases() as $enum) {
        $values[$enum->name] = $enum->value;
    }

    return $values;
}

describe('Enum synchronization with libphonenumber', function (): void {
    it('PhoneNumberFormat enum matches libphonenumber', function (): void {
        expect(enumToMap(libphonenumber\PhoneNumberFormat::class))
            ->toBe(enumToMap(PhoneNumberFormat::class));
    });

    it('PhoneNumberType enum matches libphonenumber', function (): void {
        expect(enumToMap(libphonenumber\PhoneNumberType::class))
            ->toBe(enumToMap(PhoneNumberType::class));
    });

    it('PhoneNumberParseErrorType enum matches libphonenumber constants', function (): void {
        $expected = new ReflectionClass(NumberParseException::class)->getConstants();
        $actual = enumToMap(PhoneNumberParseErrorType::class);

        expect($expected)->toBe($actual);
    });
});
