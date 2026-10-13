<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Cast\Void_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Analyser\Scope;
use Brimmar\PhpResult\Err;
use Brimmar\PhpResult\Ok;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Rules\Rule;
use PHPStan\Rules\RuleErrorBuilder;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\NeverType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;

/**
 * @implements Rule<Expression>
 */
class ResultUnusedRule implements Rule
{
    public function __construct(
        private ReflectionProvider $reflectionProvider,
        private string $resultInterface = 'Brimmar\PhpResult\Interfaces\Result',
    ) {}

    public function getNodeType(): string
    {
        return Expression::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $expr = $node->expr;
        if ($expr instanceof Assign || $expr instanceof AssignRef || $expr instanceof AssignOp || $expr instanceof Void_) {
            return [];
        }

        if ($this->discardIsAlreadyReported($expr, $scope)) {
            return [];
        }

        $type = $scope->getType($expr);
        if ($this->isNever($type)) {
            return [];
        }

        if (! (new ObjectType($this->resultInterface))->isSuperTypeOf($type)->yes()) {
            return [];
        }

        if ($this->errorCannotExist($type)) {
            return [];
        }

        return [
            RuleErrorBuilder::message($this->message($type))
                ->identifier('result.unused')
                ->build(),
        ];
    }

    private function message(Type $type): string
    {
        $name = $type->describe(VerbosityLevel::typeOnly());
        $definite = (new ObjectType(Ok::class))->isSuperTypeOf($type)->yes()
            || (new ObjectType(Err::class))->isSuperTypeOf($type)->yes();
        if ($definite) {
            return "Unused {$name}. This value must be used.";
        }

        $error = $type instanceof GenericObjectType ? ($type->getTypes()[1] ?? null) : null;
        if ($error instanceof Type && $this->isNever($error)) {
            return "Unused {$name}. The contained value must be used.";
        }

        return "Unused {$name}. This value may be Err, which should be handled.";
    }

    private function isNever(Type $type): bool
    {
        return (new NeverType())->isSuperTypeOf($type)->yes();
    }

    private function errorCannotExist(Type $type): bool
    {
        if (! $type instanceof GenericObjectType || count($type->getTypes()) < 2) {
            return false;
        }

        if (! $this->isNever($type->getTypes()[1])) {
            return false;
        }

        $success = $type->getTypes()[0];

        return ! (new ObjectType($this->resultInterface))->isSuperTypeOf($success)->yes()
            && ! (new ObjectType('Brimmar\PhpOption\Interfaces\Option'))->isSuperTypeOf($success)->yes();
    }

    private function discardIsAlreadyReported(Expr $expr, Scope $scope): bool
    {
        if ($expr instanceof MethodCall && $expr->name instanceof Identifier) {
            $method = $scope->getMethodReflection($scope->getType($expr->var), $expr->name->toString());

            return $method instanceof ExtendedMethodReflection && $method->mustUseReturnValue()->yes();
        }

        if ($expr instanceof StaticCall && $expr->name instanceof Identifier && $expr->class instanceof Name) {
            $className = $scope->resolveName($expr->class);
            if (! $this->reflectionProvider->hasClass($className)) {
                return false;
            }

            $class = $this->reflectionProvider->getClass($className);
            if (! $class->hasMethod($expr->name->toString())) {
                return false;
            }

            return $class->getMethod($expr->name->toString(), $scope)->mustUseReturnValue()->yes();
        }

        if ($expr instanceof FuncCall && $expr->name instanceof Name) {
            if (! $this->reflectionProvider->hasFunction($expr->name, $scope)) {
                return false;
            }

            return $this->reflectionProvider->getFunction($expr->name, $scope)->mustUseReturnValue()->yes();
        }

        return false;
    }
}
