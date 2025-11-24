<?php

namespace Brimmar\PHPStan\Tests\Rules\Data;

use Brimmar\PhpResult\Interfaces\Result;

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
}
