<?php

declare(strict_types=1);

namespace WilyCode\Filters;

enum ValueType
{
    case String;
    case Integer;
    case Float;
    case Boolean;
}
