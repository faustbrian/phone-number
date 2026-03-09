## Table of Contents

1. [Basic Usage](#doc-cookbooks-basic-usage)
2. [Metadata](#doc-cookbooks-metadata)
3. [Exception Handling](#doc-cookbooks-exception-handling)
4. [Overview](#doc-docs-readme)
5. [Basic Usage](#doc-docs-basic-usage)
6. [Exception Handling](#doc-docs-exception-handling)
7. [Metadata](#doc-docs-metadata)
<a id="doc-cookbooks-basic-usage"></a>

# Basic Usage Cookbook

This cookbook covers the essential operations for parsing, validating, and formatting phone numbers.

## Quick Start

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberFormat;

// Parse a phone number
$phone = PhoneNumber::parse('+1 650 253 0000');

// Validate
$phone->isValidNumber(); // true

// Format
$phone->format(PhoneNumberFormat::INTERNATIONAL); // "+1 650-253-0000"
```

## Parsing Phone Numbers

### International Format

When the number includes a country code prefix, no region hint is needed:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');
$phone = PhoneNumber::parse('+44 20 7946 0958');
$phone = PhoneNumber::parse('+81 3 1234 5678');
```

### Local Format with Region Hint

For numbers without a country code, provide the ISO 3166-1 alpha-2 region code:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('650 253 0000', 'US');
$phone = PhoneNumber::parse('020 7946 0958', 'GB');
$phone = PhoneNumber::parse('03-1234-5678', 'JP');
```

### Example Numbers

Get example phone numbers for testing or documentation:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberType;

// Default: fixed line
$example = PhoneNumber::getExampleNumber('US');

// Specific type
$mobile = PhoneNumber::getExampleNumber('US', PhoneNumberType::MOBILE);
$tollFree = PhoneNumber::getExampleNumber('US', PhoneNumberType::TOLL_FREE);
```

## Validation

### Quick Validation

Use `isPossibleNumber()` for fast, lenient validation:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');

if ($phone->isPossibleNumber()) {
    echo 'Number has valid length and format';
}
```

### Full Validation

Use `isValidNumber()` for comprehensive validation against known patterns:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');

if ($phone->isValidNumber()) {
    echo 'Number matches valid patterns for its region';
}
```

## Formatting

### Available Formats

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberFormat;

$phone = PhoneNumber::parse('+1 650 253 0000');

// E.164 format (international, no formatting)
$phone->format(PhoneNumberFormat::E164); // "+16502530000"

// International format with formatting
$phone->format(PhoneNumberFormat::INTERNATIONAL); // "+1 650-253-0000"

// National format (local dialing)
$phone->format(PhoneNumberFormat::NATIONAL); // "(650) 253-0000"

// RFC 3966 URI format
$phone->format(PhoneNumberFormat::RFC3966); // "tel:+1-650-253-0000"
```

### String Conversion

The phone number converts to E.164 format by default:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');

echo $phone; // "+16502530000"
echo (string) $phone; // "+16502530000"

// Use in string contexts
$message = "Call us at {$phone}";
```

### Dialing from Another Country

Format a number for dialing from a specific country:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');

// From United Kingdom
$phone->formatForCallingFrom('GB'); // "00 1 650-253-0000"

// From Japan
$phone->formatForCallingFrom('JP'); // "010 1 650-253-0000"
```

### Mobile Dialing Format

Format for dialing from a mobile phone in a specific region:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');

// With formatting
$phone->formatForMobileDialing('US', true); // "+1 650-253-0000"

// Without formatting (digits only)
$phone->formatForMobileDialing('US', false); // "+16502530000"

// Returns null if unreachable from that region
$result = $phone->formatForMobileDialing('XX', true); // null
```

## Number Components

### Country Code

The E.164 country code (1-3 digits):

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');
echo $phone->getCountryCode(); // "1"

$phone = PhoneNumber::parse('+44 20 7946 0958');
echo $phone->getCountryCode(); // "44"
```

### National Number

The national significant number (without country code):

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');
echo $phone->getNationalNumber(); // "6502530000"
```

### Region Code

The ISO 3166-1 alpha-2 country code:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');
echo $phone->getRegionCode(); // "US"

// Returns null for non-geographic numbers (satellite, etc.)
$satellite = PhoneNumber::parse('+881 123 456 789');
echo $satellite->getRegionCode(); // null
```

### Geographical Area Code

The area code for geographic numbers:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');
echo $phone->getGeographicalAreaCode(); // "650"

// Returns empty string for numbers without area codes
$mobile = PhoneNumber::parse('+44 7911 123456');
echo $mobile->getGeographicalAreaCode(); // ""
```

## Number Types

Determine the type of phone number:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberType;

$phone = PhoneNumber::parse('+1 650 253 0000');
$type = $phone->getNumberType();

match ($type) {
    PhoneNumberType::FIXED_LINE => 'Landline',
    PhoneNumberType::MOBILE => 'Mobile',
    PhoneNumberType::FIXED_LINE_OR_MOBILE => 'Landline or Mobile',
    PhoneNumberType::TOLL_FREE => 'Toll-free',
    PhoneNumberType::PREMIUM_RATE => 'Premium rate',
    PhoneNumberType::SHARED_COST => 'Shared cost',
    PhoneNumberType::VOIP => 'VoIP',
    PhoneNumberType::PERSONAL_NUMBER => 'Personal number',
    PhoneNumberType::PAGER => 'Pager',
    PhoneNumberType::UAN => 'Universal Access Number',
    PhoneNumberType::VOICEMAIL => 'Voicemail',
    PhoneNumberType::UNKNOWN => 'Unknown type',
};
```

## Comparison

Compare two phone numbers for equality:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone1 = PhoneNumber::parse('+1 650 253 0000');
$phone2 = PhoneNumber::parse('650-253-0000', 'US');
$phone3 = PhoneNumber::parse('+44 20 7946 0958');

$phone1->isEqualTo($phone2); // true (same number, different input format)
$phone1->isEqualTo($phone3); // false (different numbers)
```

## JSON Serialization

The phone number serializes to E.164 format in JSON:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');

json_encode($phone); // '"+16502530000"'

// In arrays/objects
json_encode(['phone' => $phone]); // '{"phone":"+16502530000"}'
```

<a id="doc-cookbooks-metadata"></a>

# Metadata Cookbook

This cookbook covers extracting metadata from phone numbers including geographic descriptions, carrier information, and time zones.

## Geographic Description

Get a human-readable description of the phone number's location:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');

// Basic usage with locale
$phone->getDescription('en'); // "Mountain View, CA"
$phone->getDescription('de'); // "Mountain View, CA"
$phone->getDescription('ja'); // "Mountain View, CA"
```

### User Region Context

When the user's region is provided, local numbers show less detail:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');

// For a US user, omit "United States"
$phone->getDescription('en', 'US'); // "Mountain View, CA"

// For a UK user, include the country
$phone->getDescription('en', 'GB'); // "Mountain View, CA, United States"
```

### Handling Unknown Locations

Returns `null` when no description is available:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 800 555 0199'); // Toll-free
$description = $phone->getDescription('en');

if ($description === null) {
    echo 'Location unknown';
}
```

## Carrier Information

Get the carrier name for a phone number:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');

// Get carrier name in English
$phone->getCarrierName('en'); // Carrier name or null
```

### Carrier Name Modes

Control when carrier names are returned using `CarrierNameMode`:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\CarrierNameMode;

$phone = PhoneNumber::parse('+1 650 253 0000');

// Always return carrier if known (default)
$phone->getCarrierName('en', CarrierNameMode::ALWAYS);

// Only return carrier for mobile numbers
$phone->getCarrierName('en', CarrierNameMode::MOBILE_ONLY);

// Only return carrier for mobile numbers in regions without number portability
$phone->getCarrierName('en', CarrierNameMode::MOBILE_NO_PORTABILITY_ONLY);
```

### Important Notes on Carrier Data

The carrier name reflects the **original** carrier allocation:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\CarrierNameMode;

$phone = PhoneNumber::parse('+1 555 123 4567');

// This is the ORIGINAL carrier, not necessarily the current one
// Numbers can be ported to different carriers
$carrier = $phone->getCarrierName('en');

// Use MOBILE_NO_PORTABILITY_ONLY for more accurate carrier info
// Only returns a carrier for regions without number portability
$safeCarrier = $phone->getCarrierName('en', CarrierNameMode::MOBILE_NO_PORTABILITY_ONLY);
```

## Time Zones

Get the time zones associated with a phone number:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 650 253 0000');

$timeZones = $phone->getTimeZones();
// ["America/Los_Angeles"]
```

### Multiple Time Zones

Some regions span multiple time zones:

```php
use Cline\PhoneNumber\PhoneNumber;

// A number that could be in multiple time zones
$phone = PhoneNumber::parse('+1 800 555 0199');

$timeZones = $phone->getTimeZones();
// May return multiple time zones for toll-free or non-geographic numbers
```

### Unknown Time Zones

Returns an empty array when time zone is unknown:

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+881 123 456 789'); // Satellite phone

$timeZones = $phone->getTimeZones();
// []

if (empty($timeZones)) {
    echo 'Time zone unknown';
}
```

### Working with Time Zones

Use the time zone data with PHP's DateTime:

```php
use Cline\PhoneNumber\PhoneNumber;
use DateTimeZone;
use DateTimeImmutable;

$phone = PhoneNumber::parse('+1 650 253 0000');

$timeZones = $phone->getTimeZones();

if (!empty($timeZones)) {
    $tz = new DateTimeZone($timeZones[0]);
    $localTime = new DateTimeImmutable('now', $tz);

    echo "Local time: " . $localTime->format('Y-m-d H:i:s T');
}
```

## Combining Metadata

Build a complete phone number profile:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberFormat;

function getPhoneProfile(PhoneNumber $phone, string $locale = 'en'): array
{
    return [
        'formatted' => $phone->format(PhoneNumberFormat::INTERNATIONAL),
        'country_code' => $phone->getCountryCode(),
        'region' => $phone->getRegionCode(),
        'type' => $phone->getNumberType()->name,
        'location' => $phone->getDescription($locale),
        'carrier' => $phone->getCarrierName($locale),
        'time_zones' => $phone->getTimeZones(),
        'valid' => $phone->isValidNumber(),
    ];
}

$phone = PhoneNumber::parse('+1 650 253 0000');
$profile = getPhoneProfile($phone);

// [
//     'formatted' => '+1 650-253-0000',
//     'country_code' => '1',
//     'region' => 'US',
//     'type' => 'FIXED_LINE_OR_MOBILE',
//     'location' => 'Mountain View, CA',
//     'carrier' => null,
//     'time_zones' => ['America/Los_Angeles'],
//     'valid' => true,
// ]
```

<a id="doc-cookbooks-exception-handling"></a>

# Exception Handling Cookbook

This cookbook covers handling errors when parsing and working with phone numbers.

## Exception Hierarchy

All exceptions extend `PhoneNumberException`:

```
PhoneNumberException
├── PhoneNumberParseException    # Parsing failures
└── PhoneNumberNotFoundException # No example number available
```

## Handling Parse Errors

### Basic Try-Catch

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberParseException;

try {
    $phone = PhoneNumber::parse('invalid-input');
} catch (PhoneNumberParseException $e) {
    echo $e->getMessage();
}
```

### Checking Error Types

The `PhoneNumberParseException` includes an `errorType` property for granular handling:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberParseException;
use Cline\PhoneNumber\PhoneNumberParseErrorType;

try {
    $phone = PhoneNumber::parse('+999 123 456');
} catch (PhoneNumberParseException $e) {
    match ($e->errorType) {
        PhoneNumberParseErrorType::INVALID_COUNTRY_CODE =>
            'The country code is not recognized',
        PhoneNumberParseErrorType::NOT_A_NUMBER =>
            'This does not appear to be a phone number',
        PhoneNumberParseErrorType::TOO_SHORT_AFTER_IDD =>
            'Number is too short after the international prefix',
        PhoneNumberParseErrorType::TOO_SHORT_NSN =>
            'Number is too short',
        PhoneNumberParseErrorType::TOO_LONG =>
            'Number is too long',
    };
}
```

## Parse Error Types

### INVALID_COUNTRY_CODE

The country code is not recognized:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberParseException;
use Cline\PhoneNumber\PhoneNumberParseErrorType;

try {
    // +999 is not a valid country code
    $phone = PhoneNumber::parse('+999 123 456 789');
} catch (PhoneNumberParseException $e) {
    $e->errorType === PhoneNumberParseErrorType::INVALID_COUNTRY_CODE; // true
}
```

### NOT_A_NUMBER

The input does not look like a phone number:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberParseException;
use Cline\PhoneNumber\PhoneNumberParseErrorType;

try {
    $phone = PhoneNumber::parse('hello world');
} catch (PhoneNumberParseException $e) {
    $e->errorType === PhoneNumberParseErrorType::NOT_A_NUMBER; // true
}
```

### TOO_SHORT_AFTER_IDD

Too few digits after the international dialing prefix:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberParseException;
use Cline\PhoneNumber\PhoneNumberParseErrorType;

try {
    $phone = PhoneNumber::parse('+1 23');
} catch (PhoneNumberParseException $e) {
    $e->errorType === PhoneNumberParseErrorType::TOO_SHORT_AFTER_IDD; // true
}
```

### TOO_SHORT_NSN

The national significant number is too short:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberParseException;
use Cline\PhoneNumber\PhoneNumberParseErrorType;

try {
    $phone = PhoneNumber::parse('12', 'US');
} catch (PhoneNumberParseException $e) {
    $e->errorType === PhoneNumberParseErrorType::TOO_SHORT_NSN; // true
}
```

### TOO_LONG

The number has too many digits:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberParseException;
use Cline\PhoneNumber\PhoneNumberParseErrorType;

try {
    $phone = PhoneNumber::parse('+1 650 253 0000 0000 0000 0000');
} catch (PhoneNumberParseException $e) {
    $e->errorType === PhoneNumberParseErrorType::TOO_LONG; // true
}
```

## Handling Example Number Errors

When no example number is available for a region and type:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberType;
use Cline\PhoneNumber\PhoneNumberNotFoundException;

try {
    // Some region/type combinations have no example
    $phone = PhoneNumber::getExampleNumber('XX', PhoneNumberType::PAGER);
} catch (PhoneNumberNotFoundException $e) {
    echo $e->getMessage();
    // "No example number is available for region "XX" and type "PAGER"."
}
```

## Catching All Phone Number Errors

Use the base exception to catch any phone number error:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberException;

try {
    $phone = PhoneNumber::parse($userInput);
    // ... work with the phone number
} catch (PhoneNumberException $e) {
    // Catches both parse errors and not-found errors
    log_error($e->getMessage());
}
```

## User-Friendly Error Messages

Create user-friendly messages based on error types:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberParseException;
use Cline\PhoneNumber\PhoneNumberParseErrorType;

function parsePhoneNumber(string $input, ?string $region = null): array
{
    try {
        $phone = PhoneNumber::parse($input, $region);
        return ['success' => true, 'phone' => $phone];
    } catch (PhoneNumberParseException $e) {
        $message = match ($e->errorType) {
            PhoneNumberParseErrorType::INVALID_COUNTRY_CODE =>
                'Please check the country code and try again.',
            PhoneNumberParseErrorType::NOT_A_NUMBER =>
                'Please enter a valid phone number.',
            PhoneNumberParseErrorType::TOO_SHORT_AFTER_IDD,
            PhoneNumberParseErrorType::TOO_SHORT_NSN =>
                'The phone number is too short.',
            PhoneNumberParseErrorType::TOO_LONG =>
                'The phone number is too long.',
        };

        return ['success' => false, 'error' => $message];
    }
}
```

## Safe Parsing Pattern

Create a helper for optional parsing:

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\PhoneNumberParseException;

function parsePhoneNumberOrNull(string $input, ?string $region = null): ?PhoneNumber
{
    try {
        return PhoneNumber::parse($input, $region);
    } catch (PhoneNumberParseException) {
        return null;
    }
}

// Usage
$phone = parsePhoneNumberOrNull($userInput, 'US');
if ($phone !== null && $phone->isValidNumber()) {
    // Process valid phone number
}
```

<a id="doc-docs-readme"></a>

Phone Number is a PHP library for parsing, validating, and formatting phone numbers using libphonenumber.

## Installation

```bash
composer require cline/phone-number
```

## Basic Usage

```php
use Cline\PhoneNumber\PhoneNumber;

// Parse a phone number
$phone = PhoneNumber::parse('+1 (555) 123-4567');

// Get formatted versions
$phone->format();           // "+15551234567"
$phone->formatNational();   // "(555) 123-4567"
$phone->formatInternational(); // "+1 555-123-4567"
$phone->formatE164();       // "+15551234567"

// Get components
$phone->getCountryCode();   // 1
$phone->getNationalNumber(); // "5551234567"
$phone->getRegionCode();    // "US"
```

## Validation

```php
use Cline\PhoneNumber\PhoneNumber;

// Check if valid
$phone = PhoneNumber::parse('+1 555 123 4567');
$phone->isValid(); // true

// Invalid number
$phone = PhoneNumber::parse('not a number');
$phone->isValid(); // false

// Validate for specific region
$phone->isValidForRegion('US'); // true
$phone->isValidForRegion('GB'); // false
```

## Parsing with Region

```php
// Parse with default region
$phone = PhoneNumber::parse('555-123-4567', 'US');

// Useful for numbers without country code
$phone = PhoneNumber::parse('020 7946 0958', 'GB');
$phone->formatInternational(); // "+44 20 7946 0958"
```

## Phone Number Types

```php
$phone = PhoneNumber::parse('+1 555 123 4567');

$phone->getType(); // "FIXED_LINE_OR_MOBILE"
$phone->isMobile(); // true/false
$phone->isFixedLine(); // true/false
$phone->isTollFree(); // false
```

## Next Steps

- [Basic Usage](#doc-docs-basic-usage) - Detailed parsing and formatting
- [Exception Handling](#doc-docs-exception-handling) - Handle parsing errors
- [Metadata](#doc-docs-metadata) - Access phone number metadata

<a id="doc-docs-basic-usage"></a>

Parsing, formatting, and validating phone numbers.

## Parsing Phone Numbers

```php
use Cline\PhoneNumber\PhoneNumber;

// International format
$phone = PhoneNumber::parse('+44 20 7946 0958');

// With region hint
$phone = PhoneNumber::parse('020 7946 0958', 'GB');

// Various input formats
PhoneNumber::parse('+1-555-123-4567');
PhoneNumber::parse('(555) 123-4567', 'US');
PhoneNumber::parse('555.123.4567', 'US');
PhoneNumber::parse('5551234567', 'US');
```

## Formatting

### Standard Formats

```php
$phone = PhoneNumber::parse('+1 555 123 4567');

// E.164 (international standard)
$phone->formatE164(); // "+15551234567"

// International
$phone->formatInternational(); // "+1 555-123-4567"

// National
$phone->formatNational(); // "(555) 123-4567"

// RFC 3966 (tel: URI)
$phone->formatRFC3966(); // "tel:+1-555-123-4567"
```

### Custom Formatting

```php
// Format for specific region
$phone->formatForRegion('US'); // "(555) 123-4567"
$phone->formatForRegion('GB'); // "001 555 123 4567"

// Out-of-country format
$phone->formatOutOfCountry('GB'); // "00 1 555-123-4567"
```

## Validation

```php
$phone = PhoneNumber::parse('+1 555 123 4567');

// Basic validation
$phone->isValid(); // true

// Region-specific validation
$phone->isValidForRegion('US'); // true
$phone->isValidForRegion('CA'); // true (same country code)
$phone->isValidForRegion('GB'); // false

// Possible number (less strict)
$phone->isPossible(); // true
```

## Phone Number Components

```php
$phone = PhoneNumber::parse('+44 20 7946 0958');

// Country code
$phone->getCountryCode(); // 44

// National number
$phone->getNationalNumber(); // "2079460958"

// Region code
$phone->getRegionCode(); // "GB"

// Extension (if present)
$phone = PhoneNumber::parse('+1 555 123 4567 ext. 890');
$phone->getExtension(); // "890"
```

## Phone Number Types

```php
$phone = PhoneNumber::parse('+1 555 123 4567');

// Get type as string
$phone->getType();
// Possible values:
// - FIXED_LINE
// - MOBILE
// - FIXED_LINE_OR_MOBILE
// - TOLL_FREE
// - PREMIUM_RATE
// - SHARED_COST
// - VOIP
// - PERSONAL_NUMBER
// - PAGER
// - UAN
// - VOICEMAIL
// - UNKNOWN

// Type checks
$phone->isMobile();
$phone->isFixedLine();
$phone->isTollFree();
$phone->isPremiumRate();
$phone->isVoip();
```

## Comparison

```php
$phone1 = PhoneNumber::parse('+1 555 123 4567');
$phone2 = PhoneNumber::parse('(555) 123-4567', 'US');
$phone3 = PhoneNumber::parse('+44 20 7946 0958');

$phone1->equals($phone2); // true (same number)
$phone1->equals($phone3); // false

// Compare as string
(string) $phone1 === (string) $phone2; // true
```

## Static Helpers

```php
use Cline\PhoneNumber\PhoneNumber;

// Quick validation
PhoneNumber::isValid('+1 555 123 4567'); // true
PhoneNumber::isValid('invalid'); // false

// Quick formatting
PhoneNumber::formatE164('+1 (555) 123-4567'); // "+15551234567"
```

<a id="doc-docs-exception-handling"></a>

Handling phone number parsing and validation errors.

## Parse Exceptions

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\Exceptions\PhoneNumberParseException;

try {
    $phone = PhoneNumber::parse('not a phone number');
} catch (PhoneNumberParseException $e) {
    echo $e->getMessage();
    // "The string supplied did not seem to be a phone number"

    echo $e->getErrorType();
    // "NOT_A_NUMBER"
}
```

## Error Types

```php
use Cline\PhoneNumber\Exceptions\PhoneNumberParseException;

// NOT_A_NUMBER - Input doesn't look like a phone number
try {
    PhoneNumber::parse('hello world');
} catch (PhoneNumberParseException $e) {
    $e->getErrorType(); // "NOT_A_NUMBER"
}

// INVALID_COUNTRY_CODE - Country code not recognized
try {
    PhoneNumber::parse('+999 123 456');
} catch (PhoneNumberParseException $e) {
    $e->getErrorType(); // "INVALID_COUNTRY_CODE"
}

// TOO_SHORT_AFTER_IDD - Number too short after country code
try {
    PhoneNumber::parse('+1 12');
} catch (PhoneNumberParseException $e) {
    $e->getErrorType(); // "TOO_SHORT_AFTER_IDD"
}

// TOO_SHORT_NSN - National number too short
try {
    PhoneNumber::parse('123', 'US');
} catch (PhoneNumberParseException $e) {
    $e->getErrorType(); // "TOO_SHORT_NSN"
}

// TOO_LONG - Number is too long
try {
    PhoneNumber::parse('+1 55512345678901234567890');
} catch (PhoneNumberParseException $e) {
    $e->getErrorType(); // "TOO_LONG"
}
```

## Safe Parsing

```php
use Cline\PhoneNumber\PhoneNumber;

// Returns null instead of throwing
$phone = PhoneNumber::tryParse('invalid');
if ($phone === null) {
    // Handle invalid input
}

// With region
$phone = PhoneNumber::tryParse('555-123-4567', 'US');
```

## Validation Without Exceptions

```php
// Check validity without parsing
$isValid = PhoneNumber::isValid('+1 555 123 4567'); // true
$isValid = PhoneNumber::isValid('invalid'); // false

// Check if possible (less strict)
$isPossible = PhoneNumber::isPossible('+1 555 123 4567'); // true
```

## Custom Exception Handling

```php
use Cline\PhoneNumber\PhoneNumber;
use Cline\PhoneNumber\Exceptions\PhoneNumberParseException;
use Cline\PhoneNumber\Exceptions\InvalidRegionException;

function parseUserPhone(string $input, string $region): ?PhoneNumber
{
    try {
        $phone = PhoneNumber::parse($input, $region);

        if (!$phone->isValid()) {
            return null;
        }

        return $phone;
    } catch (PhoneNumberParseException $e) {
        logger()->warning('Invalid phone number', [
            'input' => $input,
            'error' => $e->getErrorType(),
        ]);
        return null;
    } catch (InvalidRegionException $e) {
        logger()->error('Invalid region code', [
            'region' => $region,
        ]);
        return null;
    }
}
```

## Laravel Validation

```php
use Cline\PhoneNumber\Rules\PhoneNumberRule;

// In form request
public function rules(): array
{
    return [
        'phone' => ['required', new PhoneNumberRule()],
    ];
}

// With region
public function rules(): array
{
    return [
        'phone' => ['required', new PhoneNumberRule('US')],
    ];
}

// With type restriction
public function rules(): array
{
    return [
        'phone' => ['required', new PhoneNumberRule(type: 'MOBILE')],
    ];
}
```

## Batch Processing

```php
use Cline\PhoneNumber\PhoneNumber;

$inputs = ['+1 555 123 4567', 'invalid', '+44 20 7946 0958'];
$results = [];

foreach ($inputs as $input) {
    $phone = PhoneNumber::tryParse($input);
    $results[] = [
        'input' => $input,
        'valid' => $phone !== null,
        'formatted' => $phone?->formatE164(),
    ];
}
```

<a id="doc-docs-metadata"></a>

Access phone number metadata and carrier information.

## Region Information

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+44 20 7946 0958');

// Region code
$phone->getRegionCode(); // "GB"

// Country code
$phone->getCountryCode(); // 44

// Region name
$phone->getRegionName(); // "United Kingdom"

// Is geographic number
$phone->isGeographic(); // true
```

## Carrier Information

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 555 123 4567');

// Carrier name (when available)
$phone->getCarrierName(); // "Example Carrier"
$phone->getCarrierName('en'); // English
$phone->getCarrierName('de'); // German

// Note: Carrier info may not be available for all numbers
```

## Timezone Information

```php
$phone = PhoneNumber::parse('+1 212 555 1234'); // New York

// Get timezones
$timezones = $phone->getTimezones();
// ["America/New_York"]

// Multiple timezones possible for some numbers
$phone = PhoneNumber::parse('+1 555 123 4567');
$timezones = $phone->getTimezones();
// May return multiple if number spans timezone boundaries
```

## Geocoding

```php
use Cline\PhoneNumber\PhoneNumber;

$phone = PhoneNumber::parse('+1 212 555 1234');

// Geographic description
$phone->getGeoDescription(); // "New York, NY"
$phone->getGeoDescription('en'); // "New York, NY"
$phone->getGeoDescription('de'); // "New York, NY"
$phone->getGeoDescription('fr'); // "New York, NY"
```

## Example Numbers

```php
use Cline\PhoneNumber\PhoneNumber;

// Get example number for region
$example = PhoneNumber::getExampleNumber('US');
$example->formatNational(); // "(201) 555-0123"

// Get example for specific type
$mobile = PhoneNumber::getExampleNumber('US', 'MOBILE');
$tollFree = PhoneNumber::getExampleNumber('US', 'TOLL_FREE');
```

## Metadata Access

```php
use Cline\PhoneNumber\PhoneNumber;

// Get all supported regions
$regions = PhoneNumber::getSupportedRegions();
// ["AC", "AD", "AE", "AF", ...]

// Get country code for region
$code = PhoneNumber::getCountryCodeForRegion('US'); // 1
$code = PhoneNumber::getCountryCodeForRegion('GB'); // 44

// Get region for country code
$region = PhoneNumber::getRegionForCountryCode(1); // "US"
$region = PhoneNumber::getRegionForCountryCode(44); // "GB"

// Note: Country code 1 has multiple regions (US, CA, etc.)
$regions = PhoneNumber::getRegionsForCountryCode(1);
// ["US", "CA", "AG", "AI", ...]
```

## Number Length

```php
$phone = PhoneNumber::parse('+1 555 123 4567');

// Get length information for region
$phone->getNationalNumberLength(); // 10

// Check possible lengths
$phone->isPossibleLength(); // true
```

## Formatting Metadata

```php
use Cline\PhoneNumber\PhoneNumber;

// Get format patterns for region
$patterns = PhoneNumber::getFormatPatterns('US');

// Get national prefix
$prefix = PhoneNumber::getNationalPrefix('US'); // "1"
$prefix = PhoneNumber::getNationalPrefix('GB'); // "0"
```
