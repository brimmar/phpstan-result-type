<?php

namespace Brimmar\PhpResult\Interfaces;

/**
 * @template T
 * @template E
 */
interface Result {
    public function isOk(): bool;
    public function isErr(): bool;
    /** @return T */
    public function unwrap();
    /** @return E */
    public function unwrapErr();
    public function expect(string $msg);
    public function intoOk();
    public function intoErr();
    public function map(callable $callback): Result;
    public function mapErr(callable $callback): Result;
    public function match($Ok, $Err);
    public function ok($class = null);
    public function err($class = null);
    public function transpose($class = null);
}
