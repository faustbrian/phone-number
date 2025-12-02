<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\PhoneNumber;

use function sprintf;

/**
 * Exception thrown when no example phone number is available for a region and type.
 *
 * @author Brian Faust <brian@cline.sh>
 */
final class PhoneNumberNotFoundException extends PhoneNumberException
{
    public static function noExampleNumberForRegionAndType(string $regionCode, string $type): self
    {
        return new self(
            sprintf('No example number is available for region "%s" and type "%s".', $regionCode, $type),
        );
    }
}
