<?php

namespace Brimmar\PHPStan\Tests\Rules\Data;

use Brimmar\PhpResult\Interfaces\Result;
use Brimmar\PhpResult\Interfaces\ResultMaker;

class UnusedFixtures
{
    public function callDropped(ResultMaker $maker): void
    {
        $maker->dropped();
    }

    public function callInfallible(ResultMaker $maker): void
    {
        $maker->infallible();
    }

    public function callNested(ResultMaker $maker): void
    {
        $maker->nested();
    }

    public function callMarked(ResultMaker $maker): void
    {
        $maker->marked();
    }

    public function silenced(ResultMaker $maker): void
    {
        (void) $maker->dropped();
    }

    /** @param Result<int, string> $result */
    public function assigned(Result $result): void
    {
        $kept = $result;
    }

    /** @param Result<int, string> $result */
    public function bare(Result $result): void
    {
        $result;
    }

    public function stops(): void
    {
        throw new \RuntimeException('stop');
    }
}
