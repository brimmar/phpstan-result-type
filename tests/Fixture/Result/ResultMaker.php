<?php

declare(strict_types=1);

namespace Brimmar\PhpResult;

interface ResultMaker
{
    /** @return Result<int, string> */
    public function dropped(): Result;

    /** @return Result<int, never> */
    public function infallible(): Result;

    /** @return Result<Result<int, string>, never> */
    public function nested(): Result;

    /** @return Result<int, string> */
    #[\NoDiscard('test')]
    public function marked(): Result;
}
