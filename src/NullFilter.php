<?php

declare(strict_types=1);

namespace WilyCode\Filters;

use WilyCode\Filter\FilterInterface;

class NullFilter implements FilterInterface
{
    public function getFilteredValue(mixed $value): mixed
    {
        return $value === '' || $value === null
            ? null
            : $value;
    }
}
