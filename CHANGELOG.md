# Changelog

## [3.0.0] - 2026-11-27

### Added
- A `yield` inside the closure given to `Result::gen()` has the `Ok` type of the Result it takes. PHPStan reads it as `mixed` without the extension. A `yield` in a nested closure or in any other generator is left alone.
- `unwrapOrThrow()` is checked. It passes while every possible error is a `Throwable`. For any other error type it is `result.unsafeMethodCall`, because the call then throws a `RuntimeException` the way `unwrap()` does.

### Changed
- Works with brimmar/phpresult 4, where `Result` is the class `Brimmar\PhpResult\Result` and `Option` is `Brimmar\PhpOption\Option`. Composer refuses older versions of that package, because the extension would find no `Result` in them and report nothing.
- `isOk()` and `isErr()` narrow to `Ok<T, never>` and `Err<never, E>`. An `Err` narrowed this way is accepted as a `Result` with any other success type, so `if ($result->isErr()) { return $result; }` passes.

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

[3.0.0]: https://github.com/brimmar/phpstan-result-type/compare/v2.0.10...v3.0.0
[2.0.2]: https://github.com/brimmar/phpstan-result-type/compare/v2.0.1...v2.0.2
[2.0.1]: https://github.com/brimmar/phpstan-result-type/compare/v2.0.0...v2.0.1
[2.0.0]: https://github.com/brimmar/phpstan-result-type/compare/v1.0.5...v2.0.0
