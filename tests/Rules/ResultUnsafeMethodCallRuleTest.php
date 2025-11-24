<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Tests\Rules;

use Brimmar\PHPStan\Rules\ResultUnsafeMethodCallRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ResultUnsafeMethodCallRule>
 */
class ResultUnsafeMethodCallRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ResultUnsafeMethodCallRule('Brimmar\PhpResult\Interfaces\Result');
    }

    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon'];
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/result-unsafe-method-call.php'], [
            [
                'Potentially unsafe use of unwrap() on Result type without proper checks. Consider using isOk()/isErr() checks, match(), or unwrapOr() for safer error handling.',
                12,
            ],
        ]);
    }
}
