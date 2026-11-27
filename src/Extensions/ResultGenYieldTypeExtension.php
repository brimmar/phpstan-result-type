<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Extensions;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Yield_;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ErrorType;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * Types `yield $result` inside the closure given to Result::gen().
 *
 * Result::gen() sends the Ok value back into the generator, so the yield
 * expression is T of the yielded Result<T, E>. Without this PHPStan reads it
 * as mixed.
 */
class ResultGenYieldTypeExtension implements ExpressionTypeResolverExtension
{
    /**
     * @param class-string $resultInterface
     */
    public function __construct(private string $resultInterface = 'Brimmar\PhpResult\Result') {}

    #[\Override]
    public function getType(Expr $expr, Scope $scope): ?Type
    {
        if (! $expr instanceof Yield_ || $expr->value === null || $expr->getAttribute(ResultGenYieldVisitor::ATTRIBUTE) !== true) {
            return null;
        }

        $yielded = $scope->getType($expr->value);
        if (! (new ObjectType($this->resultInterface))->isSuperTypeOf($yielded)->yes()) {
            return null;
        }

        $success = $yielded->getTemplateType($this->resultInterface, 'T');

        return $success instanceof ErrorType ? null : $success;
    }
}
