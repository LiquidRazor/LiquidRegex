<?php

namespace LiquidRazor\Regex\Replace;

use Stringable;

final readonly class Result implements Stringable
{
    public function __construct(
        public string $pattern,
        public int $count,
        public array|string $replaced,
    ) {}

    public function __toString(): string
    {
        return $this->replaced;
    }
}
