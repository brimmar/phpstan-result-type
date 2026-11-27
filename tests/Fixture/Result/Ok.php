<?php

declare(strict_types=1);

namespace Brimmar\PhpResult;

/**
 * Test double for Ok in brimmar/phpresult.
 *
 * autoload-dev maps Brimmar\PhpResult\ onto tests/Fixture/Result. Do not require
 * brimmar/phpresult as well, or this class collides with the real one.
 *
 * @template-covariant T
 * @template-covariant E = never
 *
 * @extends Result<T, E>
 */
class Ok extends Result
{
    use ResultMethods;
}
