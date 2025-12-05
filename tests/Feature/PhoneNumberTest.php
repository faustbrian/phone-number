<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Cline\PhoneNumber\Enums\CarrierNameMode;
use Cline\PhoneNumber\Enums\PhoneNumberFormat;
use Cline\PhoneNumber\Enums\PhoneNumberParseErrorType;
use Cline\PhoneNumber\Enums\PhoneNumberType;
use Cline\PhoneNumber\Exceptions\PhoneNumberNotFoundException;
use Cline\PhoneNumber\Exceptions\PhoneNumberParseException;
use Cline\PhoneNumber\PhoneNumber;
use Composer\InstalledVersions;

const AR_MOBILE = '+5491187654321';
const AR_NUMBER = '+541187654321';
const AU_NUMBER = '+61236618300';
const BS_MOBILE = '+12423570000';
const BS_NUMBER = '+12423651234';
const DE_NUMBER = '+4930123456';
const GB_MOBILE = '+447912345678';
const GB_NUMBER = '+442070313000';
const IT_MOBILE = '+39345678901';
const IT_NUMBER = '+390236618300';
const MX_MOBILE1 = '+5212345678900';
const MX_MOBILE2 = '+5215512345678';
const MX_NUMBER1 = '+523312345678';
const MX_NUMBER2 = '+528211234567';
const NZ_NUMBER = '+6433316005';
const US_NUMBER = '+16502530000';
const US_PREMIUM = '+19002530000';
const US_LOCAL_NUMBER = '+12530000';
const US_TOLLFREE = '+18002530000';
const INTERNATIONAL_TOLL_FREE = '+80012345678';
const INTERNATIONAL_TOLL_FREE_TOO_LONG = '+800123456789';
const UNIVERSAL_PREMIUM_RATE = '+979123456789';

function requireUpstreamVersion(string $version): void
{
    $packageName = 'giggsey/libphonenumber-for-php';
    $installedVersion = InstalledVersions::getVersion($packageName);

    if (!version_compare($installedVersion, $version, '<')) {
        return;
    }

    test()->markTestSkipped(sprintf('This test requires %s version %s or later.', $packageName, $version));
}

describe('getExampleNumber', function (): void {
    it('returns valid phone number for region', function (string $regionCode, string $callingCode, ?PhoneNumberType $numberType): void {
        if (!$numberType instanceof PhoneNumberType) {
            $phoneNumber = PhoneNumber::getExampleNumber($regionCode);
        } else {
            $phoneNumber = PhoneNumber::getExampleNumber($regionCode, $numberType);
        }

        expect($phoneNumber)->toBeInstanceOf(PhoneNumber::class);
        expect($phoneNumber->isValidNumber())->toBeTrue();

        if ($numberType instanceof PhoneNumberType) {
            expect($phoneNumber->getNumberType())->toBe($numberType);
        }

        expect($phoneNumber->getCountryCode())->toBe($callingCode);
        expect($phoneNumber->getRegionCode())->toBe($regionCode);
    })->with([
        ['US', '1', null],
        ['FR', '33', PhoneNumberType::FIXED_LINE],
        ['FR', '33', PhoneNumberType::MOBILE],
        ['GB', '44', PhoneNumberType::FIXED_LINE],
        ['GB', '44', PhoneNumberType::MOBILE],
    ]);

    it('throws exception for invalid region code', function (): void {
        PhoneNumber::getExampleNumber('ZZ');
    })->throws(PhoneNumberNotFoundException::class);
});

describe('getNationalNumber', function (): void {
    it('returns correct national number', function (string $expected, string $phoneNumber): void {
        expect(PhoneNumber::parse($phoneNumber)->getNationalNumber())->toBe($expected);
    })->with([
        ['6502530000', US_NUMBER],
        ['345678901', IT_MOBILE],
        ['236618300', IT_NUMBER],
        ['12345678', INTERNATIONAL_TOLL_FREE],
    ]);
});

