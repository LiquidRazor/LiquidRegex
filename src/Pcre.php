<?php

namespace LiquidRazor\Regex;

use function preg_match;

use function sprintf;

use const PCRE_JIT_SUPPORT;
use const PCRE_VERSION;
use const PCRE_VERSION_MAJOR;
use const PCRE_VERSION_MINOR;

final readonly class Pcre
{
    public static function versionString(): string
    {
        return PCRE_VERSION;
    }

    public static function version(): string
    {
        return sprintf("%s.%s", self::majorVersion(), self::minorVersion());
    }

    public static function majorVersion(): int
    {
        return PCRE_VERSION_MAJOR;
    }


    public static function minorVersion(): int
    {
        return PCRE_VERSION_MINOR;
    }

    public static function isJitSupported(): bool
    {
        return (bool)PCRE_JIT_SUPPORT;
    }

    public static function isValid(?string $pattern): bool
    {
        if (null === $pattern) {
            return false;
        }

        return (bool)preg_match("/^\/.+\/[a-z]*$/i", $pattern);
    }
}
