# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [0.1.0] - 2026-10-26

### Added

- Ready-to-use filters: `TrimFilter`, `NullFilter`, `StringCaseFilter`, `TypeFilter`, `ArrayItemsFilter` and `SlugFilter`.
- UTF-8 string case conversion, transliteration of Polish characters and configurable scalar conversions.
- Support for applying filters to array items while retaining their original keys.
- Filter cloning behavior that isolates nested mutable filter instances.
- PHPUnit tests, Composer integration and GitHub Actions on PHP 8.4 and 8.5.

### Behavior and safeguards

- Preserve `null` and empty string input for non-boolean scalar conversions so form validators can handle missing values.
- Normalize missing checkbox-like boolean values to `false`.
- Reject invalid scalar conversions, out-of-range integers and non-finite floating-point results.
- Preserve intentionally repeated internal hyphens during slug generation.
- Reject malformed UTF-8 before processing slugs.
- Transliterate Latin diacritics using a deterministic map (including composed and decomposed accents), avoiding locale-dependent `iconv` output on macOS.