describe('parse', function (): void {
    it('parses national number correctly', function (string $expected, string $numberToParse, string $regionCode, ?string $minVersion): void {
        if ($minVersion !== null) {
            requireUpstreamVersion($minVersion);
        }

        expect((string) PhoneNumber::parse($numberToParse, $regionCode))->toBe($expected);
    })->with([
        [NZ_NUMBER, '033316005', 'NZ', null],
        [NZ_NUMBER, '33316005', 'NZ', null],
        [NZ_NUMBER, '03-331 6005', 'NZ', null],
        [NZ_NUMBER, '03 331 6005', 'NZ', null],
        [NZ_NUMBER, '0064 3 331 6005', 'NZ', null],
        [NZ_NUMBER, '01164 3 331 6005', 'US', null],
        [NZ_NUMBER, '+64 3 331 6005', 'US', null],
        ['+6464123456', '64(0)64123456', 'NZ', '8.5.2'],
        [DE_NUMBER, '301/23456', 'DE', null],
        ['+11234567890', '123-456-7890', 'US', '8.0.0'],
    ]);

    it('throws exception for invalid input', function (string $phoneNumber, ?string $regionCode, PhoneNumberParseErrorType $errorType): void {
        try {
            PhoneNumber::parse($phoneNumber, $regionCode);
            test()->fail('Expected PhoneNumberParseException was not thrown.');
        } catch (PhoneNumberParseException $phoneNumberParseException) {
            expect($phoneNumberParseException->errorType)->toBe($errorType);
            expect($phoneNumberParseException->getCode())->toBe($errorType->value);
        }
    })->with([
        ['', null, PhoneNumberParseErrorType::NOT_A_NUMBER],
        ['', 'US', PhoneNumberParseErrorType::NOT_A_NUMBER],
        ['This is not a phone number', 'NZ', PhoneNumberParseErrorType::NOT_A_NUMBER],
        ['1 Still not a number', 'NZ', PhoneNumberParseErrorType::NOT_A_NUMBER],
        ['1 MICROSOFT', 'NZ', PhoneNumberParseErrorType::NOT_A_NUMBER],
        ['12 MICROSOFT', 'NZ', PhoneNumberParseErrorType::NOT_A_NUMBER],
        ['01495 72553301873 810104', 'GB', PhoneNumberParseErrorType::TOO_LONG],
        ['+---', 'DE', PhoneNumberParseErrorType::NOT_A_NUMBER],
        ['+***', 'DE', PhoneNumberParseErrorType::NOT_A_NUMBER],
        ['+*******91', 'DE', PhoneNumberParseErrorType::NOT_A_NUMBER],
        ['+ 00 210 3 331 6005', 'NZ', PhoneNumberParseErrorType::INVALID_COUNTRY_CODE],
        ['+49 0', 'DE', PhoneNumberParseErrorType::TOO_SHORT_NSN],
        ['+02366', null, PhoneNumberParseErrorType::INVALID_COUNTRY_CODE],
        ['+210 3456 56789', 'NZ', PhoneNumberParseErrorType::INVALID_COUNTRY_CODE],
        ['123 456 7890', null, PhoneNumberParseErrorType::INVALID_COUNTRY_CODE],
        ['123 456 7890', 'CS', PhoneNumberParseErrorType::INVALID_COUNTRY_CODE],
        ['0044', 'GB', PhoneNumberParseErrorType::TOO_SHORT_AFTER_IDD],
        ['0044------', 'GB', PhoneNumberParseErrorType::TOO_SHORT_AFTER_IDD],
        ['011', 'US', PhoneNumberParseErrorType::TOO_SHORT_AFTER_IDD],
        ['0119', 'US', PhoneNumberParseErrorType::TOO_SHORT_AFTER_IDD],
    ]);
});

describe('getRegionCode', function (): void {
    it('returns correct region code', function (?string $expected, string $phoneNumber): void {
        expect(PhoneNumber::parse($phoneNumber)->getRegionCode())->toBe($expected);
    })->with([
        ['BS', BS_NUMBER],
        ['US', US_NUMBER],
        ['GB', GB_MOBILE],
        [null, INTERNATIONAL_TOLL_FREE],
    ]);
});

