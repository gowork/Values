<?php declare(strict_types=1);

namespace GW\Value\PHPStan;

use GW\Value\ArrayValue;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;

final class ArrayValueNotEmptyExtension implements DynamicMethodReturnTypeExtension
{
    use NotEmptyTypeRemover;

    public function getClass(): string
    {
        return ArrayValue::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'notEmpty' || $methodReflection->getName() === 'filterEmpty';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        return $this->removeNull($scope->getType($methodCall->var));
    }
}
