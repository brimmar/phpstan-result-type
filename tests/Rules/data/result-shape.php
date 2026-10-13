<?php

namespace Brimmar\PHPStan\Tests\Rules\Data;

use Brimmar\PhpResult\Interfaces\Result;

class ShapeFixtures
{
    /** @param Result<int, string> $result */
    public function badFlatten(Result $result): void
    {
        $result->flatten();
    }

    /** @param Result<Result<int, string>, string> $result */
    public function goodFlatten(Result $result): void
    {
        $result->flatten();
    }

    /** @param Result<mixed, string> $result */
    public function mixedFlatten(Result $result): void
    {
        $result->flatten();
    }

    /**
     * @template T
     *
     * @param Result<T, string> $result
     */
    public function templateFlatten(Result $result): void
    {
        $result->flatten();
    }

    /** @param Result<int, string> $result */
    public function badTranspose(Result $result): void
    {
        $result->transpose();
    }
}
