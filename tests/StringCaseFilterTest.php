<?php

declare(strict_types=1);

namespace WilyCode\Filters\Tests;

use PHPUnit\Framework\TestCase;
use WilyCode\Filters\StringCase;
use WilyCode\Filters\StringCaseFilter;

final class StringCaseFilterTest extends TestCase
{
    public function test_lowercases_utf8_text_and_preserves_non_strings(): void
    {
        $filter = new StringCaseFilter(StringCase::Lower);

        self::assertSame('https://example.com:8080', $filter->getFilteredValue('HTTPS://EXAMPLE.COM:8080'));
        self::assertSame('żółć', $filter->getFilteredValue('ŻÓŁĆ'));
        self::assertSame(12, $filter->getFilteredValue(12));
        self::assertNull($filter->getFilteredValue(null));
    }

    public function test_uppercases_utf8_text_and_preserves_non_strings(): void
    {
        $filter = new StringCaseFilter(StringCase::Upper);

        self::assertSame('EXAMPLE.COM', $filter->getFilteredValue('example.com'));
        self::assertSame('ŻÓŁĆ', $filter->getFilteredValue('żółć'));
        self::assertSame(false, $filter->getFilteredValue(false));
        self::assertSame([], $filter->getFilteredValue([]));
    }
}
