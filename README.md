# PHPStan rules for Result

[![CI](https://github.com/brimmar/phpstan-result-type/actions/workflows/ci.yml/badge.svg)](https://github.com/brimmar/phpstan-result-type/actions/workflows/ci.yml)
[![Latest Stable Version](https://img.shields.io/packagist/v/brimmar/phpstan-rustlike-result-extension)](https://packagist.org/packages/brimmar/phpstan-rustlike-result-extension)
[![License](https://img.shields.io/packagist/l/brimmar/phpstan-rustlike-result-extension)](https://packagist.org/packages/brimmar/phpstan-rustlike-result-extension)

A PHPStan extension for the `Result` type of [brimmar/phpresult](https://github.com/brimmar/phpresult). It needs phpresult 4, PHPStan 2.3 and PHP 8.5.

PHPStan reads the generics of `Result` from phpresult itself. This extension adds the checks below. The Option checks are in [brimmar/phpstan-rustlike-option-extension](https://github.com/brimmar/phpstan-option-type), and each extension works without the other.

## Install

```bash
composer require --dev brimmar/phpstan-rustlike-result-extension
```

```neon
includes:
    - vendor/brimmar/phpstan-rustlike-result-extension/extension.neon
```

[phpstan/extension-installer](https://github.com/phpstan/extension-installer) adds that include for you.

## Checks

`unwrap()`, `expect()`, and `intoOk()` throw on `Err`. `unwrapErr()`, `expectErr()`, and `intoErr()` throw on `Ok`. The call is `result.unsafeMethodCall` while that side is still possible. An `Ok` value passes the success calls, and an `Err` value passes the failure calls. `isOk()` and `isErr()` narrow a `Result`, so the matching call is accepted in that branch and after an early return. `Result<T, never>` accepts the success calls. `Result<never, E>` accepts the failure calls. The contained type stays intact, so `unwrap()` on `Result<int, string>` is still `int` after `isErr()` returns early.

```php
/** @param Result<int, string> $result */
function id(Result $result): int
{
    return $result->unwrap(); // result.unsafeMethodCall
}
```

```php
/** @param Result<int, string> $result */
function id(Result $result): int
{
    if ($result->isErr()) {
        return 0;
    }

    return $result->unwrap();
}
```

The narrowed types are `Ok<T, never>` and `Err<never, E>`. An `Err` narrowed this way fits a function that returns a `Result` with another success type, so the early return needs no conversion.

```php
/**
 * @param Result<int, string> $id
 * @return Result<User, string>
 */
function load(Result $id): Result
{
    if ($id->isErr()) {
        return $id;
    }

    return findUser($id->unwrap());
}
```

`isOkAnd()` and `isErrAnd()` narrow the true branch only. `unwrapErr()` in the else of `isOkAnd()` is still `result.unsafeMethodCall`.

`unwrapOrThrow()` throws the error itself, so the extension accepts the call while every possible error is a `Throwable`. For any other error type it throws a `RuntimeException` the way `unwrap()` does, and the call is `result.unsafeMethodCall`.

```php
/** @param Result<User, UserNotFound> $result */
function user(Result $result): User
{
    return $result->unwrapOrThrow();
}

/** @param Result<User, string> $result */
function user(Result $result): User
{
    return $result->unwrapOrThrow(); // result.unsafeMethodCall
}
```

The extension also types the short step. Inside the closure given to `Result::gen()`, a plain `yield $result` has the `Ok` type of that Result. PHPStan reads it as `mixed` without the extension. A `yield` in any other generator keeps its own type.

```php
Result::gen(function () {
    $user = yield $this->findUser(1); // User
    $order = yield $this->lastOrder($user); // Order

    return $order->total;
});
```

`map()` and `mapErr()` transform a value. A `throw` inside the callback leaves the Result, and the extension reports it as `result.mapException`. Return an `Err` from `andThen` when the failure belongs in the Result.

```php
$result->map(fn (int $id) => $id > 0 ? $id : throw new RuntimeException('bad id')); // result.mapException

$result->andThen(fn (int $id) => $id > 0 ? new Ok($id) : new Err('bad id'));
```

`match()` accepts `Ok` and `Err` in either order. A positional call or a different name is `result.matchNamedArgs`.

```php
$result->match(
    Err: fn ($error) => $error,
    Ok: fn ($value) => $value,
);
```

`flatten()` and `transpose()` are reported when the contained type is definitely wrong. `result.flatten` wants `Result<Result<T, E>, E>`. `result.transpose` wants `Result<Option<T>, E>`.

```php
/** @param Result<int, string> $result */
function collapsed(Result $result): mixed
{
    return $result->flatten(); // result.flatten
}
```

A dropped `Result` is `result.unused`. `new Ok(1);` counts. `Result<int, never>` does not, because that error cannot exist. `Result<Option<int>, never>` still does, because the option inside may be `None`. Methods that return a `Result` already use `#[\NoDiscard]`. Ignoring one is `method.resultDiscarded`. `(void)` acknowledges that drop. On a function without the attribute, the same cast is `function.inVoidCast`, so assign the call to `$_` instead.

## License

MIT. See [LICENSE.md](LICENSE.md).
