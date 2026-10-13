<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Tests\Rules;

use Brimmar\PHPStan\Rules\ResultShapeRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ResultShapeRule>
 */
class ResultShapeRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ResultShapeRule('Brimmar\PhpResult\Interfaces\Result');
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/result-shape.php'], [
            [
                'Result::flatten() is for Result<Result<T, E>, E>. The contained type is int.',
                12,
            ],
            [
                'Result::transpose() is for Result<Option<T>, E>. The contained type is int.',
                40,
            ],
        ]);
    }
}
