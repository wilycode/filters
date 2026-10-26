<?php

declare(strict_types=1);

namespace WilyCode\Filters;

use WilyCode\Filter\FilterInterface;
use RuntimeException;

class TrimFilter implements FilterInterface
{
    public const int TYPE_LEFT = -1;
    public const int TYPE_BOTH = 0;
    public const int TYPE_RIGHT = 1;

    private readonly int $type;
    private string $characters = " \n\r\t\v\x00";

    public function __construct(int $type = self::TYPE_BOTH)
    {
        $this->type = $type;
    }

    public function getFilteredValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        return match ($this->type) {
            self::TYPE_BOTH => trim($value, $this->characters),
            self::TYPE_LEFT => ltrim($value, $this->characters),
            self::TYPE_RIGHT => rtrim($value, $this->characters),
            default => throw new RuntimeException('Trim type does not exist.'),
        };
    }
}
