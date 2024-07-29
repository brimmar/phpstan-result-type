<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<MethodCall>
 */
class ResultMatchNamedArgumentsRule implements Rule
{
    private string $resultInterface;

    public function __construct(string $resultInterface = 'Brimmar\PhpResult\Interfaces\Result')
    {
        $this->resultInterface = $resultInterface;
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node instanceof MethodCall) {
            return [];
        }

        if (! $node->name instanceof Node\Identifier) {
            return [];
        }

        if ($node->name->name !== 'match') {
            return [];
        }

        $type = $scope->getType($node->var);
        if (! $type instanceof ObjectType) {
            return [];
        }

        if (! $type->isInstanceOf($this->resultInterface)->yes()) {
            return [];
        }

        if (count($node->getArgs()) !== 2) {
            return ['Result::match() must have exactly two arguments.'];
        }

        $okArg = $node->getArgs()[0];
        $errArg = $node->getArgs()[1];

        if ($okArg->name === null || $errArg->name === null) {
            return ['Result::match() must use named arguments "Ok" and "Err".'];
        }

        if ($okArg->name->name !== 'Ok' || $errArg->name->name !== 'Err') {
            return ['Result::match() must use named arguments "Ok" and "Err" in this order.'];
        }

        return [];
    }
}
