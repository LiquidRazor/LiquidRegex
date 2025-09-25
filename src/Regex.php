<?php

namespace LiquidRazor\Regex;

use LiquidRazor\Regex\Exception\Exception;
use LiquidRazor\Regex\Result\ResultTypes;

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
        Lib\Flags|int $flags = Lib\Flags::Default,
        int $offset = 0,
        int $resultFlags = ResultTypes::BOTH,
    ): Result\Matches\Result {
        if ($flags instanceof Lib\Flags) {
            $flags = $flags->value;
        }

        error_clear_last();
        $result = preg_match($this->pattern, $haystack, $matches, $offset, $flags);
        if (false === $result) {
            throw Exception::fromInternalError();
        }

        return new Result\Matches\Result(
            pattern: $this->pattern,
            haystack: $haystack,
            didMatch: (bool)$result,
            matches: $matches,
            flags: $resultFlags,
        );
    }

    public function replace(
        string|array $haystack,
        string|array|callable $replacement,
        int $limit = -1,
    ): Result\Replace\Result {
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
            throw Exception::fromInternalError();
        }

        return new Result\Replace\Result(
            pattern: $this->pattern,
            count: $count,
            replaced: $result,
        );
    }

    public function replaceNamed(string $haystack, string $template, int $limit = -1): Result\Replace\Result
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
            throw Exception::fromInternalError();
        }

        return new Result\Replace\Result(
            pattern: $this->pattern,
            count: $count,
            replaced: $result,
        );
    }
}
