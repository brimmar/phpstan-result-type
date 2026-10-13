<?php

namespace Brimmar\PHPStan\Tests\Rules\Data;

use Brimmar\PhpResult\Interfaces\Result;

class MatchFixtures
{
    public function positional(Result $result): void
    {
        $result->match(fn ($value) => $value, fn ($error) => $error);
    }

    public function named(Result $result): void
    {
        $result->match(
            Ok: fn ($value) => $value,
            Err: fn ($error) => $error,
        );
    }

    public function swapped(Result $result): void
    {
        $result->match(
            Err: fn ($error) => $error,
            Ok: fn ($value) => $value,
        );
    }

    public function wrongName(Result $result): void
    {
        $result->match(
            Ok: fn ($value) => $value,
            Failure: fn ($error) => $error,
        );
    }

    public function oneArg(Result $result): void
    {
        $result->match(Ok: fn ($value) => $value);
    }
}
