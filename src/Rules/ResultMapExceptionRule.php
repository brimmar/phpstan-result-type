<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Throw_;
use PhpParser\Node\Expr\Throw_ as ExprThrow;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\ObjectType;

class ResultMapExceptionRule implements Rule
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

    /**
     * @param  MethodCall  $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Node\Identifier) {
            return [];
        }

        $methodName = $node->name->name;
        if ($methodName !== 'map' && $methodName !== 'mapErr') {
            return [];
        }

        $type = $scope->getType($node->var);
        if (! $type instanceof ObjectType || ! $type->isInstanceOf($this->resultInterface)->yes()) {
            return [];
        }

        return $this->checkMapUsage($node, $scope);
    }

    private function containsThrow(Node $node): bool
    {
        if ($node instanceof Throw_ || $node instanceof ExprThrow) {
            return true;
        }

        foreach ($node->getSubNodeNames() as $name) {
            $subNode = $node->$name;
            if ($subNode instanceof Node) {
                if ($this->containsThrow($subNode)) {
                    return true;
                }
            } elseif (is_array($subNode)) {
                foreach ($subNode as $item) {
                    if ($item instanceof Node && $this->containsThrow($item)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @return string[]
     */
    private function checkMapUsage(MethodCall $node, Scope $scope): array
    {
        $args = $node->getArgs();
        if (count($args) !== 1) {
            return [];
        }

        $callbackArg = $args[0]->value;
        if (! $callbackArg instanceof Closure && ! $callbackArg instanceof ArrowFunction) {
            return [];
        }

        $stmts = $callbackArg instanceof Closure ? $callbackArg->stmts : [$callbackArg->expr];
        foreach ($stmts as $stmt) {
            if ($this->containsThrow($stmt)) {
                return [
                    "Throwing exceptions in {$node->name->name}() callback may lead to unexpected behavior. Consider returning an Err instead.",
                ];
            }
        }

        return [];
    }
}
