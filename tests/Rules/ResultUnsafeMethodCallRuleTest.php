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
    #[\Override]
    protected function getRule(): Rule
    {
        return new ResultUnsafeMethodCallRule('Brimmar\PhpResult\Result');
    }

    #[\Override]
    public static function getAdditionalConfigFiles(): array
    {
        return [__DIR__ . '/../../extension.neon'];
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/result-unsafe-method-call.php'], [
            [
                'Potentially unsafe use of unwrap() on Result type without proper checks. Consider using isOk()/isErr() checks, match(), or unwrapOr() for safer error handling.',
                14,
            ],
            [
                'Potentially unsafe use of expectErr() on Result type without proper checks. Consider using isOk()/isErr() checks, match(), or unwrapOr() for safer error handling.',
                36,
            ],
            [
                'Potentially unsafe use of unwrap() on Result type without proper checks. Consider using isOk()/isErr() checks, match(), or unwrapOr() for safer error handling.',
                61,
            ],
            [
                'unwrapOrThrow() throws the error itself only when the error is a Throwable. This error is string, so the call throws a RuntimeException the way unwrap() does. Turn the error into an exception with mapErr() first.',
                73,
            ],
            [
                'unwrapOrThrow() throws the error itself only when the error is a Throwable. This error is RuntimeException|string, so the call throws a RuntimeException the way unwrap() does. Turn the error into an exception with mapErr() first.',
                79,
            ],
        ]);
    }
}
