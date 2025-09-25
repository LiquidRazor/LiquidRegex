<?php

declare(strict_types=1);

namespace LiquidRazor\Regex\Result\Matches;

use ArrayAccess;
use Countable;
use Generator;
use Iterator;
use JsonSerializable;
use InvalidArgumentException;
use LiquidRazor\Regex\Exception\ImmutableException;
use LiquidRazor\Regex\Result\ResultTypes;
use stdClass;


final  class Result implements Iterator, ArrayAccess, Countable, JsonSerializable
{
    // --- external state (readonly-like) ---
    public function __construct(
        public readonly string $pattern,
        public readonly string $haystack,
        public readonly bool   $didMatch,
        private readonly array $matches,
        private readonly int   $flags = ResultTypes::BOTH,
    ) {
        $this->validateFlags($this->flags);
        $this->gen = $this->buildIterable($this->matches, $this->flags);
        $this->pos = 0;
        $this->valid = $this->ensureIndex(0); // prime first element lazily (or eof)
    }

    // --- iterator internals / memoization ---
    /** @var array<int|string,mixed> */
    private array $buffer = [];
    private int $pos;          // current numeric index over buffer keys
    private bool $done = false;    // true when the generator is fully drained
    private bool $valid;   // iterator::valid()
    private ?Generator $gen;

    // -- Iterator --

    public function current(): mixed
    {
        $key = $this->keyAt($this->pos);
        return $key !== null ? $this->buffer[$key] : null;
    }

    public function key(): string|int|null
    {
        return $this->keyAt($this->pos);
    }

    public function next(): void
    {
        $this->pos++;
        $this->valid = $this->ensureIndex($this->pos);
    }

    public function rewind(): void
    {
        $this->pos = 0;
        // No generator rewind! We rely on memoized buffer for earlier items.
        $this->valid = $this->ensureIndex(0);
    }

    public function valid(): bool
    {
        return $this->valid;
    }

    // -- ArrayAccess/Countable/JsonSerializable force materialization --

    public function offsetExists(mixed $offset): bool
    {
        $this->materialize();
        return array_key_exists($offset, $this->buffer);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $this->materialize();
        return $this->buffer[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new ImmutableException(self::class);
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new ImmutableException(self::class);
    }

    public function count(): int
    {
        $this->materialize();
        return count($this->buffer);
    }

    public function toArray(): array
    {
        $this->materialize();
        return $this->buffer;
    }

    public function jsonSerialize(): stdClass
    {
        return (object)$this->toArray();
    }

    // --- helpers ---

    private function materialize(): void
    {
        if ($this->done) return;
        // Drain remaining generator into buffer
        while ($this->ensureIndex(count($this->buffer))) { /* keep pulling */ }
    }

    private function keyAt(int $pos): int|string|null
    {
        // Get the N-th key from buffer (stable order)
        $keys = array_keys($this->buffer);
        return $keys[$pos] ?? null;
    }

    /**
     * Ensure the buffer has an element at numeric position $n.
     * Returns true if it exists (after possibly pulling more from the generator).
     */
    private function ensureIndex(int $n): bool
    {
        // If we already have >= n+1 elements, done.
        if (count($this->buffer) > $n) return true;
        if ($this->done) return false;

        // Pull until we have n+1 elements or generator ends
        while (count($this->buffer) <= $n ) {
            if ($this->gen === null) { $this->done = true; break; }
            if (!$this->gen->valid()) { $this->gen = null; $this->done = true; break; }

            $k = $this->gen->key();
            $v = $this->gen->current();
            $this->buffer[$k] = $v;
            $this->gen->next();
        }

        return count($this->buffer) > $n;
    }

    private function validateFlags(int $flags): void
    {
        if ($flags & ~ResultTypes::ALL_FLAGS) {
            throw new InvalidArgumentException('Unknown flags passed to Result.');
        }

        $typeBits = $flags & ResultTypes::TYPE_MASK;
        if ($typeBits === 0) {
            throw new InvalidArgumentException('No result type specified.');
        }

        $strict = (bool) ($flags & ResultTypes::STRICT);
        if ($strict && $typeBits === ResultTypes::TYPE_MASK) {
            throw new InvalidArgumentException('STRICT cannot be combined with BOTH.');
        }
    }

    // --- generator factory & producers ---

    private function buildIterable(array $matches, int $flags): Generator
    {
        $typeBits = $flags & ResultTypes::TYPE_MASK;
        $strict   = (bool) ($flags & ResultTypes::STRICT);
        $isBoth   = ($typeBits === ResultTypes::BOTH);

        if ($typeBits & ResultTypes::INDEXED) {
            yield from $this->getIndexedResult($matches, $strict);
            if (!$isBoth) return;
        }

        if ($typeBits & ResultTypes::ASSOCIATIVE) {
            yield from $this->getAssociativeResult($matches, $strict);
        }
    }

    private function getIndexedResult(array $matches, bool $strict): Generator
    {
        $i = 0;
        foreach ($matches as $key => $value) {
            if ($strict && !is_int($key)) continue;
            yield $i++ => $value; // reindex sequentially
        }
    }

    private function getAssociativeResult(array $matches, bool $strict): Generator
    {
        foreach ($matches as $key => $value) {
            if ($strict && (is_numeric($key) || !is_string($key))) continue;
            yield is_numeric($key) ? ('result_' . $key) : $key => $value;
        }
    }
}
