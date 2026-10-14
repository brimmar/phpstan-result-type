<?php

namespace Brimmar\PHPStan\Tests\Rules\Data;

use Brimmar\PhpResult\Err;
use Brimmar\PhpResult\Interfaces\Result;
use Brimmar\PhpResult\Ok;

class TestClass
{
    public function testUnsafe(Result $result)
    {
        // Unsafe
        $result->unwrap();
    }

    public function testSafe(Result $result)
    {
        if ($result->isOk()) {
            // Safe
            $result->unwrap();
        }
    }

    public function testSafeEarlyReturn(Result $result)
    {
        if ($result->isErr()) {
            return;
        }
        // Safe
        $result->unwrap();
    }

    public function testExpectErr(Result $result)
    {
        $result->expectErr('missing');
    }

    public function testExpectErrSafe(Result $result)
    {
        if ($result->isErr()) {
            $result->expectErr('missing');
        }
    }

    /** @param Result<int, never> $result */
    public function testIntoOkNever(Result $result): void
    {
        $result->intoOk();
    }

    /** @param Result<never, string> $result */
    public function testExpectErrNever(Result $result): void
    {
        $result->expectErr('missing');
    }

    /** @param Ok<int, string>|Err<int, string> $result */
    public function testUnionUnwrap(Ok|Err $result): void
    {
        $result->unwrap();
    }
}
