<?php

namespace Brimmar\PhpResult\Interfaces;

use Brimmar\PhpResult\Err;
use Brimmar\PhpResult\Ok;

/**
 * @template T
 * @template E
 */
interface Result {
    // The published phpresult interface also asserts Ok here. This stub does not,
    // so the narrowing test sees the extension rather than native assert-if-true.
    public function isOk(): bool;

    /**
     * @param callable(T): bool $fn
     *
     * @phpstan-assert-if-true Ok<T, E> $this
     */
    public function isOkAnd(callable $fn): bool;

    public function isErr(): bool;

    /**
     * @param callable(E): bool $fn
     *
     * @phpstan-assert-if-true Err<T, E> $this
     */
    public function isErrAnd(callable $fn): bool;
    /** @return T */
    public function unwrap();
    /** @return E */
    public function unwrapErr();
    public function expect(string $msg);
    public function expectErr(string $msg);
    public function intoOk();
    public function intoErr();
    public function flatten(): Result;
    public function map(callable $callback): Result;
    public function mapErr(callable $callback): Result;
    public function match($Ok, $Err);
    public function ok($class = null);
    public function err($class = null);
    public function transpose($class = null);
}
