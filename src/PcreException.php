<?php

namespace LiquidRazor\Regex;

use RuntimeException;

use function preg_last_error;
use function preg_last_error_msg;

class PcreException extends RuntimeException
{
    public static function fromInternalError(): self
    {
        return new self(preg_last_error_msg(), preg_last_error());
    }

    public function __construct(string $message, int $code = 0)
    {
        parent::__construct($message, $code);
    }
}
