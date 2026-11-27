<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Throw_;
use PhpParser\NodeFinder;
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

    public function __construct(string $resultInterface = 'Brimmar\PhpResult\Result')
    {
        $this->resultInterface = $resultInterface;
    }

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
        return (new NodeFinder())->findFirstInstanceOf($node, Throw_::class) !== null;
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

        $callbackArg = reset($args)->value;
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
