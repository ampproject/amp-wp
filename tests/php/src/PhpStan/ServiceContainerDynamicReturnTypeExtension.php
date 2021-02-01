<?php
/**
 * Class ServicesDynamicReturnTypeExtension.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\PhpStan;

use AmpProject\AmpWP\AmpWpPlugin;
use AmpProject\AmpWP\Infrastructure\Injector;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Infrastructure\ServiceContainer;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\ShouldNotHappenException;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;

/**
 * PHPStan extension class that provides the type for services returned via the
 * static service locator.
 *
 * phpcs:disable WordPress.NamingConventions.ValidVariableName
 * phpcs:disable PHPCompatibility.FunctionDeclarations.NewReturnTypeDeclarations
 * phpcs:disable VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
 */
final class ServiceContainerDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension {

	public function getClass(): string {
		return ServiceContainer::class;
	}

	public function isMethodSupported(
		MethodReflection $methodReflection
	): bool {
		return in_array( $methodReflection->getName(), [ 'get' ], true );
	}

	public function getTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall,
		Scope $scope
	): Type {
		switch ( $methodReflection->getName() ) {
			case 'get':
				return $this->getGetTypeFromMethodCall(
					$methodReflection,
					$methodCall
				);

			case 'has':
				return $this->getHasTypeFromMethodCall(
					$methodReflection,
					$methodCall
				);
		}

		throw new ShouldNotHappenException();
	}

	private function getGetTypeFromMethodCall(
		MethodReflection $methodReflection,
		MethodCall $methodCall
	): Type {
		$return_type = ParametersAcceptorSelector::selectSingle(
			$methodReflection->getVariants()
		)->getReturnType();

		if (
			! isset( $methodCall->args[0] )
			||
			empty( $methodCall->args[0]->value )
		) {
			return $return_type;
		}

		$service_id = $methodCall->args[0]->value;
		if ( $service_id instanceof String_ ) {
			$service_id = $service_id->value;
		}

		$services = array_merge(
			[ 'injector' => Injector::class ],
			AmpWpPlugin::SERVICES
		);

		if ( $service_id instanceof Variable ) {
			return new ObjectType( Service::class );
		}

		if ( array_key_exists( (string) $service_id, $services ) ) {
			return new ObjectType( $services[ (string) $service_id ] );
		}

		return $return_type;
	}
}
