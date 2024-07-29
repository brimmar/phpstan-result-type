<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\BooleanNot;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\ObjectType;

class ResultUnsafeMethodCallRule implements Rule
{
    public function __construct(private string $resultInterface = 'Brimmar\PhpResult\Interfaces\Result') {}

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
        if (! $node->name instanceof Identifier) {
            return [];
        }

        $methodName = $node->name->name;
        $type = $scope->getType($node->var);

        if (! $type instanceof ObjectType || ! $type->isInstanceOf($this->resultInterface)->yes()) {
            return [];
        }

        $dangerousMethods = ['unwrap', 'expect', 'unwrapErr', 'intoOk', 'intoErr'];
        if (in_array($methodName, $dangerousMethods, true)) {
            $checkResult = $this->analyzeContext($node, $scope);
            if (! $checkResult['isSafe']) {
                return [
                    "Potentially unsafe use of {$methodName}() on Result type without proper checks. Consider using isOk()/isErr() checks, match(), or unwrapOr() for safer error handling.",
                ];
            }
        }

        return [];
    }

    private function analyzeContext(MethodCall $node, Scope $scope): array
    {
        $parent = $node->getAttribute('parent');
        $methodName = $node->name->name;

        while ($parent !== null) {
            if ($parent instanceof If_) {
                $condition = $this->analyzeCondition($parent->cond);
                if ($condition !== null) {
                    $inIfBlock = $this->isNodeInIfBlock($node, $parent);
                    $hasEarlyReturn = $this->hasEarlyReturn($parent->stmts);

                    if ($inIfBlock) {
                        return ['isSafe' => ($condition === 'isOk' && $methodName === 'unwrap') ||
                                            ($condition === 'isErr' && $methodName === 'unwrapErr')];
                    } elseif ($hasEarlyReturn) {
                        return ['isSafe' => ($condition === 'isOk' && $methodName === 'unwrapErr') ||
                                            ($condition === 'isErr' && $methodName === 'unwrap')];
                    }
                }
            }
            $parent = $parent->getAttribute('parent');
        }

        return ['isSafe' => false];
    }

    private function analyzeCondition(Node $condition): ?string
    {
        if ($condition instanceof MethodCall && $condition->name instanceof Identifier) {
            $methodName = $condition->name->name;
            if ($methodName === 'isOk' || $methodName === 'isErr') {
                return $methodName;
            }
        } elseif ($condition instanceof BooleanNot && $condition->expr instanceof MethodCall) {
            $methodName = $condition->expr->name->name;
            if ($methodName === 'isOk') {
                return 'isErr';
            } elseif ($methodName === 'isErr') {
                return 'isOk';
            }
        }

        return null;
    }

    private function isNodeInIfBlock(Node $node, If_ $ifStatement): bool
    {
        foreach ($ifStatement->stmts as $stmt) {
            if ($this->nodeContains($stmt, $node)) {
                return true;
            }
        }

        return false;
    }

    private function hasEarlyReturn(array $statements): bool
    {
        foreach ($statements as $stmt) {
            if ($stmt instanceof Return_) {
                return true;
            }
        }

        return false;
    }

    private function nodeContains(Node $haystack, Node $needle): bool
    {
        if ($haystack === $needle) {
            return true;
        }

        foreach ($haystack->getSubNodeNames() as $name) {
            $subNode = $haystack->$name;
            if ($subNode instanceof Node) {
                if ($this->nodeContains($subNode, $needle)) {
                    return true;
                }
            } elseif (is_array($subNode)) {
                foreach ($subNode as $item) {
                    if ($item instanceof Node && $this->nodeContains($item, $needle)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
