# WilyCode Filters

[![PHP 8.4+](https://img.shields.io/badge/PHP-8.4%2B-777BB4)](https://www.php.net/)
[![MIT License](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![Tests](https://github.com/wilycode/filters/actions/workflows/tests.yml/badge.svg)](https://github.com/wilycode/filters/actions/workflows/tests.yml)

**Small, framework-independent filters for normalizing values in PHP 8.4+.** Built on the [`wilycode/filter`](https://github.com/wilycode/filter) contract. Use them individually, chain filters over array items, or integrate them into your own forms and input-processing workflows.

> **WilyCode PHP Ecosystem**
>
> Independent, reusable PHP libraries with their own repositories, releases and Composer packages.
>
> **[Explore the WilyCode PHP Ecosystem →](https://github.com/wilycode/wilycode)**

## Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Quick start](#quick-start)
- [Available filters](#available-filters)
  - [Trimming and empty values](#trimming-and-empty-values)
  - [String case](#string-case)
  - [Slug generation](#slug-generation)
  - [Scalar type conversion](#scalar-type-conversion)
  - [Filtering array items](#filtering-array-items)
- [Custom filters](#custom-filters)
- [Development](#development)
- [License](#license)

## Requirements

- PHP **8.4+**
- PHP extensions: `filter`, `mbstring`
- `wilycode/filter` `^0.1` (installed automatically)

## Installation

```bash
composer require wilycode/filters
```

This package provides implementations; the smaller [`wilycode/filter`](https://github.com/wilycode/filter) package provides only the `FilterInterface` contract.

## Quick start

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use WilyCode\Filters\ArrayItemsFilter;
use WilyCode\Filters\NullFilter;
use WilyCode\Filters\TrimFilter;

$filter = new ArrayItemsFilter(new TrimFilter(), new NullFilter());

$result = $filter->getFilteredValue([
    'name' => ' Alice ',
    'nickname' => '   ',
    'quantity' => '0',
]);

// ['name' => 'Alice', 'nickname' => null, 'quantity' => '0']
```

Each filter implements `WilyCode\Filter\FilterInterface` and exposes `getFilteredValue(mixed $value): mixed`. Filters transform values; they **do not** validate that a value is acceptable for your application.

## Available filters

| Class | Purpose |
| --- | --- |
| `TrimFilter` | Remove leading, trailing or surrounding characters from strings. |
| `NullFilter` | Convert the empty string `''` to `null` (but preserve `'0'`, `0`, and `false`). |
| `StringCaseFilter` | Lowercase or uppercase UTF-8 strings. |
| `SlugFilter` | Produce lowercase ASCII URL slugs, including Polish transliteration. |
| `TypeFilter` | Convert scalars into strings, integers, floats or booleans. |
| `ArrayItemsFilter` | Apply a series of filters to each array value while preserving keys. |

### Trimming and empty values

```php
use WilyCode\Filters\NullFilter;
use WilyCode\Filters\TrimFilter;

(new TrimFilter())->getFilteredValue('  Alice  ');                         // 'Alice'
(new TrimFilter(TrimFilter::TYPE_LEFT))->getFilteredValue(' text ');    // 'text '
(new TrimFilter(TrimFilter::TYPE_RIGHT))->getFilteredValue(' text ');   // ' text'

(new NullFilter())->getFilteredValue('');      // null
(new NullFilter())->getFilteredValue('0');     // '0'
(new NullFilter())->getFilteredValue(false);   // false
```

`TrimFilter` leaves non-string values unchanged. `NullFilter` only converts `''` and already-null input into `null`. To treat strings consisting entirely of spaces as empty, trim **before** applying `NullFilter`.

`TrimFilter` accepts `TYPE_LEFT`, `TYPE_BOTH` (default) or `TYPE_RIGHT`.

### String case

```php
use WilyCode\Filters\StringCase;
use WilyCode\Filters\StringCaseFilter;

(new StringCaseFilter(StringCase::Lower))->getFilteredValue('ŻÓŁĆ'); // 'żółć'
(new StringCaseFilter(StringCase::Upper))->getFilteredValue('żółć'); // 'ŻÓŁĆ'
```

`StringCaseFilter` uses `mbstring` and leaves non-string input unchanged.

### Slug generation

```php
use WilyCode\Filters\SlugFilter;

$slug = new SlugFilter();

$slug->getFilteredValue(' My First Article! '); // 'my-first-article'
$slug->getFilteredValue('Zażółć gęślą jaźń');  // 'zazolc-gesla-jazn'
$slug->getFilteredValue('my--article');          // 'my--article'
```

The filter uses a built-in Latin-to-ASCII mapping, giving consistent results on macOS and Linux without relying on system locale or `iconv`. Common Western, Central and Eastern European Latin characters are supported; unsupported scripts are not transliterated. Whitespace and punctuation become separators, with leading/trailing hyphens removed. **Existing repeated internal hyphens are preserved** rather than collapsed.

Only string input is changed. Strings without usable characters return `''`. Invalid UTF-8 strings throw `InvalidArgumentException` rather than silently losing bytes. Slugs are not checked for uniqueness or validity; use a separate validator (for example, `SlugValidator` in the optional `wilycode/validators` package) and enforce uniqueness in your application if necessary.

### Scalar type conversion

```php
use WilyCode\Filters\TypeFilter;
use WilyCode\Filters\ValueType;

(new TypeFilter(ValueType::Integer))->getFilteredValue('42');        // 42
(new TypeFilter(ValueType::Float))->getFilteredValue('12.5');       // 12.5
(new TypeFilter(ValueType::Boolean))->getFilteredValue('yes');      // true
(new TypeFilter(ValueType::Boolean))->getFilteredValue('');         // false
(new TypeFilter(ValueType::Integer, each: true))
    ->getFilteredValue(['10', '20']);                               // [10, 20]
```

| `ValueType` | Behavior |
| --- | --- |
| `String` | Accept strings, numbers, booleans and `Stringable` objects. |
| `Integer` | Accept integers and integer-formatted strings within PHP's integer range. |
| `Float` | Accept finite integer/float inputs and valid finite numeric strings. |
| `Boolean` | Accept recognized boolean strings such as `yes`, `no`, `on`, `off`, `true`, `false`, `1`, and `0`. |

For string, integer and float conversion, `null` and `''` are left unchanged so later validators can handle optional or missing values. Boolean conversion intentionally treats missing/empty checkbox values as `false`.

Invalid non-empty conversions throw `InvalidArgumentException`, including integer overflow, non-finite floats and unrecognized boolean strings. Conversion is deliberately strict: for example, a leading-zero integer string such as `'01'` is not accepted by the underlying `FILTER_VALIDATE_INT` conversion.

With `each: true`, the input **must be an array**; keys are preserved and each value is converted independently. `each: true` does not replace a chain of different filters: use `ArrayItemsFilter` for that.

### Filtering array items

```php
use WilyCode\Filters\ArrayItemsFilter;
use WilyCode\Filters\NullFilter;
use WilyCode\Filters\TrimFilter;

$filter = new ArrayItemsFilter(new TrimFilter(), new NullFilter());

$filter->getFilteredValue([' first ', ' ', '0']);
// ['first', null, '0']
```

Filters run **in the order supplied** for each value. Array keys are preserved, and non-array values pass through unchanged. The `getFilters()` method returns the configured list. Cloning `ArrayItemsFilter` clones its nested filter objects so the cloned filter can be modified independently when those filters have mutable state.

## Custom filters

Use `FilterInterface` from the standalone `wilycode/filter` package:

```php
use WilyCode\Filter\FilterInterface;

final class PrefixFilter implements FilterInterface
{
    public function getFilteredValue(mixed $value): mixed
    {
        return is_string($value) ? 'item-' . $value : $value;
    }
}
```

You can use your own filters alongside the built-in filters in `ArrayItemsFilter` or form pipelines. This library has no dependency on `wilycode/form` or `wilycode/validators`.

## Development

```bash
composer install
composer validate --strict
composer test
```

The included GitHub Actions workflow tests PHP **8.4** and **8.5**. Test sources are kept in the Git repository but omitted from regular Composer distribution archives.

## License

MIT © 2026 Simon Piter. See [LICENSE](LICENSE).