describe('getNumberType', function (): void {
    it('returns correct number type', function (PhoneNumberType $expected, string $phoneNumber, ?string $minVersion): void {
        if ($minVersion !== null) {
            requireUpstreamVersion($minVersion);
        }

        expect(PhoneNumber::parse($phoneNumber)->getNumberType())->toBe($expected);
    })->with([
        [PhoneNumberType::PREMIUM_RATE, US_PREMIUM, null],
        [PhoneNumberType::PREMIUM_RATE, '+39892123', null],
        [PhoneNumberType::PREMIUM_RATE, '+449187654321', null],
        [PhoneNumberType::PREMIUM_RATE, '+499001654321', null],
        [PhoneNumberType::PREMIUM_RATE, '+4990091234567', null],
        [PhoneNumberType::PREMIUM_RATE, UNIVERSAL_PREMIUM_RATE, null],
        [PhoneNumberType::TOLL_FREE, '+39803123', null],
        [PhoneNumberType::TOLL_FREE, '+498001234567', null],
        [PhoneNumberType::TOLL_FREE, INTERNATIONAL_TOLL_FREE, null],
        [PhoneNumberType::MOBILE, BS_MOBILE, null],
        [PhoneNumberType::MOBILE, GB_MOBILE, null],
        [PhoneNumberType::MOBILE, IT_MOBILE, '8.9.11'],
        [PhoneNumberType::MOBILE, AR_MOBILE, null],
        [PhoneNumberType::MOBILE, '+4915123456789', null],
        [PhoneNumberType::FIXED_LINE, BS_NUMBER, null],
        [PhoneNumberType::FIXED_LINE, IT_NUMBER, null],
        [PhoneNumberType::FIXED_LINE, GB_NUMBER, null],
        [PhoneNumberType::FIXED_LINE, DE_NUMBER, null],
        [PhoneNumberType::FIXED_LINE_OR_MOBILE, US_NUMBER, null],
        [PhoneNumberType::VOIP, '+445631231234', null],
        [PhoneNumberType::PERSONAL_NUMBER, '+447031231234', null],
        [PhoneNumberType::UNKNOWN, US_LOCAL_NUMBER, null],
    ]);
});

describe('isPossibleNumber', function (): void {
    it('returns true for possible numbers', function (string $phoneNumber): void {
        expect(PhoneNumber::parse($phoneNumber)->isPossibleNumber())->toBeTrue();
    })->with([
        [US_NUMBER],
        [IT_NUMBER],
        [GB_MOBILE],
        [INTERNATIONAL_TOLL_FREE],
        [UNIVERSAL_PREMIUM_RATE],
        ['+6421387835'],
        [US_LOCAL_NUMBER],
        ['+3923661830000'],
        ['+44791234567'],
        ['+491234'],
        ['+643316005'],
        ['+39232366'],
    ]);

    it('returns false for impossible numbers', function (string $phoneNumber): void {
        expect(PhoneNumber::parse($phoneNumber)->isPossibleNumber())->toBeFalse();
    })->with([
        [INTERNATIONAL_TOLL_FREE_TOO_LONG],
        ['+1253000'],
    ]);
});

describe('isValidNumber', function (): void {
    it('returns true for valid numbers', function (string $phoneNumber): void {
        expect(PhoneNumber::parse($phoneNumber)->isValidNumber())->toBeTrue();
    })->with([
        [US_NUMBER],
        [IT_NUMBER],
        [GB_MOBILE],
        [INTERNATIONAL_TOLL_FREE],
        [UNIVERSAL_PREMIUM_RATE],
        ['+6421387835'],
    ]);

    it('returns false for invalid numbers', function (string $phoneNumber): void {
        expect(PhoneNumber::parse($phoneNumber)->isValidNumber())->toBeFalse();
    })->with([
        [INTERNATIONAL_TOLL_FREE_TOO_LONG],
        ['+1253000'],
        [US_LOCAL_NUMBER],
        ['+3923661830000'],
        ['+44791234567'],
        ['+491234'],
        ['+643316005'],
        ['+39232366'],
    ]);
});

