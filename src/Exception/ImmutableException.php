<?php

namespace LiquidRazor\Regex\Exception;

use RuntimeException;

class ImmutableException extends RuntimeException
{
    public function __construct(string $target)
    {
        parent::__construct(
            sprintf(
                '"%s" is marked as immutable.',
                $target,
            ),
        );
    }
}
