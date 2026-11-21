<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A billing rule was broken (overpayment, paying a cancelled invoice…).
 * The message is safe to show to the user.
 */
class BillingException extends RuntimeException
{
}
