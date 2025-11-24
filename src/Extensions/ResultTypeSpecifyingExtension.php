<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Extensions;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Analyser\SpecifiedTypes;
use PHPStan\Analyser\TypeSpecifier;
use PHPStan\Analyser\TypeSpecifierAwareExtension;
use PHPStan\Analyser\TypeSpecifierContext;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\MethodTypeSpecifyingExtension;
use PHPStan\Type\MixedType;
use PHPStan\Type\NeverType;

class ResultTypeSpecifyingExtension implements MethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
    private TypeSpecifier $typeSpecifier;
    private string $resultInterface;

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
        $methodName = $methodReflection->getName();
        $isOk = $methodName === 'isOk';

        // Result<mixed, never> => It is OK
        $okType = new GenericObjectType($this->getClass(), [new MixedType(), new NeverType()]);
        // Result<never, mixed> => It is Err
        $errType = new GenericObjectType($this->getClass(), [new NeverType(), new MixedType()]);

        if ($isOk) {
            return $this->typeSpecifier->create(
                $node->var,
                $okType,
                TypeSpecifierContext::createTruthy(),
                $scope
            )->unionWith($this->typeSpecifier->create(
                $node->var,
                $errType,
                TypeSpecifierContext::createFalsey(),
                $scope
            ));
        } else {
            // isErr
            return $this->typeSpecifier->create(
                $node->var,
                $errType,
                TypeSpecifierContext::createTruthy(),
                $scope
            )->unionWith($this->typeSpecifier->create(
                $node->var,
                $okType,
                TypeSpecifierContext::createFalsey(),
                $scope
            ));
        }
    }
}
