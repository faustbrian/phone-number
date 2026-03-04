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
