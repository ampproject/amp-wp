<?php

namespace AmpProject\AmpWP\Tests\PhpStan;

use AmpProject\AmpWP\AmpWpPlugin;
use AmpProject\AmpWP\Services;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Symfony\ServiceMap;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

final class ServicesDynamicReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension {

	public function getClass(): string {
		return Services::class;
	}

	public function isStaticMethodSupported(
		MethodReflection $methodReflection
	): bool {
		return in_array( $methodReflection->getName(), [ 'get' ], true );
	}

	public function getTypeFromStaticMethodCall(
		MethodReflection $methodReflection,
		StaticCall $methodCall,
		Scope $scope
	): Type {
		switch ( $methodReflection->getName() ) {
			case 'get':
				return $this->getGetTypeFromStaticMethodCall(
					$methodReflection,
					$methodCall
				);

			case 'has':
				return $this->getHasTypeFromStaticMethodCall(
					$methodReflection,
					$methodCall
				);
		}

		throw new ShouldNotHappenException();
	}

	private function getGetTypeFromStaticMethodCall(
		MethodReflection $methodReflection,
		StaticCall $methodCall
	): Type {
		$returnType = ParametersAcceptorSelector::selectSingle(
			$methodReflection->getVariants()
		)->getReturnType();

		if (
			! isset( $methodCall->args[0] )
			||
			empty( $methodCall->args[0]->value )
		) {
			return $returnType;
		}

		$serviceId = $methodCall->args[0]->value;
		if ( $serviceId instanceof String_ ) {
			$serviceId = $serviceId->value;
		}

		if ( array_key_exists( (string) $serviceId, AmpWpPlugin::SERVICES ) ) {
			return new ObjectType( AmpWpPlugin::SERVICES[ (string) $serviceId ] );
		}

		return $returnType;
	}
}
