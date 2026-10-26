<?php

declare(strict_types=1);

namespace WilyCode\Filters\Tests;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use WilyCode\Filters\SlugFilter;

final class SlugFilterRobustnessTest extends TestCase
{
    public function testSlugRetainsNumbersAndDoesNotCollapseIntentionalHyphens(): void
    {
        $filter = new SlugFilter();
        self::assertSame('chapter-001--part-2', $filter->getFilteredValue('Chapter 001 -- Part 2'));
    }

    public function testMalformedUtf8ThrowsInvalidArgumentException(): void
    {
        $filter = new SlugFilter();
        $this->expectException(InvalidArgumentException::class);
        $filter->getFilteredValue("Hello \xFF world");
    }
}
