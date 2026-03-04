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
