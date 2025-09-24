<?php

namespace LiquidRazor\Regex;

use function error_clear_last;
use function is_callable;
use function preg_match;
use function preg_replace;
use function preg_replace_callback;

final class Regex
{
    private static array $compiled = [];

    public static function compiled(string $pattern): self
    {
        return self::$compiled[$pattern] ??= new self($pattern);
    }

    public function __construct(
        private readonly string $pattern,
    ) {}

    public function isValid(): bool
    {
        return Pcre::isValid($this->pattern);
    }

    public function test(string $haystack): bool
    {
        return (bool)preg_match($this->pattern, $haystack);
    }

    public function match(
        string $haystack,
        Match_\Flags|int $flags = Match_\Flags::Default,
        int $offset = 0,
    ): Match_\Result {
        if ($flags instanceof Match_\Flags) {
            $flags = $flags->value;
        }

        error_clear_last();
        $result = preg_match($this->pattern, $haystack, $matches, $offset, $flags);
        if (false === $result) {
            throw Match_\Exception::fromInternalError();
        }

        return new Match_\Result(
            pattern: $this->pattern,
            haystack: $haystack,
            matches: $matches,
            didMatch: (bool)$result,
        );
    }

    public function replace(
        string|array $haystack,
        string|array|callable $replacement,
        int $limit = -1,
    ): Replace\Result {
        error_clear_last();

        if (is_callable($replacement)) {
            $result = preg_replace_callback(
                pattern: $this->pattern,
                callback: $replacement,
                subject: $haystack,
                limit: $limit,
                count: $count,
            );
        } else {
            $result = preg_replace(
                pattern: $this->pattern,
                replacement: $replacement,
                subject: $haystack,
                limit: $limit,
                count: $count,
            );
        }
        if (false === $result) {
            throw Replace\Exception::fromInternalError();
        }

        return new Replace\Result(
            pattern: $this->pattern,
            count: $count,
            replaced: $result,
        );
    }

    public function replaceNamed(string $haystack, string $template, int $limit = -1): Replace\Result
    {
        error_clear_last();
        $result = preg_replace_callback(
            pattern: $this->pattern,
            callback: static fn(array $matches): string
                => preg_replace_callback(
                '/{(\w+)}/',
                static fn(array $m) => $matches[$m[1]] ?? '??',
                $template,
            ),
            subject: $haystack,
            limit: $limit,
            count: $count,
        );

        if (false === $result) {
            throw Replace\Exception::fromInternalError();
        }

        return new Replace\Result(
            pattern: $this->pattern,
            count: $count,
            replaced: $result,
        );
    }
}
