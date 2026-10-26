<?php

declare(strict_types=1);

namespace WilyCode\Filters\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WilyCode\Filters\NullFilter;
use WilyCode\Filters\TypeFilter;
use WilyCode\Filters\ValueType;

final class TypeFilterEmptyValuesTest extends TestCase
{
    public function testMissingFormValuesArePreservedForValidation(): void
    {
        foreach ([ValueType::Integer, ValueType::Float, ValueType::String] as $type) {
            $filter = new TypeFilter($type);
            self::assertSame('', $filter->getFilteredValue(''));
            self::assertNull($filter->getFilteredValue(null));
        }
    }

    public function testOptionalIntegerWorksAfterNullNormalization(): void
    {
        $nullFilter = new NullFilter();
        $integerFilter = new TypeFilter(ValueType::Integer);

        self::assertNull($integerFilter->getFilteredValue($nullFilter->getFilteredValue('')));
        self::assertSame(42, $integerFilter->getFilteredValue($nullFilter->getFilteredValue('42')));
    }

    public function testEachModePreservesEmptyItemsAndConvertsFilledItems(): void
    {
        $filter = new TypeFilter(ValueType::Integer, each: true);

        self::assertSame([7, '', null, 0], $filter->getFilteredValue(['7', '', null, '0']));
    }

    public function testCheckboxBooleanConversionRemainsUnchanged(): void
    {
        $filter = new TypeFilter(ValueType::Boolean);

        self::assertFalse($filter->getFilteredValue(''));
        self::assertFalse($filter->getFilteredValue(null));
        self::assertFalse($filter->getFilteredValue('off'));
        self::assertTrue($filter->getFilteredValue('on'));
    }

    public function testNonEmptyInvalidNumberStillThrows(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new TypeFilter(ValueType::Integer)->getFilteredValue('not a number');
    }
}