describe('format', function (): void {
    it('formats phone number correctly', function (string $expected, string $phoneNumber, PhoneNumberFormat $format, ?string $minVersion): void {
        if ($minVersion !== null) {
            requireUpstreamVersion($minVersion);
        }

        expect(PhoneNumber::parse($phoneNumber)->format($format))->toBe($expected);
    })->with([
        ['(650) 253-0000', US_NUMBER, PhoneNumberFormat::NATIONAL, null],
        ['+1 650-253-0000', US_NUMBER, PhoneNumberFormat::INTERNATIONAL, null],
        ['(800) 253-0000', US_TOLLFREE, PhoneNumberFormat::NATIONAL, null],
        ['+1 800-253-0000', US_TOLLFREE, PhoneNumberFormat::INTERNATIONAL, null],
        ['(900) 253-0000', US_PREMIUM, PhoneNumberFormat::NATIONAL, null],
        ['+1 900-253-0000', US_PREMIUM, PhoneNumberFormat::INTERNATIONAL, null],
        ['tel:+1-900-253-0000', US_PREMIUM, PhoneNumberFormat::RFC3966, null],
        ['(242) 365-1234', BS_NUMBER, PhoneNumberFormat::NATIONAL, null],
        ['+1 242-365-1234', BS_NUMBER, PhoneNumberFormat::INTERNATIONAL, null],
        ['020 7031 3000', GB_NUMBER, PhoneNumberFormat::NATIONAL, null],
        ['+44 20 7031 3000', GB_NUMBER, PhoneNumberFormat::INTERNATIONAL, null],
        ['07912 345678', GB_MOBILE, PhoneNumberFormat::NATIONAL, null],
        ['+44 7912 345678', GB_MOBILE, PhoneNumberFormat::INTERNATIONAL, null],
        ['030 1234', '+49301234', PhoneNumberFormat::NATIONAL, null],
        ['+49 30 1234', '+49301234', PhoneNumberFormat::INTERNATIONAL, null],
        ['tel:+49-30-1234', '+49301234', PhoneNumberFormat::RFC3966, null],
        ['0291 123', '+49291123', PhoneNumberFormat::NATIONAL, null],
        ['+49 291 123', '+49291123', PhoneNumberFormat::INTERNATIONAL, null],
        ['0291 12345678', '+4929112345678', PhoneNumberFormat::NATIONAL, null],
        ['+49 291 12345678', '+4929112345678', PhoneNumberFormat::INTERNATIONAL, null],
        ['09123 12345', '+49912312345', PhoneNumberFormat::NATIONAL, null],
        ['+49 9123 12345', '+49912312345', PhoneNumberFormat::INTERNATIONAL, null],
        ['08021 2345', '+4980212345', PhoneNumberFormat::NATIONAL, null],
        ['+49 8021 2345', '+4980212345', PhoneNumberFormat::INTERNATIONAL, null],
        ['030 123456', DE_NUMBER, PhoneNumberFormat::NATIONAL, null],
        ['04134 1234', '+4941341234', PhoneNumberFormat::NATIONAL, null],
        ['02 3661 8300', IT_NUMBER, PhoneNumberFormat::NATIONAL, null],
        ['+39 02 3661 8300', IT_NUMBER, PhoneNumberFormat::INTERNATIONAL, null],
        ['+390236618300', IT_NUMBER, PhoneNumberFormat::E164, null],
        ['345 678 901', IT_MOBILE, PhoneNumberFormat::NATIONAL, null],
        ['+39 345 678 901', IT_MOBILE, PhoneNumberFormat::INTERNATIONAL, null],
        ['+39345678901', IT_MOBILE, PhoneNumberFormat::E164, null],
        ['(02) 3661 8300', AU_NUMBER, PhoneNumberFormat::NATIONAL, null],
        ['+61 2 3661 8300', AU_NUMBER, PhoneNumberFormat::INTERNATIONAL, null],
        ['+61236618300', AU_NUMBER, PhoneNumberFormat::E164, null],
        ['1800 123 456', '+611800123456', PhoneNumberFormat::NATIONAL, null],
        ['+61 1800 123 456', '+611800123456', PhoneNumberFormat::INTERNATIONAL, null],
        ['+611800123456', '+611800123456', PhoneNumberFormat::E164, null],
        ['011 8765-4321', AR_NUMBER, PhoneNumberFormat::NATIONAL, null],
        ['+54 11 8765-4321', AR_NUMBER, PhoneNumberFormat::INTERNATIONAL, null],
        ['+541187654321', AR_NUMBER, PhoneNumberFormat::E164, null],
        ['011 15-8765-4321', AR_MOBILE, PhoneNumberFormat::NATIONAL, null],
        ['+54 9 11 8765-4321', AR_MOBILE, PhoneNumberFormat::INTERNATIONAL, null],
        ['+5491187654321', AR_MOBILE, PhoneNumberFormat::E164, null],
        ['12345678900', MX_MOBILE1, PhoneNumberFormat::NATIONAL, '8.13.38'],
        ['+52 12345678900', MX_MOBILE1, PhoneNumberFormat::INTERNATIONAL, '8.13.38'],
        ['+5212345678900', MX_MOBILE1, PhoneNumberFormat::E164, '8.13.38'],
        ['15512345678', MX_MOBILE2, PhoneNumberFormat::NATIONAL, '8.13.38'],
        ['+52 15512345678', MX_MOBILE2, PhoneNumberFormat::INTERNATIONAL, '8.13.38'],
        ['+5215512345678', MX_MOBILE2, PhoneNumberFormat::E164, '8.13.38'],
        ['33 1234 5678', MX_NUMBER1, PhoneNumberFormat::NATIONAL, '8.10.23'],
        ['+52 33 1234 5678', MX_NUMBER1, PhoneNumberFormat::INTERNATIONAL, null],
        ['+523312345678', MX_NUMBER1, PhoneNumberFormat::E164, null],
        ['821 123 4567', MX_NUMBER2, PhoneNumberFormat::NATIONAL, '8.10.23'],
        ['+52 821 123 4567', MX_NUMBER2, PhoneNumberFormat::INTERNATIONAL, null],
        ['+528211234567', MX_NUMBER2, PhoneNumberFormat::E164, null],
    ]);
});

