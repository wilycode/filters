<?php

declare(strict_types=1);

namespace WilyCode\Filters\Tests;

use PHPUnit\Framework\TestCase;
use WilyCode\Filter\FilterInterface;
use WilyCode\Filters\ArrayItemsFilter;
use WilyCode\Filters\NullFilter;
use WilyCode\Filters\TrimFilter;

final class BasicFiltersTest extends TestCase
{
    public function testTrimFilterSupportsBothSidesAndDirectionalTrimming(): void
    {
        self::assertSame('name', (new TrimFilter())->getFilteredValue(" \tname\n"));
        self::assertSame('name ', (new TrimFilter(TrimFilter::TYPE_LEFT))->getFilteredValue(' name '));
        self::assertSame(' name', (new TrimFilter(TrimFilter::TYPE_RIGHT))->getFilteredValue(' name '));
        self::assertSame(0, (new TrimFilter())->getFilteredValue(0));
        self::assertSame([' text '], (new TrimFilter())->getFilteredValue([' text ']));
    }

    public function testNullFilterNormalizesOnlyEmptyStringsAndNull(): void
    {
        $filter = new NullFilter();

        self::assertNull($filter->getFilteredValue(''));
        self::assertNull($filter->getFilteredValue(null));
        self::assertSame('0', $filter->getFilteredValue('0'));
        self::assertSame(0, $filter->getFilteredValue(0));
        self::assertSame(false, $filter->getFilteredValue(false));
        self::assertSame(' ', $filter->getFilteredValue(' '));
    }

    public function testArrayItemsFilterAppliesFiltersInOrderAndKeepsKeys(): void
    {
        $filter = new ArrayItemsFilter(new TrimFilter(), new NullFilter());

        self::assertSame(
            ['first' => 'hello', 'empty' => null, 5 => '0'],
            $filter->getFilteredValue(['first' => ' hello ', 'empty' => '   ', 5 => '0']),
        );
        self::assertSame(' unchanged ', $filter->getFilteredValue(' unchanged '));
        self::assertCount(2, $filter->getFilters());
    }

    public function testClonedArrayItemsFilterDoesNotShareMutableInnerFilters(): void
    {
        $prefixFilter = new class implements FilterInterface {
            public string $prefix = 'old:';

            public function getFilteredValue(mixed $value): mixed
            {
                return is_string($value) ? $this->prefix . $value : $value;
            }
        };

        $original = new ArrayItemsFilter($prefixFilter);
        $copy = clone $original;
        $prefixFilter->prefix = 'new:';

        self::assertSame(['new:a'], $original->getFilteredValue(['a']));
        self::assertSame(['old:a'], $copy->getFilteredValue(['a']));
    }
}
