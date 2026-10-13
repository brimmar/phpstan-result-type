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
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<MethodCall>
 */
class ResultShapeRule implements Rule
{
    public function __construct(private string $resultInterface = 'Brimmar\PhpResult\Interfaces\Result') {}

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->name instanceof Identifier) {
            return [];
        }

        $method = $node->name->name;
        if (! in_array($method, ['flatten', 'transpose'], true)) {
            return [];
        }

        $type = $scope->getType($node->var);
        if (! $type instanceof GenericObjectType) {
            return [];
        }

        if (! (new ObjectType($this->resultInterface))->isSuperTypeOf($type)->yes()) {
            return [];
        }

        $inner = $type->getTypes()[0] ?? null;
        if (! $inner instanceof Type) {
            return [];
        }

        $expected = $method === 'flatten' ? $this->resultInterface : 'Brimmar\PhpOption\Interfaces\Option';
        if (! (new ObjectType($expected))->isSuperTypeOf($inner)->no()) {
            return [];
        }

        $needed = $method === 'flatten' ? 'Result<Result<T, E>, E>' : 'Result<Option<T>, E>';

        return [
            RuleErrorBuilder::message("Result::{$method}() is for {$needed}. The contained type is ".$inner->describe(VerbosityLevel::typeOnly()).'.')
                ->identifier('result.'.$method)
                ->build(),
        ];
    }
}
