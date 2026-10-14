# Changelog

## [Unreleased]

## [2.0.2] - 2026-10-14

### Fixed
- `src` is clean at PHPStan level max, and CI runs that analysis.
- `flatten()`, `transpose()`, unused results, and unsafe calls read `T` and `E` with `getTemplateType()`.
- `map()` and `mapErr()` no longer reference the removed statement `throw` node.
- Unions of Result are checked. `Ok<int, string>|Err<int, string>` now reports `result.flatten`, `result.unsafeMethodCall`, and `result.mapException`. Those calls were skipped before.

## [2.0.1] - 2026-10-14

### Fixed
- Removed the `version` field from `composer.json`. This is the first 2.x release Composer can install. v2.0.0 never reached Packagist because that field was still `1.0.0`.

## [2.0.0] - 2026-10-14

### Changed
- Requires PHP 8.5 and PHPStan 2.3.
- `isOk()` and `isErr()` narrow to `Ok<T, E>` and `Err<T, E>` from brimmar/phpresult. A custom Result implementation is narrowed to those classes as well.
- `result.matchArgsOrder` is gone. Ignore `result.matchNamedArgs` instead.
- `result.unused`, `result.flatten`, and `result.transpose` are new errors.

[Unreleased]: https://github.com/brimmar/phpstan-result-type/compare/v2.0.2...HEAD
[2.0.2]: https://github.com/brimmar/phpstan-result-type/compare/v2.0.1...v2.0.2
[2.0.1]: https://github.com/brimmar/phpstan-result-type/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/brimmar/phpstan-result-type/compare/v1.0.5...v2.0.0
