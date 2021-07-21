<?php
/**
 * Plugin class.
 *
 * @package AmpProject\AmpWP\PhpCssParserInstall
 */

namespace AmpProject\AmpWP\PhpCssParserInstall;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\IO\IOInterface;
use Composer\Package\AliasPackage;
use Composer\Package\CompleteAliasPackage;
use Composer\Package\CompletePackageInterface;
use Composer\Package\Link;
use Composer\Package\Loader\ArrayLoader;
use Composer\Package\Locker;
use Composer\Plugin\PluginInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\LockArrayRepository;
use Composer\Script\Event;
use Composer\Script\ScriptEvents;
use Composer\Semver\Constraint\Constraint;

/**
 * This Composer plugin patches the `sabberworm/php-css-parser` package so that it autoloads the correct namespace.
 */
class Plugin implements PluginInterface, EventSubscriberInterface {

	/** @var string */
	const CSS_PARSER_PACKAGE_NAME = 'sabberworm/php-css-parser';

	/**
	 * Apply plugin modifications to Composer
	 *
	 * @param Composer    $composer Composer instance.
	 * @param IOInterface $io       IO instance.
	 */
	public function activate( Composer $composer, IOInterface $io ) {
	}

	/**
	 * Remove any hooks from Composer
	 *
	 * This will be called when a plugin is deactivated before being
	 * uninstalled, but also before it gets upgraded to a new version
	 * so the old one can be deactivated and the new one activated.
	 *
	 * @param Composer    $composer Composer instance.
	 * @param IOInterface $io       IO instance.
	 */
	public function deactivate( Composer $composer, IOInterface $io ) {
	}

	/**
	 * Prepare the plugin to be uninstalled
	 *
	 * This will be called after deactivate.
	 *
	 * @param Composer    $composer Composer instance.
	 * @param IOInterface $io       IO instance.
	 */
	public function uninstall( Composer $composer, IOInterface $io ) {
	}

	/**
	 * Returns an array of event names this subscriber wants to listen to.
	 *
	 * The array keys are event names and the value can be:
	 *
	 * * The method name to call (priority defaults to 0)
	 * * An array composed of the method name to call and the priority
	 * * An array of arrays composed of the method names to call and respective
	 *   priorities, or 0 if unset
	 *
	 * For instance:
	 *
	 * * array('eventName' => 'methodName')
	 * * array('eventName' => array('methodName', $priority))
	 * * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
	 *
	 * @return array The event names to listen to
	 */
	public static function getSubscribedEvents() {
		return [
			ScriptEvents::PRE_AUTOLOAD_DUMP => 'onPreAutoloadDump',
		];
	}

	/**
	 * Patch the package before the autoload file is generated.
	 *
	 * @param Event $event Composer event.
	 */
	public static function onPreAutoloadDump( Event $event ) {
		$package_locker   = $event->getComposer()->getLocker();
		$local_repository = $event->getComposer()->getRepositoryManager()->getLocalRepository();

		self::patchComposerLockFile( $package_locker );
		self::patchPackage( $local_repository );
	}

	/**
	 * Patch the `composer.lock` file to use the correct dependency requirements and `psr-4` namespace.
	 *
	 * @param Locker $package_locker Package locker instance.
	 */
	private static function patchComposerLockFile( Locker $package_locker ) {
		$lock_data = $package_locker->getLockData();

		if ( ! isset( $lock_data['packages'] ) ) {
			return;
		}

		$package_key = array_search( self::CSS_PARSER_PACKAGE_NAME, array_column( $lock_data['packages'], 'name' ), true );

		if ( ! $package_key ) {
			return;
		}

		unset( $lock_data['packages'][ $package_key ]['suggest'] );
		$lock_data['packages'][ $package_key ]['require'] = [
			'php' => '>=5.3.2',
		];

		$lock_data['packages'][ $package_key ]['autoload']['psr-4']['Sabberworm\\CSS\\'] = 'lib/Sabberworm/CSS/';

		$package_locker->setLockData(
			self::getLockedRepository( $lock_data['packages'], $lock_data['aliases'] )->getPackages(),
			self::getLockedRepository( $lock_data['packages-dev'], $lock_data['aliases'] )->getPackages(),
			$lock_data['platform'],
			$lock_data['platform-dev'],
			$lock_data['aliases'],
			$lock_data['minimum-stability'],
			$lock_data['stability-flags'],
			$lock_data['prefer-stable'],
			$lock_data['prefer-lowest'],
			$lock_data['platform-overrides']
		);
	}

	/**
	 * Patch package to point to the correct `psr-4` namespace so that it can be autoloaded.
	 *
	 * @param InstalledRepositoryInterface $local_repository Local repository instance.
	 */
	private static function patchPackage( InstalledRepositoryInterface $local_repository ) {
		$package = $local_repository->findPackage( self::CSS_PARSER_PACKAGE_NAME, '*' );

		if ( ! $package ) {
			return;
		}

		$root_package = $package instanceof CompleteAliasPackage ? $package->getAliasOf() : $package;

		$root_package->setRequires(
			[
				'php' => new Link(
					self::CSS_PARSER_PACKAGE_NAME,
					'php',
					new Constraint( '>=', '5.3.2' ),
					Link::TYPE_REQUIRE,
					'>=5.3.2'
				),
			]
		);

		$root_package->setAutoload(
			[
				'psr-4' => [
					'Sabberworm\\CSS\\' => 'lib/Sabberworm/CSS/',
				],
			]
		);
	}

	/**
	 * Converts string array representation of locked packages into a \Composer\Repository\LockArrayRepository object.
	 *
	 * Adapted from \Composer\Package\Locker::getLockedRepository().
	 *
	 * @param array      $locked_packages Array of locked packages.
	 * @param array|null $locked_aliases  Array of locked package aliases.
	 *
	 * @return array|LockArrayRepository
	 */
	private static function getLockedRepository( array $locked_packages, $locked_aliases ) {
		$loader   = new ArrayLoader( null, true );
		$packages = new LockArrayRepository();

		if ( empty( $locked_packages ) ) {
			return $packages;
		}

		if ( isset( $locked_packages[0]['name'] ) ) {
			$package_by_name = [];
			foreach ( $locked_packages as $info ) {
				$package = $loader->load( $info );
				$packages->addPackage( $package );
				$package_by_name[ $package->getName() ] = $package;

				if ( $package instanceof AliasPackage ) {
					$package_by_name[ $package->getAliasOf()->getName() ] = $package->getAliasOf();
				}
			}

			if ( $locked_aliases ) {
				foreach ( $locked_aliases as $alias ) {
					if ( isset( $package_by_name[ $alias['package'] ] ) ) {
						if ( $package_by_name[ $alias['package'] ] instanceof CompletePackageInterface ) {
							$alias_pkg = new CompleteAliasPackage( $package_by_name[ $alias['package'] ], $alias['alias_normalized'], $alias['alias'] );
						} else {
							$alias_pkg = new AliasPackage( $package_by_name[ $alias['package'] ], $alias['alias_normalized'], $alias['alias'] );
						}
						$alias_pkg->setRootPackageAlias( true );
						$packages->addPackage( $alias_pkg );
					}
				}
			}

			return $packages;
		}

		return [];
	}
}
