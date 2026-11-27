<?php

declare(strict_types=1);

namespace Brimmar\PhpResult;

/**
 * Test double for Result in brimmar/phpresult.
 *
 * @template-covariant T
 * @template-covariant E
 */
abstract class Result
{
    /**
     * @template U
     * @template F
     *
     * @param callable(): \Generator<mixed, Result<mixed, F>, mixed, U> $fn
     * @return Result<U, F>
     */
    public static function gen(callable $fn): Result
    {
        throw new \RuntimeException('stub');
    }

    // The published phpresult class also asserts Ok and Err here. This stub does not,
    // so the narrowing test sees the extension rather than native assert-if-true.
    abstract public function isOk(): bool;

    /**
     * @param callable(T): bool $fn
     *
     * @phpstan-assert-if-true Ok<T, never> $this
     */
    abstract public function isOkAnd(callable $fn): bool;

    abstract public function isErr(): bool;

    /**
     * @param callable(E): bool $fn
     *
     * @phpstan-assert-if-true Err<never, E> $this
     */
    abstract public function isErrAnd(callable $fn): bool;
    /** @return T */
    abstract public function unwrap();
    /** @return T */
    abstract public function unwrapOrThrow();
    /** @return E */
    abstract public function unwrapErr();
    abstract public function expect(string $msg);
    abstract public function expectErr(string $msg);
    abstract public function intoOk();
    abstract public function intoErr();
    abstract public function flatten(): Result;
    abstract public function map(callable $callback): Result;
    abstract public function mapErr(callable $callback): Result;
    abstract public function match($Ok, $Err);
    abstract public function ok($class = null);
    abstract public function err($class = null);
    abstract public function transpose($class = null);
}
