<?php

declare(strict_types=1);

namespace WilyCode\Filters\Tests;

use PHPUnit\Framework\TestCase;
use WilyCode\Filters\SlugFilter;

final class SlugFilterTest extends TestCase
{
    public function testConvertsTitlesToAsciiSlugs(): void
    {
        $filter = new SlugFilter();

        self::assertSame('my-first-article', $filter->getFilteredValue(' My First Article! '));
        self::assertSame('zazolc-gesla-jazn', $filter->getFilteredValue('Zażółć gęślą jaźń'));
        self::assertSame('lodz', $filter->getFilteredValue('Łódź'));
        self::assertSame('cafe-creme', $filter->getFilteredValue('Café crème'));
        self::assertSame('article-2026', $filter->getFilteredValue('Article_2026'));
    }


    public function testAccentedLatinTextIsTransliteratedConsistently(): void
    {
        $filter = new SlugFilter();

        self::assertSame('cafe-creme', $filter->getFilteredValue('Café crème'));
        self::assertSame('cafe-creme', $filter->getFilteredValue("Cafe\u{0301} cre\u{0300}me"));
        self::assertSame('francois', $filter->getFilteredValue('François'));
        self::assertSame('dusseldorf', $filter->getFilteredValue('Düsseldorf'));
        self::assertSame('strasse', $filter->getFilteredValue('Straße'));
        self::assertSame('smorrebrod', $filter->getFilteredValue('Smørrebrød'));
        self::assertSame('cesky-krumlov', $filter->getFilteredValue('Český Krumlov'));
        self::assertSame('istanbul', $filter->getFilteredValue('İstanbul'));
        self::assertSame('aether-oeuvre', $filter->getFilteredValue('Æther Œuvre'));
    }

    public function testAccentedSlugsDoNotDependOnProcessLocale(): void
    {
        $filter = new SlugFilter();
        $originalLocale = setlocale(LC_CTYPE, '0');

        try {
            setlocale(LC_CTYPE, 'C');
            self::assertSame('cafe-creme', $filter->getFilteredValue('Café crème'));
            self::assertSame('zazolc-gesla-jazn', $filter->getFilteredValue('Zażółć gęślą jaźń'));
        } finally {
            if ($originalLocale !== false) {
                setlocale(LC_CTYPE, $originalLocale);
            }
        }
    }

    public function testPreservesIntentionalRepeatedHyphens(): void
    {
        $filter = new SlugFilter();

        self::assertSame('my--article', $filter->getFilteredValue('my--article'));
        self::assertSame('my---article', $filter->getFilteredValue('my---article'));
        self::assertSame('my-article', $filter->getFilteredValue('my - article'));
    }

    public function testTrimsPunctuationAndHandlesEmptyInputs(): void
    {
        $filter = new SlugFilter();

        self::assertSame('hello-world', $filter->getFilteredValue('!!! Hello, World !!!'));
        self::assertSame('0', $filter->getFilteredValue('0'));
        self::assertSame('', $filter->getFilteredValue('   '));
        self::assertSame('', $filter->getFilteredValue('---'));
        self::assertSame(null, $filter->getFilteredValue(null));
        self::assertSame(42, $filter->getFilteredValue(42));
        self::assertSame(['Hello World'], $filter->getFilteredValue(['Hello World']));
    }
}
