<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Tests\Rules;

use Brimmar\PHPStan\Rules\ResultMatchNamedArgumentsRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ResultMatchNamedArgumentsRule>
 */
class ResultMatchNamedArgumentsRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ResultMatchNamedArgumentsRule('Brimmar\PhpResult\Interfaces\Result');
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/result-match-named.php'], [
            [
                'Result::match() must use named arguments "Ok" and "Err".',
                11,
            ],
            [
                'Result::match() must use named arguments "Ok" and "Err".',
                32,
            ],
            [
                'Result::match() must have exactly two arguments.',
                40,
            ],
        ]);
    }
}
