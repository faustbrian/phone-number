<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\PhoneNumber;

use libphonenumber\NumberParseException;

/**
 * Exception thrown when a phone number cannot be parsed.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class PhoneNumberParseException extends PhoneNumberException
{
    private function __construct(
        string $message,
        int $code,
        public readonly PhoneNumberParseErrorType $errorType,
        NumberParseException $previous,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public static function fromLibPhoneNumber(NumberParseException $exception): self
    {
        /** @var int $errorType */
        $errorType = $exception->getErrorType();

        return new self(
            $exception->getMessage(),
            $errorType,
            PhoneNumberParseErrorType::from($errorType),
            $exception,
        );
    }
}
