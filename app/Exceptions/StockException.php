<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * A stock movement that can't happen (e.g. more out than is in stock).
 * The message is safe to show to the user.
 */
class StockException extends RuntimeException
{
}
