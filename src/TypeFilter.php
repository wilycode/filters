<?php

declare(strict_types=1);

namespace WilyCode\Filters;

use WilyCode\Filter\FilterInterface;
use InvalidArgumentException;
use Stringable;

/** Strict conversion of request values to one scalar type. */
final class TypeFilter implements FilterInterface
{
    public function __construct(
        private readonly ValueType $type,
        private readonly bool $each = false,
    ) {
    }

    /** @return string|int|float|bool|null|array<int|string, string|int|float|bool|null> */
    public function getFilteredValue(mixed $value): string|int|float|bool|array|null
    {
        if (!$this->each) {
            return $this->cast($value);
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException('TypeFilter with each=true expects an array.');
        }

        return array_map($this->cast(...), $value);
    }

    private function cast(mixed $value): string|int|float|bool|null
    {
        // Preserve empty form values so required/optional validators can handle them.
        // Boolean is deliberately different: an unchecked checkbox maps to false.
        if ($this->type !== ValueType::Boolean && ($value === null || $value === '')) {
            return $value;
        }

        return match ($this->type) {
            ValueType::String => $this->toString($value),
            ValueType::Integer => $this->toInteger($value),
            ValueType::Float => $this->toFloat($value),
            ValueType::Boolean => $this->toBoolean($value),
        };
    }

    private function toString(mixed $value): string
    {
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value) || is_bool($value) || $value instanceof Stringable) {
            return (string) $value;
        }

        throw new InvalidArgumentException('Value cannot be converted to string.');
    }

    private function toInteger(mixed $value): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (!is_string($value) || preg_match('/^[+-]?\d+$/D', $value) !== 1) {
            throw new InvalidArgumentException('Value is not an integer.');
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT);
        if ($integer === false) {
            throw new InvalidArgumentException('Integer value is outside the supported range.');
        }

        return $integer;
    }

    private function toFloat(mixed $value): float
    {
        if (is_int($value) || is_float($value)) {
            $float = (float) $value;
            if (!is_finite($float)) {
                throw new InvalidArgumentException('Value is not a finite float.');
            }
            return $float;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException('Value is not a float.');
        }

        $float = filter_var($value, FILTER_VALIDATE_FLOAT);
        if ($float === false || !is_finite($float)) {
            throw new InvalidArgumentException('Value is not a finite float.');
        }

        return $float;
    }

    private function toBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        $boolean = filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if ($boolean === null) {
            throw new InvalidArgumentException('Value is not a boolean.');
        }

        return $boolean;
    }
}
