<?php
/**
 * Abstract seed base class.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli;

use DirectoryIterator;
use WP_CLI;
use WP_CLI\Utils;
use WP_CLI_Command;

final class SeedCommand extends WP_CLI_Command {

	/**
	 * Regular expression to extract the namespace from a PHP file.
	 *
	 * @var string
	 */
	const EXTRACT_NAMESPACE_REGEX_PATTERN = '/(?:namespace\s+)([^;]*)(?:\s*;)/i';

	/**
	 * Seed the WordPress installation with test data.
	 *
	 * ## OPTIONS
	 *
	 * [<seed>...]
	 * : One or more seeds to process.
	 *
	 * [--all]
	 * : Process all seeds that are found in the seed source directory.
	 *
	 * [--seed-dir=<path>]
	 * : Absolute or relative path to the directory that contains the seeds.
	 * ---
	 * default: tests/seeds
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 *     $ wp amp reference-site seed frontpage
	 *     Seeding feature "Frontpage" ...
	 *     Success: Seeded 1 of 1 feature(s).
	 *
	 * @when after_wp_load
	 * @param array $args       Array of positional arguments
	 * @param array $assoc_args Associative array of associative arguments.
	 *
	 * @subcommand "amp reference-site seed"
	 */
	public function __invoke( $args, $assoc_args ) {
		$all      = Utils\get_flag_value( $assoc_args, 'all', false );
		$seed_dir = Utils\get_flag_value( $assoc_args, 'seed-dir', 'tests/seeds' );

		WP_CLI::debug( "Configured seed dir: '{$seed_dir}'", 'amp' );

		if ( ! $all && count( $args ) < 1 ) {
			WP_CLI::error( 'You have to provide either one or more feature names or use the --all flag.' );
		}

		if ( $all && count( $args ) > 0 ) {
			WP_CLI::error( 'You cannot provide both a selection of feature names and the --all flag a tthe same time.' );
		}

		if ( $all ) {
			$feature_files = $this->get_all_feature_files( $seed_dir );
		} else {
			$feature_files = $this->validate_feature_files( $seed_dir, $args );
		}

		$features = $this->instantiate_features( $seed_dir, $feature_files );

		/** @var Seed $feature */
		foreach ( $features as $feature ) {
			WP_CLI::line( "Seeding feature '{$feature->get_feature_name()}' ..." );
		}

		WP_CLI::success(
			sprintf(
				'Seeded %d of %d feature(s).',
				count( $features ),
				count( $features )
			)
		);
	}

	/**
	 * Retrieve all feature files that are found in the provided seed
	 * directory.
	 *
	 * @param string $seed_dir Seed directory to use for discovery.
	 * @return string[] Array of absolute feature file paths found in the seed
	 *                         directory.
	 */
	private function get_all_feature_files( $seed_dir ) {
		$feature_files = [];

		foreach ( new DirectoryIterator( $seed_dir ) as $file ) {
			if ( $file->isFile() && $file->getExtension() === 'php' ) {
				$key                   = strtolower( $file->getBasename( '.php' ) );
				$feature_files[ $key ] = $file->getPathname();
			}
		}

		return $feature_files;
	}

	/**
	 * Validate the provided feature files against the seed directory.
	 *
	 * @param string   $seed_dir Seed directory to validate against.
	 * @param string[] $args     Array of feature file names.
	 * @return string[] Validated array of absolute feature file paths.
	 */
	private function validate_feature_files( $seed_dir, $args ) {
		$all_feature_files = $this->get_all_feature_files( $seed_dir );
		$feature_files     = [];

		foreach ( $args as $feature_file ) {
			$key = strtolower( $feature_file );
			if ( ! array_key_exists( $key, $all_feature_files ) ) {
				WP_CLI::warning( "Feature file '{$feature_file}' not found, skipping." );
			} else {
				$feature_files[ $key ] = $all_feature_files[ $key ];
			}
		}

		return $feature_files;
	}

	/**
	 * Instantiate the features and returns as objects.
	 *
	 * @param string   $seed_dir      Seed directory to load feature files
	 *                                from.
	 * @param string[] $feature_files Feature files to instantiate.
	 * @return Seed[] Array of Seed objects that can seed the requested
	 *                                features.
	 */
	private function instantiate_features( $seed_dir, $feature_files ) {
		$features = [];

		foreach ( $feature_files as $feature_file ) {
			$root_dir = getcwd();
			$filepath = "{$root_dir}/{$feature_file}";

			include $filepath;

			$namespace  = $this->extract_namespace_from_php_file( $filepath );
			$class_name = $namespace . rtrim( basename( $feature_file ), '.php' );

			$features[] = new $class_name();
		}

		return $features;
	}

	/**
	 * Extract the namespace from a provided PHP file.
	 *
	 * @param string $seed_dir Seed directory to get the namespace for.
	 * @return string
	 */
	private function extract_namespace_from_php_file( $filepath ) {
		$file    = file_get_contents( $filepath );
		$matches = [];

		if ( ! preg_match(
			self::EXTRACT_NAMESPACE_REGEX_PATTERN,
			$file,
			$matches
		) ) {
			return '';
		}

		return "{$matches[1]}\\";
	}
}
