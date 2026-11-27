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
use PHPStan\Type\NeverType;
use PHPStan\Type\Type;

/**
 * Narrows isOk() and isErr() to brimmar/phpresult Ok and Err.
 *
 * The side that cannot exist becomes never: Ok<T, never> and Err<never, E>.
 * That lets an Err pass as the Result of any other success type, so
 * `if ($result->isErr()) { return $result; }` type-checks. A custom Result
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
    public function __construct(string $resultInterface = 'Brimmar\PhpResult\Result')
    {
        $this->resultInterface = $resultInterface;
    }

    #[\Override]
    public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
    {
        $this->typeSpecifier = $typeSpecifier;
    }

    #[\Override]
    public function getClass(): string
    {
        return $this->resultInterface;
    }

    #[\Override]
    public function isMethodSupported(
        MethodReflection $methodReflection,
        MethodCall $node,
        TypeSpecifierContext $context,
    ): bool {
        return in_array($methodReflection->getName(), ['isOk', 'isErr'], true)
            && ! $context->null();
    }

    #[\Override]
    public function specifyTypes(
        MethodReflection $methodReflection,
        MethodCall $node,
        Scope $scope,
        TypeSpecifierContext $context,
    ): SpecifiedTypes {
        if ($context->true()) {
            $wantsOk = $methodReflection->getName() === 'isOk';
        } elseif ($context->false()) {
            $wantsOk = $methodReflection->getName() !== 'isOk';
        } else {
            return new SpecifiedTypes();
        }

        [$success, $error] = $this->typeParameters($scope->getType($node->var));
        $narrowed = $wantsOk
            ? new GenericObjectType(Ok::class, [$success, new NeverType(true)])
            : new GenericObjectType(Err::class, [new NeverType(true), $error]);

        return $this->typeSpecifier->create(
            $node->var,
            $narrowed,
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
