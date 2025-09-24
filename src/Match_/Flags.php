<?php

namespace LiquidRazor\Regex\Match_;

enum Flags: int
{
    case Default = 0;
    case OffsetCapture = 256;
    case UnmatchedAsNull = 512;
}
