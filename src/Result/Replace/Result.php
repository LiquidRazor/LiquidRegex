<?php

namespace LiquidRazor\Regex\Result\Replace;

use Stringable;

final readonly class Result implements Stringable
{
    public function __construct(
        public string $pattern,
        public int $count,
        public array|string $replaced,
    ) {}

    /**
     * @throws \JsonException
     */
    public function __toString(): string
    {
        if(is_array($this->replaced)) {
            return json_encode($this->replaced, JSON_THROW_ON_ERROR);
        }
        return $this->replaced;
    }
}
