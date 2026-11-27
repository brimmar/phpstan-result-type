<?php

namespace Brimmar\PHPStan\Tests\Rules\Data;

use Brimmar\PhpResult\Result;
use function PHPStan\Testing\assertType;

/**
 * @param Result<int, string> $number
 * @param Result<string, \RuntimeException> $text
 */
function genYields(Result $number, Result $text): void
{
    $result = Result::gen(function () use ($number, $text) {
        $a = yield $number;
        assertType('int', $a);
        $b = yield $text;
        assertType('string', $b);
        assertType('int<0, max>', strlen(yield $text));

        // A nested generator is not driven by Result::gen(), so its yields stay untyped.
        $nested = static function () use ($number) {
            $inner = yield $number;
            assertType('mixed', $inner);
        };

        return [$a, $b];
    });

    assertType('Brimmar\PhpResult\Result<array{int, string}, RuntimeException|string>', $result);
}

/**
 * A generator that is not given to Result::gen() keeps its own send type.
 *
 * @param Result<int, string> $number
 * @return \Generator<int, Result<int, string>, bool, void>
 */
function plainGenerator(Result $number): \Generator
{
    $sent = yield $number;
    assertType('bool', $sent);
}
