<?php

declare(strict_types=1);

namespace Brimmar\PHPStan\Rules;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\ObjectType;
use PHPStan\Reflection\ReflectionProvider;

class ResultOptionClassNameRule implements Rule
{
    private string $resultInterface;
    private string $optionInterface;
    private ReflectionProvider $reflectionProvider;

    public function __construct(
        ReflectionProvider $reflectionProvider,
        string $resultInterface = 'Brimmar\PhpResult\Interfaces\Result',
        string $optionInterface = 'Brimmar\PhpOption\Interfaces\Option'
    ) {
        $this->reflectionProvider = $reflectionProvider;
        $this->resultInterface = $resultInterface;
        $this->optionInterface = $optionInterface;
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     * @param Scope $scope
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (!$node->name instanceof Node\Identifier) {
            return [];
        }

        $methodName = $node->name->name;
        if (!in_array($methodName, ['ok', 'err', 'transpose'], true)) {
            return [];
        }

        $type = $scope->getType($node->var);
        if (!$type instanceof ObjectType || !$type->isInstanceOf($this->resultInterface)->yes()) {
            return [];
        }

        $args = $node->getArgs();
        
        // Check if a class name is provided
        if (empty($args)) {
            return $this->checkDefaultClass($methodName);
        }

        $classNameArg = $args[0]->value;

        // Handle class name as string
        if ($classNameArg instanceof String_) {
            return $this->validateClassName($classNameArg->value, $methodName);
        }

        // Handle class name as ::class constant
        if ($classNameArg instanceof ClassConstFetch && $classNameArg->name instanceof Node\Identifier && $classNameArg->name->name === 'class') {
            if ($classNameArg->class instanceof Name) {
                $className = $classNameArg->class->toString();
                return $this->validateClassName($className, $methodName);
            }
        }

        return ["Invalid argument type for {$methodName}() method. Expected class name as string or ::class constant."];
    }

    private function validateClassName(string $className, string $methodName): array
    {
        if (!$this->reflectionProvider->hasClass($className)) {
            return ["Class {$className} does not exist."];
        }

        $classReflection = $this->reflectionProvider->getClass($className);

        if (!$classReflection->isInstantiable()) {
            return ["Class {$className} is not instantiable."];
        }

        if (!$classReflection->implementsInterface($this->optionInterface)) {
            return ["Class {$className} does not implement {$this->optionInterface}."];
        }

        return [];
    }

    private function checkDefaultClass(string $methodName): array
    {
        $defaultClasses = [
            'ok' => '\Brimmar\PhpOption\Some',
            'err' => '\Brimmar\PhpOption\None',
            'transpose' => ['\Brimmar\PhpOption\None', '\Brimmar\PhpOption\Some'],
        ];

        $classesToCheck = $defaultClasses[$methodName];
        if (!is_array($classesToCheck)) {
            $classesToCheck = [$classesToCheck];
        }

        foreach ($classesToCheck as $className) {
            $result = $this->validateClassName($className, $methodName);
            if (!empty($result)) {
                return ["Default class {$className} for {$methodName}() method is invalid: " . $result[0]];
            }
        }

        return [];
    }
}
