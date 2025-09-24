<?php

namespace LiquidRazor\Regex;

use RuntimeException;

class Immutable extends RuntimeException
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
