<?php

declare(strict_types=1);

namespace WilyCode\Filters;

use WilyCode\Filter\FilterInterface;
final class StringCaseFilter implements FilterInterface
{
    public function __construct(
        private readonly StringCase $case,
    ) {
    }

    public function getFilteredValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return match ($this->case) {
            StringCase::Lower => mb_strtolower($value, 'UTF-8'),
            StringCase::Upper => mb_strtoupper($value, 'UTF-8'),
        };
    }
}
