<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A booking rule was broken (room taken, wrong status for this action…).
 * The message is safe to show to the user.
 */
class BookingException extends RuntimeException
{
}
