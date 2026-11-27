<?php

namespace Brimmar\PHPStan\Tests\Rules\Data;

use Brimmar\PhpResult\Err;
use Brimmar\PhpResult\Result;
use Brimmar\PhpResult\Ok;
use function PHPStan\Testing\assertType;

/** @param Result<int, string> $result */
function resultOkBranches(Result $result): void
{
    if ($result->isOk()) {
        assertType('Brimmar\PhpResult\Ok<int, *NEVER*>', $result);
    } else {
        assertType('Brimmar\PhpResult\Err<*NEVER*, string>', $result);
    }
}

/** @param Result<int, string> $result */
function resultErrBranches(Result $result): void
{
    if ($result->isErr()) {
        assertType('Brimmar\PhpResult\Err<*NEVER*, string>', $result);
    } else {
        assertType('Brimmar\PhpResult\Ok<int, *NEVER*>', $result);
    }
}

/** @param Result<int, string> $result */
function resultEarly(Result $result): void
{
    if ($result->isErr()) {
        return;
    }

    assertType('Brimmar\PhpResult\Ok<int, *NEVER*>', $result);
}

/** @param Result<int, string> $result */
function resultOkAnd(Result $result): void
{
    if ($result->isOkAnd(static fn (int $value): bool => $value > 0)) {
        assertType('Brimmar\PhpResult\Ok<int, never>', $result);
    } else {
        // Native assert-if-true removes Ok. The extension does not turn this branch into Err.
        assertType('Brimmar\PhpResult\Result~Brimmar\PhpResult\Ok<int, never><int, string>', $result);
    }
}

/** @param Result<int, string> $result */
function resultErrAnd(Result $result): void
{
    if ($result->isErrAnd(static fn (string $error): bool => $error === 'x')) {
        assertType('Brimmar\PhpResult\Err<never, string>', $result);
    } else {
        // Native assert-if-true removes Err. The extension does not turn this branch into Ok.
        assertType('Brimmar\PhpResult\Result~Brimmar\PhpResult\Err<never, string><int, string>', $result);
    }
}

/**
 * PHPStan runs a method type-specifying extension only when the receiver is one class.
 * Ok|Err stays a union here. The published phpresult asserts narrow that case natively.
 *
 * @param Ok<int, string>|Err<int, string> $result
 */
function resultUnion(Ok|Err $result): void
{
    if ($result->isOk()) {
        assertType('Brimmar\PhpResult\Err<int, string>|Brimmar\PhpResult\Ok<int, string>', $result);
    } else {
        assertType('Brimmar\PhpResult\Err<int, string>|Brimmar\PhpResult\Ok<int, string>', $result);
    }
}

/**
 * The narrowed Err carries no success type, so it passes as the Result of another one.
 *
 * @param Result<int, string> $result
 * @return Result<bool, string>
 */
function resultEarlyReturnsTheErr(Result $result): Result
{
    if ($result->isErr()) {
        return $result;
    }

    assertType('int', $result->unwrap());

    return new Ok(true);
}
