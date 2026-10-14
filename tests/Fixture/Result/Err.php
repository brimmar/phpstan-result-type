<?php

declare(strict_types=1);

namespace Brimmar\PhpResult;

use Brimmar\PhpResult\Interfaces\Result;

/**
 * @template T
 * @template E
 *
 * @implements Result<T, E>
 */
class Err implements Result
{
    use ResultMethods;
}
