<?php

namespace LiquidRazor\Regex\Lib;

enum Flags: int
{
    case Default = 0;
    case OffsetCapture = 256;
    case UnmatchedAsNull = 512;
}
