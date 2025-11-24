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
}
