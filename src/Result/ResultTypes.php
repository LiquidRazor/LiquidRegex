<?php

declare(strict_types=1);

namespace LiquidRazor\Regex\Result;

final class ResultTypes
{
    // Type bits
    public const int INDEXED     = 1 << 0; // 0001
    public const int ASSOCIATIVE = 1 << 1; // 0010
    public const int BOTH        = self::INDEXED | self::ASSOCIATIVE; // 0011 (convenience)

    // Modifiers
    public const int STRICT      = 1 << 3; // 1000

    // Helpers
    public const int TYPE_MASK = self::INDEXED | self::ASSOCIATIVE; // 0011
    public const int ALL_FLAGS = self::TYPE_MASK | self::STRICT;    // 1011
}
