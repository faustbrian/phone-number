<?php declare(strict_types=1);

/**
 * Copyright (C) Brian Faust
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cline\PhoneNumber\Enums;

/**
 * Enum values for carrier name mode.
 *
 * @author Brian Faust <brian@cline.sh>
 */
enum CarrierNameMode
{
    /**
     * Always return the carrier name when it is available.
     */
    case ALWAYS;

    /**
     * Return the carrier name only when the number is a mobile number.
     */
    case MOBILE_ONLY;

    /**
     * Return the carrier name only when the number is a mobile number,
     * and the region does not support mobile number portability.
     */
    case MOBILE_NO_PORTABILITY_ONLY;
}
