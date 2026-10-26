<?php

declare(strict_types=1);

namespace WilyCode\Filters\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WilyCode\Filters\TypeFilter;
use WilyCode\Filters\ValueType;

final class TypeFilterNonFiniteTest extends TestCase
{
    public function testInfiniteFloatIsRejected(): void
    {
        $filter = new TypeFilter(ValueType::Float);
        $this->expectException(InvalidArgumentException::class);
        $filter->getFilteredValue(INF);
    }
}
