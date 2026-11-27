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
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<MethodCall>
 */
class ResultUnsafeMethodCallRule implements Rule
{
    /**
     * @param class-string $resultInterface
     */
    public function __construct(private string $resultInterface = 'Brimmar\PhpResult\Result') {}

    #[\Override]
    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param  MethodCall  $node
     */
    #[\Override]
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

        $dangerousMethods = ['unwrap', 'unwrapOrThrow', 'expect', 'expectErr', 'unwrapErr', 'intoOk', 'intoErr'];
        if (! in_array($methodName, $dangerousMethods, true)) {
            return [];
        }

        // unwrap/expect/intoOk are safe when E is never, or the value is definitely Ok.
        // unwrapErr/expectErr/intoErr are safe when T is never, or the value is definitely Err.
        $requireOk = in_array($methodName, ['unwrap', 'unwrapOrThrow', 'expect', 'intoOk'], true);
        $impossible = $type->getTemplateType($this->resultInterface, $requireOk ? 'E' : 'T');
        $definite = new ObjectType($requireOk ? Ok::class : Err::class);

        if ((! $impossible instanceof ErrorType && (new NeverType())->isSuperTypeOf($impossible)->yes())
            || $definite->isSuperTypeOf($type)->yes()) {
            return [];
        }

        // unwrapOrThrow() is the way back to exceptions. It is fine while every possible error is a Throwable.
        if ($methodName === 'unwrapOrThrow') {
            if (! $impossible instanceof ErrorType && (new ObjectType(\Throwable::class))->isSuperTypeOf($impossible)->yes()) {
                return [];
            }

            return [
                RuleErrorBuilder::message('unwrapOrThrow() throws the error itself only when the error is a Throwable. This error is ' . $impossible->describe(VerbosityLevel::typeOnly()) . ', so the call throws a RuntimeException the way unwrap() does. Turn the error into an exception with mapErr() first.')
                    ->identifier('result.unsafeMethodCall')
                    ->build(),
            ];
        }

        return [
            RuleErrorBuilder::message("Potentially unsafe use of {$methodName}() on Result type without proper checks. Consider using isOk()/isErr() checks, match(), or unwrapOr() for safer error handling.")
                ->identifier('result.unsafeMethodCall')
                ->build(),
        ];
    }
}
