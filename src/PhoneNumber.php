<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\PhoneNumber;

use Cline\PhoneNumber\Enums\CarrierNameMode;
use Cline\PhoneNumber\Enums\PhoneNumberFormat;
use Cline\PhoneNumber\Enums\PhoneNumberType;
use Cline\PhoneNumber\Exceptions\PhoneNumberNotFoundException;
use Cline\PhoneNumber\Exceptions\PhoneNumberParseException;
use JsonSerializable;
use libphonenumber\geocoding\PhoneNumberOfflineGeocoder;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber as LibPhoneNumber;
use libphonenumber\PhoneNumberFormat as LibPhoneNumberFormat;
use libphonenumber\PhoneNumberToCarrierMapper;
use libphonenumber\PhoneNumberToTimeZonesMapper;
use libphonenumber\PhoneNumberType as LibPhoneNumberType;
use libphonenumber\PhoneNumberUtil;
use Override;
use Stringable;

use function assert;
use function mb_substr;

/**
 * A phone number.
 *
 * @author Brian Faust <brian@cline.sh>
 *
 * @psalm-immutable
 */
final readonly class PhoneNumber implements JsonSerializable, Stringable
{
    /**
     * Private constructor. Use a factory method to obtain an instance.
     */
    private function __construct(
        /**
         * The underlying PhoneNumber object from libphonenumber.
         */
        private LibPhoneNumber $phoneNumber,
    ) {}

    /**
     * Returns a string representation of this phone number in international E164 format.
     */
    #[Override()]
    public function __toString(): string
    {
        return $this->format(PhoneNumberFormat::E164);
    }

    /**
     * Parses a string representation of a phone number.
     *
     * @param string      $phoneNumber the phone number to parse
     * @param null|string $regionCode  the region code to assume, if the number is not in international format
     *
     * @throws PhoneNumberParseException
     */
    public static function parse(string $phoneNumber, ?string $regionCode = null): self
    {
        try {
            return new self(
                PhoneNumberUtil::getInstance()->parse($phoneNumber, $regionCode),
            );
        } catch (NumberParseException $numberParseException) {
            throw PhoneNumberParseException::fromLibPhoneNumber($numberParseException);
        }
    }

    /**
     * @param string          $regionCode      the region code
     * @param PhoneNumberType $phoneNumberType the phone number type, defaults to a fixed line
     *
     * @throws PhoneNumberNotFoundException if no example number is available for this region and type
     */
    public static function getExampleNumber(string $regionCode, PhoneNumberType $phoneNumberType = PhoneNumberType::FIXED_LINE): self
    {
        $phoneNumber = PhoneNumberUtil::getInstance()->getExampleNumberForType(
            $regionCode,
            LibPhoneNumberType::from($phoneNumberType->value),
        );

        if (!$phoneNumber instanceof LibPhoneNumber) {
            throw PhoneNumberNotFoundException::noExampleNumberForRegionAndType($regionCode, $phoneNumberType->name);
        }

        return new self($phoneNumber);
    }

    /**
     * Returns the country code of this PhoneNumber.
     *
     * The country code is a series of 1 to 3 digits, as defined per the E.164 recommendation.
     */
    public function getCountryCode(): string
    {
        $countryCode = $this->phoneNumber->getCountryCode();
        assert($countryCode !== null);

        return (string) $countryCode;
    }

    /**
     * Returns the geographical area code of this PhoneNumber.
     *
     * Notes:
     *
     *  - geographical area codes change over time, and this method honors those changes; therefore, it doesn't
     *    guarantee the stability of the result it produces;
     *  - most non-geographical numbers have no area codes, including numbers from non-geographical entities;
     *  - some geographical numbers have no area codes.
     *
     * If this number has no area code, an empty string is returned.
     */
    public function getGeographicalAreaCode(): string
    {
        $phoneNumberUtil = PhoneNumberUtil::getInstance();

        $nationalSignificantNumber = $phoneNumberUtil->getNationalSignificantNumber($this->phoneNumber);

        $areaCodeLength = $phoneNumberUtil->getLengthOfGeographicalAreaCode($this->phoneNumber);

        return mb_substr($nationalSignificantNumber, 0, $areaCodeLength);
    }

    /**
     * Returns the national number of this PhoneNumber.
     *
     * The national number is a series of digits.
     */
    public function getNationalNumber(): string
    {
        $nationalNumber = $this->phoneNumber->getNationalNumber();
        assert($nationalNumber !== null);

        return $nationalNumber;
    }

    /**
     * Returns the region code of this PhoneNumber.
     *
     * The region code is an ISO 3166-1 alpha-2 country code.
     *
     * If the phone number does not map to a geographic region
     * (global networks, such as satellite phone numbers) this method returns null.
     *
     * @return null|string the region code, or null if the number does not map to a geographic region
     */
    public function getRegionCode(): ?string
    {
        $regionCode = PhoneNumberUtil::getInstance()->getRegionCodeForNumber($this->phoneNumber);

        if ($regionCode === '001') {
            return null;
        }

        return $regionCode;
    }

    /**
     * Returns whether this phone number is a possible number.
     *
     * Note this provides a more lenient and faster check than `isValidNumber()`.
     */
    public function isPossibleNumber(): bool
    {
        return PhoneNumberUtil::getInstance()->isPossibleNumber($this->phoneNumber);
    }

    /**
     * Returns whether this phone number matches a valid pattern.
     *
     * Note this doesn't verify the number is actually in use,
     * which is impossible to tell by just looking at a number itself.
     */
    public function isValidNumber(): bool
    {
        return PhoneNumberUtil::getInstance()->isValidNumber($this->phoneNumber);
    }

    /**
     * Returns the type of this phone number.
     */
    public function getNumberType(): PhoneNumberType
    {
        return PhoneNumberType::from(
            PhoneNumberUtil::getInstance()->getNumberType($this->phoneNumber)->value,
        );
    }

    /**
     * Returns a formatted string representation of this phone number.
     */
    public function format(PhoneNumberFormat $format): string
    {
        return PhoneNumberUtil::getInstance()->format(
            $this->phoneNumber,
            LibPhoneNumberFormat::from($format->value),
        );
    }

    /**
     * Formats this phone number for out-of-country dialing purposes.
     *
     * @param string $regionCode the ISO 3166-1 alpha-2 country code
     */
    public function formatForCallingFrom(string $regionCode): string
    {
        return PhoneNumberUtil::getInstance()->formatOutOfCountryCallingNumber($this->phoneNumber, $regionCode);
    }

    /**
     * Returns a number formatted in such a way that it can be dialed from a mobile phone in a specific region.
     *
     * If the number cannot be reached from the region (e.g. some countries block toll-free numbers from being called
     * from outside the country), this method returns null.
     */
    public function formatForMobileDialing(string $regionCallingFrom, bool $withFormatting): ?string
    {
        $result = PhoneNumberUtil::getInstance()->formatNumberForMobileDialing(
            $this->phoneNumber,
            $regionCallingFrom,
            $withFormatting,
        );

        return $result === '' ? null : $result;
    }

    public function isEqualTo(self $phoneNumber): bool
    {
        return $this->phoneNumber->equals($phoneNumber->phoneNumber);
    }

    /**
     * Required by interface JsonSerializable.
     */
    #[Override()]
    public function jsonSerialize(): string
    {
        return (string) $this;
    }

    /**
     * Returns a text description for this phone number, in the language provided. The description might consist of
     * the name of the country where the phone number is from, or the name of the geographical area the phone number is
     * from if more detailed information is available.
     *
     * If $userRegion is set, we also consider the region of the user. If the phone number is from the same region as
     * the user, only a lower-level description will be returned, if one exists. Otherwise, the phone number's region
     * will be returned, with optionally some more detailed information.
     *
     * For example, for a user from the region "US" (United States), we would show "Mountain View, CA" for a particular
     * number, omitting the United States from the description. For a user from the United Kingdom (region "GB"), for
     * the same number we may show "Mountain View, CA, United States" or even just "United States".
     *
     * If no description is found, this method returns null.
     *
     * @param string      $locale     the locale for which the description should be written
     * @param null|string $userRegion The region code for a given user. This region will be omitted from the description
     *                                if the phone number comes from this region. It is a two-letter uppercase CLDR
     *                                region code.
     */
    public function getDescription(string $locale, ?string $userRegion = null): ?string
    {
        $description = PhoneNumberOfflineGeocoder::getInstance()->getDescriptionForNumber(
            $this->phoneNumber,
            $locale,
            $userRegion,
        );

        if ($description === '') {
            return null;
        }

        return $description;
    }

    /**
     * Returns the name of the carrier for this phone number, in the given language.
     *
     * The carrier name is the one the number was originally allocated to, however if the country supports mobile number
     * portability the number might not belong to the returned carrier anymore.
     *
     * The conditions for returning a carrier name can be configured with the CarrierNameMode enum.
     *
     * This method returns null if the carrier is unknown, or the conditions for returning a carrier name are not met.
     */
    public function getCarrierName(
        string $languageCode,
        CarrierNameMode $mode = CarrierNameMode::ALWAYS,
    ): ?string {
        $carrierMapper = PhoneNumberToCarrierMapper::getInstance();

        $carrierName = match ($mode) {
            CarrierNameMode::ALWAYS => $carrierMapper->getNameForValidNumber($this->phoneNumber, $languageCode),
            CarrierNameMode::MOBILE_ONLY => $carrierMapper->getNameForNumber($this->phoneNumber, $languageCode),
            CarrierNameMode::MOBILE_NO_PORTABILITY_ONLY => $carrierMapper->getSafeDisplayName($this->phoneNumber, $languageCode),
        };

        return $carrierName === '' ? null : $carrierName;
    }

    /**
     * Returns a list of time zones to which a phone number belongs.
     *
     * Example: ['Europe/Paris']
     *
     * Returns an empty array if the time zone is unknown.
     *
     * @return array<string>
     */
    public function getTimeZones(): array
    {
        $timeZoneMapper = PhoneNumberToTimeZonesMapper::getInstance();

        /** @var array<string> $timeZones */
        $timeZones = $timeZoneMapper->getTimeZonesForNumber($this->phoneNumber);

        if ($timeZones === [PhoneNumberToTimeZonesMapper::UNKNOWN_TIMEZONE]) {
            return [];
        }

        return $timeZones;
    }
}
