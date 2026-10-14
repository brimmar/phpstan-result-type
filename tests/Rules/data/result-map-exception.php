<?php

namespace Brimmar\PHPStan\Tests\Rules\Data;

use Brimmar\PhpResult\Err;
use Brimmar\PhpResult\Interfaces\Result;
use Brimmar\PhpResult\Ok;

class ResultMapExceptionTestClass
{
    public function testMapException(Result $result)
    {
        $result->map(function ($val) {
            throw new \Exception('error');
        });

        $result->map(fn($val) => throw new \Exception('error'));

        $result->mapErr(function ($err) {
            throw new \Exception('error');
        });

        $result->map(function ($val) {
            return $val;
        });
    }

    /** @param Ok<int, string>|Err<int, string> $result */
    public function testUnionMap(Ok|Err $result): void
    {
        $result->map(fn ($val) => throw new \Exception('error'));
    }
}
