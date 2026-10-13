<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Tests\Rules;

use Brimmar\PHPStan\Rules\ResultUnusedRule;
use PHPStan\Rules\Rule;
use PHPStan\Testing\RuleTestCase;

/**
 * @extends RuleTestCase<ResultUnusedRule>
 */
class ResultUnusedRuleTest extends RuleTestCase
{
    protected function getRule(): Rule
    {
        return new ResultUnusedRule(self::createReflectionProvider());
    }

    public function testRule(): void
    {
        $this->analyse([__DIR__ . '/data/result-unused.php'], [
            [
                'Unused Brimmar\PhpResult\Interfaces\Result<int, string>. This value may be Err, which should be handled.',
                12,
            ],
            [
                'Unused Brimmar\PhpResult\Interfaces\Result<Brimmar\PhpResult\Interfaces\Result<int, string>, never>. The contained value must be used.',
                22,
            ],
            [
                'Unused Brimmar\PhpResult\Interfaces\Result<int, string>. This value may be Err, which should be handled.',
                44,
            ],
        ]);
    }
}
