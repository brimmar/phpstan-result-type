<?php

namespace Brimmar\PHPStan\Tests\Rules\Data;

use Brimmar\PhpResult\Interfaces\Result;

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
}
