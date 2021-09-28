<?php
/**
 * Class ServiceBasedPlugin.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Infrastructure;

use AmpProject\AmpWP\Exception\InvalidService;
use AmpProject\AmpWP\Infrastructure\ServiceContainer\LazilyInstantiatedService;
use WP_CLI;

/**
 * This abstract base plugin provides all the boilerplate code for working with
 * the dependency injector and the service container.
 *
 * @since 2.0
 * @internal
 */
abstract class ServiceBasedPlugin implements Plugin {

	// Main filters to control the flow of the plugin from outside code.
	const SERVICES_FILTER         = 'services';
	const BINDINGS_FILTER         = 'bindings';
	const ARGUMENTS_FILTER        = 'arguments';
	const SHARED_INSTANCES_FILTER = 'shared_instances';
	const DELEGATIONS_FILTER      = 'delegations';

	// Service identifier for the injector.
	const INJECTOR_ID = 'injector';

	// WordPress action to trigger the service registration on.
	// Use false to register as soon as the code is loaded.
	const REGISTRATION_ACTION = false;

	// Whether to enable filtering by default or not.
	const ENABLE_FILTERS_DEFAULT = true;

	// Prefixes to use.
	const HOOK_PREFIX    = '';
	const SERVICE_PREFIX = '';

	// Pattern used for detecting capitals to turn PascalCase into snake_case.
	const DETECT_CAPITALS_REGEX_PATTERN = '/[A-Z]([A-Z](?![a-z]))*/';

	/** @var bool */
	protected $enable_filters;

	/** @var Injector */
	protected $injector;

	/** @var ServiceContainer */
	protected $service_container;

	/**
	 * Instantiate a Theme object.
	 *
	 * @param bool|null             $enable_filters    Optional. Whether to
	 *                                                 enable filtering of the
	 *                                                 injector configuration.
	 * @param Injector|null         $injector          Optional. Injector
	 *                                                 instance to use.
	 * @param ServiceContainer|null $service_container Optional. Service
	 *                                                 container instance to
	 *                                                 use.
	 */
	public function __construct(
		$enable_filters = null,
		Injector $injector = null,
		ServiceContainer $service_container = null
	) {
		/*
		 * We use what is commonly referred to as a "poka-yoke" here.
		 *
		 * We need an injector and a container. We make them injectable so that
		 * we can easily provide overrides for testing, but we also make them
		 * optional and provide default implementations for easy regular usage.
		 */

		$this->enable_filters = null !== $enable_filters
			? $enable_filters
			: static::ENABLE_FILTERS_DEFAULT;

		$this->injector = null !== $injector
			? $injector
			: new Injector\SimpleInjector();

		$this->injector = $this->configure_injector( $this->injector );

		$this->service_container = null !== $service_container
			? $service_container
			: new ServiceContainer\SimpleServiceContainer();
	}

	/**
	 * Activate the plugin.
	 *
	 * @param bool $network_wide Whether the activation was done network-wide.
	 * @return void
	 */
	public function activate( $network_wide ) {
		$this->register_services();

		foreach ( $this->service_container as $service ) {
			if ( $service instanceof Activateable ) {
				$service->activate( $network_wide );
			}
		}

		\flush_rewrite_rules();
	}

	/**
	 * Deactivate the plugin.
	 *
	 * @param bool $network_wide Whether the deactivation was done network-wide.
	 * @return void
	 */
	public function deactivate( $network_wide ) {
		$this->register_services();

		foreach ( $this->service_container as $service ) {
			if ( $service instanceof Deactivateable ) {
				$service->deactivate( $network_wide );
			}
		}

		\flush_rewrite_rules();
	}

	/**
	 * Register the plugin with the WordPress system.
	 *
	 * @return void
	 * @throws InvalidService If a service is not valid.
	 */
	public function register() {
		if ( false !== static::REGISTRATION_ACTION ) {
			\add_action(
				static::REGISTRATION_ACTION,
				[ $this, 'register_services' ]
			);
		} else {
			$this->register_services();
		}
	}

