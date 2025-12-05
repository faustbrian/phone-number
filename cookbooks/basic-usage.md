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
