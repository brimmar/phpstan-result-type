<?php

declare(strict_types=1);

namespace Brimmar\PhpResult;

use Brimmar\PhpResult\Interfaces\Result;

/**
 * Test double for Err in brimmar/phpresult.
 *
 * autoload-dev maps Brimmar\PhpResult\ onto tests/Fixture/Result. Do not require
 * brimmar/phpresult as well, or this class collides with the real one.
 *
 * @template T
 * @template E
 *
 * @implements Result<T, E>
 */
class Err implements Result
{
    use ResultMethods;
}
