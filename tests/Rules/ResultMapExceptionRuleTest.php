<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Tests\Rules;

use Brimmar\PHPStan\Rules\ResultMapExceptionRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ResultMapExceptionRule>
 */
class ResultMapExceptionRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ResultMapExceptionRule('Brimmar\PhpResult\Interfaces\Result');
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/result-map-exception.php'], [
            [
                'Throwing exceptions in map() callback may lead to unexpected behavior. Consider returning an Err instead.',
                11,
            ],
            [
                'Throwing exceptions in map() callback may lead to unexpected behavior. Consider returning an Err instead.',
                15,
            ],
            [
                'Throwing exceptions in mapErr() callback may lead to unexpected behavior. Consider returning an Err instead.',
                17,
            ],
        ]);
    }
}
