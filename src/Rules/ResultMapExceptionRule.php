<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Throw_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\IdentifierRuleError;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\ObjectType;

/**
 * @implements Rule<MethodCall>
 */
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
        if (! (new ObjectType($this->resultInterface))->isSuperTypeOf($type)->yes()) {
            return [];
        }

        return $this->checkMapUsage($node, $methodName);
    }

    private function containsThrow(Node $node): bool
    {
        if ($node instanceof Throw_) {
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
     * @return list<IdentifierRuleError>
     */
    private function checkMapUsage(MethodCall $node, string $methodName): array
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
                    RuleErrorBuilder::message("Throwing exceptions in {$methodName}() callback may lead to unexpected behavior. Consider returning an Err instead.")
                        ->identifier('result.mapException')
                        ->build(),
                ];
            }
        }

        return [];
    }
}
