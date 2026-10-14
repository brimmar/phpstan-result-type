<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Tests\Rules;

use Brimmar\PhpResult\Interfaces\Result;

class ResultStaticFixtures
{
    #[\NoDiscard]
    public static function makeNd(): Result
    {
        throw new \RuntimeException('missing');
    }

    /** @return Result<int, string> */
    public static function make(): Result
    {
        throw new \RuntimeException('missing');
    }

    public function calls(): void
    {
        self::makeNd();
        static::makeNd();
        self::make();
    }
}
