<?php

namespace LiquidRazor\Regex\Match_;

use ArrayAccess;
use Countable;
use JsonSerializable;
use LiquidRazor\Regex\Immutable;

use function count;
use function is_int;
use function is_string;

final readonly class Result implements ArrayAccess, Countable, JsonSerializable
{
    public function __construct(
        public string $pattern,
        public string $haystack,
        private array $matches,
        public bool $didMatch,
    ) {}

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->matches[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->matches[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new Immutable(self::class);
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new Immutable(self::class);
    }

    public function count(): int
    {
        return count($this->matches);
    }

    public function toArray(): array
    {
        $mode = 'indexed';
        foreach ($this->matches as $key => $value) {
            if (is_string($key)) {
                $mode = 'named';
                break;
            }
        }

        $collector = [];
        foreach ($this->matches as $key => $value) {
            if ('named' === $mode && is_int($key)) {
                continue;
            }

            $collector[$key] = $value;
        }

        return $collector;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
