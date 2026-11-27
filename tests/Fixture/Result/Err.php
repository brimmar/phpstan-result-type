<?php

declare(strict_types=1);

namespace Brimmar\PhpResult;

/**
 * Test double for Err in brimmar/phpresult.
 *
 * autoload-dev maps Brimmar\PhpResult\ onto tests/Fixture/Result. Do not require
 * brimmar/phpresult as well, or this class collides with the real one.
 *
 * @template-covariant T = never
 * @template-covariant E = mixed
 *
 * @extends Result<T, E>
 */
class Err extends Result
{
    use ResultMethods;
}
