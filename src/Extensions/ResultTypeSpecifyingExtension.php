<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Extensions;

use Brimmar\PhpResult\Err;
use Brimmar\PhpResult\Ok;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ErrorType;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;

/**
 * Narrows isOk() and isErr() to brimmar/phpresult Ok and Err.
 *
 * Those classes are declared as template T and template E. A custom Result
 * implementation is narrowed to Ok or Err as well.
 */
class ResultTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    private TypeSpecifier $typeSpecifier;

    /** @var class-string */
    private string $resultInterface;

    /**
     * @param class-string $resultInterface
     */
    public function __construct(string $resultInterface = 'Brimmar\PhpResult\Interfaces\Result')
    {
        $this->resultInterface = $resultInterface;
    }

    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }

    public function getClass(): string
    {
        return $this->resultInterface;
    }

    public function isMethodSupported(
        MethodReflection $methodReflection,
        MethodCall $node,
        TypeSpecifierContext $context
    ): bool {
        return in_array($methodReflection->getName(), ['isOk', 'isErr'], true)
            && ! $context->null();
    }

    public function specifyTypes(
        MethodReflection $methodReflection,
        MethodCall $node,
        Scope $scope,
        TypeSpecifierContext $context
    ): SpecifiedTypes {
        if ($context->true()) {
            $wantsOk = $methodReflection->getName() === 'isOk';
        } elseif ($context->false()) {
            $wantsOk = $methodReflection->getName() !== 'isOk';
        } else {
            return new SpecifiedTypes();
        }

        [$success, $error] = $this->typeParameters($scope->getType($node->var));
        $class = $wantsOk ? Ok::class : Err::class;

        return $this->typeSpecifier->create(
            $node->var,
            new GenericObjectType($class, [$success, $error]),
            TypeSpecifierContext::createTrue(),
            $scope,
        );
    }

    /**
     * @return array{Type, Type}
     */
    private function typeParameters(Type $type): array
    {
        $success = $type->getTemplateType($this->resultInterface, 'T');
        $error = $type->getTemplateType($this->resultInterface, 'E');
        if ($success instanceof ErrorType || $error instanceof ErrorType) {
            return [new MixedType(), new MixedType()];
        }

        return [$success, $error];
    }
}