describe('formatForCallingFrom', function (): void {
    it('formats for calling from region', function (string $phoneNumber, string $countryCode, string $expected): void {
        expect(PhoneNumber::parse($phoneNumber)->formatForCallingFrom($countryCode))->toBe($expected);
    })->with([
        ['+33123456789', 'FR', '01 23 45 67 89'],
        ['+33123456789', 'BE', '00 33 1 23 45 67 89'],
        ['+33123456789', 'CH', '00 33 1 23 45 67 89'],
        ['+33123456789', 'DE', '00 33 1 23 45 67 89'],
        ['+33123456789', 'GB', '00 33 1 23 45 67 89'],
        ['+33123456789', 'US', '011 33 1 23 45 67 89'],
        ['+33123456789', 'CA', '011 33 1 23 45 67 89'],
        ['+16502530000', 'US', '1 (650) 253-0000'],
        ['+16502530000', 'CA', '1 (650) 253-0000'],
        ['+16502530000', 'FR', '00 1 650-253-0000'],
        ['+16502530000', 'BE', '00 1 650-253-0000'],
        ['+16502530000', 'CH', '00 1 650-253-0000'],
        ['+16502530000', 'DE', '00 1 650-253-0000'],
        ['+16502530000', 'GB', '00 1 650-253-0000'],
    ]);
});

describe('formatForMobileDialing', function (): void {
    it('formats for mobile dialing', function (string $phoneNumber, string $region, bool $withFormatting, ?string $expected, ?string $minVersion): void {
        if ($minVersion !== null) {
            requireUpstreamVersion($minVersion);
        }

        expect(PhoneNumber::parse($phoneNumber)->formatForMobileDialing($region, $withFormatting))->toBe($expected);
    })->with([
        ['+33123456789', 'FR', false, '0123456789', null],
        ['+33123456789', 'FR', true, '01 23 45 67 89', null],
        ['+33123456789', 'BE', false, '+33123456789', null],
        ['+33123456789', 'BE', true, '+33 1 23 45 67 89', null],
        ['+33123456789', 'US', false, '+33123456789', null],
        ['+33123456789', 'US', true, '+33 1 23 45 67 89', null],
        ['+33123456789', 'CA', false, '+33123456789', null],
        ['+33123456789', 'CA', true, '+33 1 23 45 67 89', null],
        ['+558001234567', 'CN', false, null, '8.12.51'],
        ['+558001234567', 'CN', true, null, '8.12.51'],
    ]);
});

describe('getGeographicalAreaCode', function (): void {
    it('returns correct area code', function (string $phoneNumber, string $areaCode): void {
        expect(PhoneNumber::parse($phoneNumber)->getGeographicalAreaCode())->toBe($areaCode);
    })->with([
        ['+442079460585', '20'],
        ['+441132224444', '113'],
        ['+447553840000', ''],
        ['+33123000000', '1'],
        ['+33234000000', '2'],
        ['+33345000000', '3'],
        ['+33456000000', '4'],
        ['+33567000000', '5'],
    ]);
});

describe('isEqualTo', function (): void {
    it('compares phone numbers correctly', function (string $number1, string $number2, bool $expected): void {
        $phoneNumber1 = PhoneNumber::parse($number1);
        $phoneNumber2 = PhoneNumber::parse($number2);

        expect($phoneNumber1->isEqualTo($phoneNumber2))->toBe($expected);
    })->with([
        ['+442079460585', '+442079460585', true],
        ['+442079460585', '+442079460586', false],
    ]);
});

describe('jsonSerialize', function (): void {
    it('serializes to JSON correctly', function (): void {
        $data = [
            'phoneNumber' => PhoneNumber::parse('0123000000', 'FR'),
        ];

        expect(json_encode($data))->toBe('{"phoneNumber":"+33123000000"}');
    });
});