	/**
	 * Register the individual services of this plugin.
	 *
	 * @throws InvalidService If a service is not valid.
	 *
	 * @return void
	 */
	public function register_services() {
		// Bail early so we don't instantiate services twice.
		if ( count( $this->service_container ) > 0 ) {
			return;
		}

		// Add the injector as the very first service.
		$this->service_container->put(
			static::SERVICE_PREFIX . static::INJECTOR_ID,
			$this->injector
		);

		$services = $this->get_service_classes();

		if ( $this->enable_filters ) {
			/**
			 * Filter the default services that make up this plugin.
			 *
			 * This can be used to add services to the service container for
			 * this plugin.
			 *
			 * @param array<string> $services Associative array of identifier =>
			 *                                class mappings. The provided
			 *                                classes need to implement the
			 *                                Service interface.
			 */
			$filtered_services = \apply_filters(
				static::HOOK_PREFIX . static::SERVICES_FILTER,
				$services
			);

			$services = $this->validate_services( $filtered_services, $services );
		}

		while ( null !== key( $services ) ) {
			$id    = $this->maybe_resolve( key( $services ) );
			$class = $this->maybe_resolve( current( $services ) );

			// Delay registering the service until all requirements are met.
			if (
				is_a( $class, HasRequirements::class, true )
				&&
				! $this->requirements_are_met( $id, $class, $services )
			) {
				continue;
			}

			$this->schedule_potential_service_registration( $id, $class );

			next( $services );
		}
	}

	/**
	 * Determine if the requirements for a service to be registered are met.
	 *
	 * This also hooks up another check in the future to the registration action(s) of its requirements.
	 *
	 * @param string   $id       Service ID of the service with requirements.
	 * @param string   $class    Service FQCN of the service with requirements.
	 * @param string[] $services List of services to be registered.
	 *
	 * @throws InvalidService If the required service is not recognized.
	 *
	 * @return bool Whether the requirements for the service has been met.
	 */
	protected function requirements_are_met( $id, $class, &$services ) {
		$missing_requirements = $this->collect_missing_requirements( $class, $services );

		if ( empty( $missing_requirements ) ) {
			return true;
		}

		foreach ( $missing_requirements as $missing_requirement ) {
			if ( is_a( $missing_requirement, Delayed::class, true ) ) {
				$action = $missing_requirement::get_registration_action();

				if ( \did_action( $action ) ) {
					continue;
				}

				/*
				 * The current service depends on another service that is Delayed and hasn't been registered yet
				 * and for which the registration action has not yet passed.
				 *
				 * Therefore, we postpone the registration of the current service up until the requirement's
				 * action has passed.
				 *
				 * We don't register the service right away, though, we will first check whether at that point,
				 * the requirements have been met.
				 *
				 * Note that badly configured requirements can lead to services that will never register at all.
				 */

				\add_action(
					$action,
					function () use ( $id, $class, $services ) {
						if ( ! $this->requirements_are_met( $id, $class, $services ) ) {
							return;
						}

						$this->schedule_potential_service_registration( $id, $class );
					},
					PHP_INT_MAX
				);

				next( $services );
				return false;
			}
		}

		/*
		 * The registration actions from all of the requirements were already processed. This means that the missing
		 * requirement(s) are about to be registered, they just weren't encountered yet while traversing the services
		 * map. Therefore, we skip registration for now and move this particular service to the end of the service map.
		 *
		 * Note: Moving the service to the end of the service map advances the internal array pointer to the next service.
		 */
		unset( $services[ $id ] );
		$services[ $id ] = $class;

		return false;
	}

	/**
	 * Collect the list of missing requirements for a service which has requirements.
	 *
	 * @param string   $class Service FQCN of the service with requirements.
	 * @param string[] $services List of services to register.
	 *
	 * @throws InvalidService If the required service is not recognized.
	 *
	 * @return string[] List of missing requirements as a $service_id => $service_class mapping.
	 */
	protected function collect_missing_requirements( $class, $services ) {
		$requirements = $class::get_requirements();

		$missing = [];

		foreach ( $requirements as $requirement ) {
			// Bail if it requires a service that is not recognized.
			if ( ! array_key_exists( $requirement, $services ) ) {
				throw InvalidService::from_service_id( $requirement );
			}

			if ( $this->get_container()->has( $requirement ) ) {
				continue;
			}

			$missing[ $requirement ] = $services[ $requirement ];
		}

		return $missing;
	}

	/**
	 * Validates the services array to make sure it is in a usable shape.
	 *
	 * As the array of services could be filtered, we need to ensure it is
	 * always in a state where it doesn't throw PHP warnings or errors.
	 *
	 * @param mixed    $services Services to validate.
	 * @param string[] $fallback Fallback value to use if $services is not
	 *                           salvageable.
	 * @return string[] Validated array of service mappings.
	 */
	protected function validate_services( $services, $fallback ) {
		// If we don't have an array, something went wrong with filtering.
		// Just use the fallback value in this case.
		if ( ! is_array( $services ) ) {
			return $fallback;
		}

		// Make a copy so we can safely mutate while iterating.
		$services_to_check = $services;

		foreach ( $services_to_check as $identifier => $fqcn ) {
			// Ensure we have valid identifiers we can refer to.
			// If not, generate them from the FQCN.
			if ( empty( $identifier ) || ! is_string( $identifier ) ) {
				unset( $services[ $identifier ] );
				$identifier              = $this->get_identifier_from_fqcn( $fqcn );
				$services[ $identifier ] = $fqcn;
			}

			// Verify that the FQCN is valid and points to an existing class.
			// If not, skip this service.
			if ( empty( $fqcn ) || ! is_string( $fqcn ) || ! class_exists( $fqcn ) ) {
				unset( $services[ $identifier ] );
			}
		}

		return $services;
	}

