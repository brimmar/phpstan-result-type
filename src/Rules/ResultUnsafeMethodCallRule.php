<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<MethodCall>
 */
class ResultUnsafeMethodCallRule implements Rule
{
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

        if (! $type instanceof ObjectType || ! $type->isInstanceOf($this->resultInterface)->yes()) {
            return [];
        }

        $dangerousMethods = ['unwrap', 'expect', 'unwrapErr', 'intoOk', 'intoErr'];
        if (! in_array($methodName, $dangerousMethods, true)) {
            return [];
        }

        // We check if the type is narrowed to be safe.
        // For unwrap/expect/intoOk: we expect Result<T, never> (Error side is impossible)
        // For unwrapErr/intoErr: we expect Result<never, E> (Ok side is impossible)

        $requireOk = in_array($methodName, ['unwrap', 'expect', 'intoOk'], true);

        if ($requireOk) {
            // Check if it is guaranteed OK.
            // i.e. Result<mixed, never> is supertype of current type.
            $okRequirement = new GenericObjectType($this->resultInterface, [new MixedType(), new NeverType()]);
            if ($okRequirement->isSuperTypeOf($type)->yes()) {
                return [];
            }
        } else {
            // Check if it is guaranteed Err.
            $errRequirement = new GenericObjectType($this->resultInterface, [new NeverType(), new MixedType()]);
            if ($errRequirement->isSuperTypeOf($type)->yes()) {
                return [];
            }
        }

        return [
            RuleErrorBuilder::message("Potentially unsafe use of {$methodName}() on Result type without proper checks. Consider using isOk()/isErr() checks, match(), or unwrapOr() for safer error handling.")
                ->identifier('result.unsafeMethodCall')
                ->build(),
        ];
    }
}
