<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Rules;

use Brimmar\PhpResult\Err;
use Brimmar\PhpResult\Ok;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ErrorType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<MethodCall>
 */
class ResultUnsafeMethodCallRule implements Rule
{
    /**
     * @param class-string $resultInterface
     */
    public function __construct(private string $resultInterface = 'Brimmar\PhpResult\Interfaces\Result') {}

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param  MethodCall  $node
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Identifier) {
            return [];
        }

        $methodName = $node->name->name;
        $type = $scope->getType($node->var);

        if (! (new ObjectType($this->resultInterface))->isSuperTypeOf($type)->yes()) {
            return [];
        }

        $dangerousMethods = ['unwrap', 'expect', 'expectErr', 'unwrapErr', 'intoOk', 'intoErr'];
        if (! in_array($methodName, $dangerousMethods, true)) {
            return [];
        }

        // unwrap/expect/intoOk are safe when E is never, or the value is definitely Ok.
        // unwrapErr/expectErr/intoErr are safe when T is never, or the value is definitely Err.
        $requireOk = in_array($methodName, ['unwrap', 'expect', 'intoOk'], true);
        $impossible = $type->getTemplateType($this->resultInterface, $requireOk ? 'E' : 'T');
        $definite = new ObjectType($requireOk ? Ok::class : Err::class);

        if ((! $impossible instanceof ErrorType && (new NeverType())->isSuperTypeOf($impossible)->yes())
            || $definite->isSuperTypeOf($type)->yes()) {
            return [];
        }

        return [
            RuleErrorBuilder::message("Potentially unsafe use of {$methodName}() on Result type without proper checks. Consider using isOk()/isErr() checks, match(), or unwrapOr() for safer error handling.")
                ->identifier('result.unsafeMethodCall')
                ->build(),
        ];
    }
}