	/**
	 * Generate a valid identifier for a provided FQCN.
	 *
	 * @param string $fqcn FQCN to use as base to generate an identifer.
	 * @return string Identifier to use for the provided FQCN.
	 */
	protected function get_identifier_from_fqcn( $fqcn ) {
		// Retrieve the short name from the FQCN first.
		$short_name = substr( $fqcn, strrpos( $fqcn, '\\' ) + 1 );

		// Turn camelCase or PascalCase into snake_case.
		$snake_case = strtolower(
			trim(
				preg_replace( self::DETECT_CAPITALS_REGEX_PATTERN, '_$0', $short_name ),
				'_'
			)
		);

		return $snake_case;
	}

	/**
	 * Schedule the potential registration of a single service.
	 *
	 * This takes into account whether the service registration needs to be delayed or not.
	 *
	 * @param string $id ID of the service to register.
	 * @param string $class Class of the service to register.
	 */
	protected function schedule_potential_service_registration( $id, $class ) {
		if ( is_a( $class, Delayed::class, true ) ) {
			$registration_action = $class::get_registration_action();

			if ( \did_action( $registration_action ) ) {
				$this->maybe_register_service( $id, $class );
			} else {
				\add_action(
					$registration_action,
					function () use ( $id, $class ) {
						$this->maybe_register_service( $id, $class );
					}
				);
			}
		} else {
			$this->maybe_register_service( $id, $class );
		}
	}

	/**
	 * Register a single service, provided its conditions are met.
	 *
	 * @param string $id ID of the service to register.
	 * @param string $class Class of the service to register.
	 */
	protected function maybe_register_service( $id, $class ) {
		// Ensure we don't register the same service more than once.
		if ( $this->service_container->has( $id ) ) {
			return;
		}

		// Only instantiate services that are actually needed.
		if ( is_a( $class, Conditional::class, true )
			&& ! $class::is_needed() ) {
			return;
		}

		$service = $this->instantiate_service( $class );

		$this->service_container->put( $id, $service );

		if ( $service instanceof CliCommand && defined( 'WP_CLI' ) && WP_CLI ) {
			WP_CLI::add_command( $service::get_command_name(), $service );
		}

		if ( $service instanceof Registerable ) {
			$service->register();
		}
	}

	/**
	 * Get the service container that contains the services that make up the
	 * plugin.
	 *
	 * @return ServiceContainer Service container of the plugin.
	 */
	public function get_container() {
		return $this->service_container;
	}

	/**
	 * Instantiate a single service.
	 *
	 * @param string $class Service class to instantiate.
	 *
	 * @throws InvalidService If the service could not be properly instantiated.
	 *
	 * @return Service Instantiated service.
	 */
	protected function instantiate_service( $class ) {
		/*
		 * If the service is not registerable, we default to lazily instantiated
		 * services here for some basic optimization.
		 *
		 * The services will be properly instantiated once they are retrieved
		 * from the service container.
		 */
		if (
			! is_a( $class, Registerable::class, true )
			&&
			(
				! defined( 'WP_CLI' )
				||
				! WP_CLI
				||
				! is_a( $class, CliCommand::class, true )
			)
		) {
			return new LazilyInstantiatedService(
				function () use ( $class ) {
					return $this->injector->make( $class );
				}
			);
		}

		// The service needs to be registered, so instantiate right away.
		$service = $this->injector->make( $class );

		if ( ! $service instanceof Service ) {
			throw InvalidService::from_service( $service );
		}

		return $service;
	}

