<?php

declare(strict_types=1);

namespace Brimmar\PhpResult;

use Brimmar\PhpResult\Interfaces\Result;

trait ResultMethods
{
    public function isOk(): bool
    {
        return true;
    }

    public function isOkAnd(callable $fn): bool
    {
        return true;
    }

    public function isErr(): bool
    {
        return false;
    }

    public function isErrAnd(callable $fn): bool
    {
        return false;
    }

    public function unwrap(): mixed
    {
        return null;
    }

    public function unwrapErr(): mixed
    {
        return null;
    }

    public function expect(string $msg): mixed
    {
        return null;
    }

    public function expectErr(string $msg): mixed
    {
        return null;
    }

    public function intoOk(): mixed
    {
        return null;
    }

    public function intoErr(): mixed
    {
        return null;
    }

    public function flatten(): Result
    {
        throw new \RuntimeException('stub');
    }

    public function map(callable $callback): Result
    {
        throw new \RuntimeException('stub');
    }

    public function mapErr(callable $callback): Result
    {
        throw new \RuntimeException('stub');
    }

    public function match(mixed $Ok, mixed $Err): mixed
    {
        return null;
    }

    public function ok(mixed $class = null): mixed
    {
        return null;
    }

    public function err(mixed $class = null): mixed
    {
        return null;
    }

    public function transpose(mixed $class = null): mixed
    {
        return null;
    }
}