describe('getDescription', function (): void {
    it('returns correct description', function (string $phoneNumber, string $locale, ?string $userRegion, array $expected): void {
        expect($expected)->toContain(PhoneNumber::parse($phoneNumber)->getDescription($locale, $userRegion));
    })->with([
        ['+16509036313', 'EN', null, ['Mountain View, CA']],
        ['+16509036313', 'EN', 'US', ['Mountain View, CA']],
        ['+16509036313', 'EN', 'GB', ['United States']],
        ['+16509036313', 'EN', 'FR', ['United States']],
        ['+16509036313', 'EN', 'XX', ['United States']],
        ['+16509036313', 'FR', null, ['Mountain View, CA']],
        ['+16509036313', 'FR', 'US', ['Mountain View, CA']],
        ['+16509036313', 'FR', 'GB', ['États-Unis']],
        ['+16509036313', 'FR', 'FR', ['États-Unis']],
        ['+16509036313', 'FR', 'XX', ['États-Unis']],
        ['+33381251234', 'FR', null, ['France', 'Besançon']],
        ['+33381251234', 'FR', 'FR', ['France', 'Besançon']],
        ['+33381251234', 'FR', 'US', ['France']],
        ['+33381251234', 'FR', 'XX', ['France']],
        ['+33381251234', 'EN', null, ['France', 'Besançon']],
        ['+33381251234', 'EN', 'FR', ['France', 'Besançon']],
        ['+33381251234', 'EN', 'US', ['France']],
        ['+33381251234', 'EN', 'XX', ['France']],
        ['+33328201234', 'FR', null, ['France', 'Dunkerque']],
        ['+33328201234', 'FR', 'FR', ['France', 'Dunkerque']],
        ['+33328201234', 'FR', 'US', ['France']],
        ['+33328201234', 'FR', 'XX', ['France']],
        ['+33328201234', 'GB', null, ['Dunkirk', null]],
        ['+33328201234', 'XX', null, ['Dunkirk', null]],
        ['+41229097000', 'FR', null, ['Genève']],
        ['+41229097000', 'FR', 'CH', ['Genève']],
        ['+41229097000', 'FR', 'US', ['Suisse']],
        ['+41229097000', 'XX', null, ['Geneva']],
        ['+37328000000', 'XX', null, [null]],
    ]);
});

describe('getCarrierName', function (): void {
    it('returns correct carrier name', function (string $phoneNumber, string $languageCode, CarrierNameMode $mode, ?string $expected, ?string $minVersion): void {
        if ($minVersion !== null) {
            requireUpstreamVersion($minVersion);
        }

        expect(PhoneNumber::parse($phoneNumber)->getCarrierName($languageCode, $mode))->toBe($expected);
    })->with([
        ['+33600012345', 'en', CarrierNameMode::ALWAYS, 'Free Mobile', '8.11.1'],
        ['+33600012345', 'fr', CarrierNameMode::ALWAYS, 'Free Mobile', '8.11.1'],
        ['+33600012345', 'fr', CarrierNameMode::MOBILE_ONLY, 'Free Mobile', '8.11.1'],
        ['+33600012345', 'fr', CarrierNameMode::MOBILE_NO_PORTABILITY_ONLY, null, null],
        ['+33900000000', 'fr', CarrierNameMode::ALWAYS, null, null],
        ['+447305123456', 'en', CarrierNameMode::ALWAYS, 'Virgin Mobile', '8.0.1'],
        ['+447305123456', 'fr', CarrierNameMode::ALWAYS, 'Virgin Mobile', '8.0.1'],
        ['+821001234567', 'en', CarrierNameMode::ALWAYS, 'LG U+', '8.13.17'],
        ['+821001234567', 'fr', CarrierNameMode::ALWAYS, 'LG U+', '8.13.17'],
        ['+821001234567', 'ko', CarrierNameMode::ALWAYS, '데이콤', '8.13.17'],
    ]);
});

describe('getTimeZones', function (): void {
    it('returns correct time zones', function (string $phoneNumber, array $expected, ?string $minVersion): void {
        if ($minVersion !== null) {
            requireUpstreamVersion($minVersion);
        }

        expect(PhoneNumber::parse($phoneNumber)->getTimeZones())->toBe($expected);
    })->with([
        ['+33600012345', ['Europe/Paris'], null],
        ['+441614960000', ['Europe/London'], null],
        ['+4412', [], null],
        ['+447123456789', ['Europe/Guernsey', 'Europe/Isle_of_Man', 'Europe/Jersey', 'Europe/London'], '8.10.23'],
    ]);
});