	/**
	 * Configure the provided injector.
	 *
	 * This method defines the mappings that the injector knows about, and the
	 * logic it requires to make more complex instantiations work.
	 *
	 * For more complex plugins, this should be extracted into a separate
	 * object
	 * or into configuration files.
	 *
	 * @param Injector $injector Injector instance to configure.
	 * @return Injector Configured injector instance.
	 */
	protected function configure_injector( Injector $injector ) {
		$bindings         = $this->get_bindings();
		$shared_instances = $this->get_shared_instances();
		$arguments        = $this->get_arguments();
		$delegations      = $this->get_delegations();

		if ( $this->enable_filters ) {
			/**
			 * Filter the default bindings that are provided by the plugin.
			 *
			 * This can be used to swap implementations out for alternatives.
			 *
			 * @param array<string> $bindings Associative array of interface =>
			 *                                implementation bindings. Both
			 *                                should be FQCNs.
			 */
			$bindings = (array) \apply_filters(
				static::HOOK_PREFIX . static::BINDINGS_FILTER,
				$bindings
			);

			/**
			 * Filter the default argument bindings that are provided by the
			 * plugin.
			 *
			 * This can be used to override scalar values.
			 *
			 * @param array<array> $arguments Associative array of class =>
			 *                                arguments mappings. The arguments
			 *                                array maps argument names to
			 *                                values.
			 */
			$arguments = (array) \apply_filters(
				static::HOOK_PREFIX . static::ARGUMENTS_FILTER,
				$arguments
			);

			/**
			 * Filter the instances that are shared by default by the plugin.
			 *
			 * This can be used to turn objects that were added externally into
			 * shared instances.
			 *
			 * @param array<string> $shared_instances Array of FQCNs to turn
			 *                                        into shared objects.
			 */
			$shared_instances = (array) \apply_filters(
				static::HOOK_PREFIX . static::SHARED_INSTANCES_FILTER,
				$shared_instances
			);

			/**
			 * Filter the instances that are shared by default by the plugin.
			 *
			 * This can be used to turn objects that were added externally into
			 * shared instances.
			 *
			 * @param array<string> $delegations Associative array of class =>
			 *                                   callable mappings.
			 */
			$delegations = (array) \apply_filters(
				static::HOOK_PREFIX . static::DELEGATIONS_FILTER,
				$delegations
			);
		}

		foreach ( $bindings as $from => $to ) {
			$from = $this->maybe_resolve( $from );
			$to   = $this->maybe_resolve( $to );

			$injector = $injector->bind( $from, $to );
		}

		foreach ( $arguments as $class => $argument_map ) {
			$class = $this->maybe_resolve( $class );

			foreach ( $argument_map as $name => $value ) {
				// We don't try to resolve the $value here, as we might want to
				// pass a callable as-is.
				$name = $this->maybe_resolve( $name );

				$injector = $injector->bind_argument( $class, $name, $value );
			}
		}

		foreach ( $shared_instances as $shared_instance ) {
			$shared_instance = $this->maybe_resolve( $shared_instance );

			$injector = $injector->share( $shared_instance );
		}

		foreach ( $delegations as $class => $callable ) {
			// We don't try to resolve the $callable here, as we want to pass it
			// on as-is.
			$class = $this->maybe_resolve( $class );

			$injector = $injector->delegate( $class, $callable );
		}

		return $injector;
	}

	/**
	 * Get the list of services to register.
	 *
	 * @return array<string> Associative array of identifiers mapped to fully
	 *                       qualified class names.
	 */
	protected function get_service_classes() {
		return [];
	}

	/**
	 * Get the bindings for the dependency injector.
	 *
	 * The bindings let you map interfaces (or classes) to the classes that
	 * should be used to implement them.
	 *
	 * @return array<string> Associative array of fully qualified class names.
	 */
	protected function get_bindings() {
		return [];
	}

	/**
	 * Get the argument bindings for the dependency injector.
	 *
	 * The argument bindings let you map specific argument values for specific
	 * classes.
	 *
	 * @return array<array> Associative array of arrays mapping argument names
	 *                      to argument values.
	 */
	protected function get_arguments() {
		return [];
	}

	/**
	 * Get the shared instances for the dependency injector.
	 *
	 * These classes will only be instantiated once by the injector and then
	 * reused on subsequent requests.
	 *
	 * This effectively turns them into singletons, without any of the
	 * drawbacks of the actual Singleton anti-pattern.
	 *
	 * @return array<string> Array of fully qualified class names.
	 */
	protected function get_shared_instances() {
		return [];
	}

	/**
	 * Get the delegations for the dependency injector.
	 *
	 * These are basically factories to provide custom instantiation logic for
	 * classes.
	 *
	 * @return array<callable> Associative array of callables.
	 */
	protected function get_delegations() {
		return [];
	}

	/**
	 * Maybe resolve a value that is a callable instead of a scalar.
	 *
	 * Values that are passed through this method can optionally be provided as
	 * callables instead of direct values and will be evaluated when needed.
	 *
	 * @param mixed $value Value to potentially resolve.
	 * @return mixed Resolved or unchanged value.
	 */
	protected function maybe_resolve( $value ) {
		if ( is_callable( $value ) ) {
			$value = $value( $this->injector, $this->service_container );
		}

		return $value;
	}
}
