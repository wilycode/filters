<?php

declare(strict_types=1);

namespace WilyCode\Filters\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WilyCode\Filters\TypeFilter;
use WilyCode\Filters\ValueType;

final class TypeFilterConversionEdgeTest extends TestCase
{
    public function testKeepsArrayKeysWhenCastingEachValue(): void
    {
        $filter = new TypeFilter(ValueType::Integer, each: true);

        self::assertSame(['first' => 5, 8 => 6, 'empty' => null], $filter->getFilteredValue([
            'first' => '5', 8 => '6', 'empty' => null,
        ]));
    }

    public function testIntegerOverflowsAreRejected(): void
    {
        $filter = new TypeFilter(ValueType::Integer);
        $this->expectException(InvalidArgumentException::class);
        $filter->getFilteredValue((string) PHP_INT_MAX . '0');
    }

    public function testNonFiniteNumericStringIsRejected(): void
    {
        $filter = new TypeFilter(ValueType::Float);
        $this->expectException(InvalidArgumentException::class);
        $filter->getFilteredValue('1e9999');
    }

    public function testArrayModeRequiresArrayInput(): void
    {
        $filter = new TypeFilter(ValueType::Integer, each: true);
        $this->expectException(InvalidArgumentException::class);
        $filter->getFilteredValue('42');
    }

    public function testInvalidBooleanStringIsRejected(): void
    {
        $filter = new TypeFilter(ValueType::Boolean);
        $this->expectException(InvalidArgumentException::class);
        $filter->getFilteredValue('maybe');
    }
}
