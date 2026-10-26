<?php

declare(strict_types=1);

namespace WilyCode\Filters\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WilyCode\Filters\TypeFilter;
use WilyCode\Filters\ValueType;

final class TypeFilterTest extends TestCase
{
    public function testConvertsValidRequestScalars(): void
    {
        self::assertSame(12, new TypeFilter(ValueType::Integer)->getFilteredValue('12'));
        self::assertSame(1.5, new TypeFilter(ValueType::Float)->getFilteredValue('1.5'));
        self::assertTrue(new TypeFilter(ValueType::Boolean)->getFilteredValue('yes'));
        self::assertSame([1, 2], new TypeFilter(ValueType::Integer, each: true)->getFilteredValue(['1', '2']));
    }

    public function testRejectsInvalidConversionInsteadOfSilentlyReturningZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TypeFilter(ValueType::Integer)->getFilteredValue('invalid');
    }
}
