<?php

namespace Brimmar\PHPStan\Tests\Rules\Data;

use Brimmar\PhpOption\Interfaces\Option;
use Brimmar\PhpResult\Err;
use Brimmar\PhpResult\Interfaces\Result;
use Brimmar\PhpResult\Ok;

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

    /** @param Result<Option<int>, string> $result */
    public function goodTranspose(Result $result): void
    {
        $result->transpose();
    }

    /** @param Result<int, string>|null $result */
    public function nullableTranspose(Result|null $result): void
    {
        $result?->transpose();
    }

    /** @param Ok<int, string>|Err<int, string> $result */
    public function unionFlatten(Ok|Err $result): void
    {
        $result->flatten();
    }
}
