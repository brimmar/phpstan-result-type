# Changelog

## [Unreleased]

## [2.0.0] - 2026-10-13

### Changed
- Requires PHP 8.5 and PHPStan 2.3.
- `isOk()` and `isErr()` narrow to `Ok<T, E>` and `Err<T, E>` from brimmar/phpresult. A custom Result implementation is narrowed to those classes as well.
- `result.matchArgsOrder` is gone. Ignore `result.matchNamedArgs` instead.
- `result.unused`, `result.flatten`, and `result.transpose` are new errors.

[Unreleased]: https://github.com/brimmar/phpstan-result-type/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/brimmar/phpstan-result-type/compare/v1.0.5...v2.0.0
