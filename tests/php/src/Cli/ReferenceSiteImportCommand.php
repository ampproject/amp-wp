<?php
/**
 * Reference site import class.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli;

use AmpProject\AmpWP\Tests\Cli\Import\ImportWxrFile;
use Exception;
use Google\Cloud\Storage\StorageClient;
use RuntimeException;
use WP_CLI;
use WP_CLI_Command;
use WP_CLI\Utils;

final class ReferenceSiteImportCommand extends WP_CLI_Command {

	/**
	 * Associative array of result counts.
	 *
	 * @var int[]
	 */
	private $results = [];

	/**
	 * Associative array of detected errors.
	 *
	 * @var int[]
	 */
	private $errors = [];

	/**
	 * Imports content from a reference site definition.
	 *
	 * Uses the WordPress Importer plugin behind the scenes for performing data
	 * migrations.
	 *
	 * ## OPTIONS
	 *
	 * <file>
	 * : Path to the reference site definition to import.
	 *
	 * [--empty-content]
	 * : Empty the site content (posts, comments, and terms) before importing the reference content.
	 *
	 * [--empty-uploads]
	 * : Empty the site uploads folder before importing the reference content. This can only be used in conjunction with --empty-content.
	 *
	 * [--empty-extensions]
	 * : Empty the extensions folder (plugins & themes except for the AMP & WordPress Importer plugin) before importing the reference content.
	 *
	 * [--empty-options]
	 * : Empty the site options before importing the reference content and only leave default WordPress options in place.
	 *
	 * [--skip-site-meta]
	 * : Skip importing the site meta information like blog name or description.
	 *
	 * ## EXAMPLES
	 *
	 *     # Import content from a reference site definition file
	 *     $ wp amp reference-site import example-site
	 *     Starting the import process...
	 *     Processing post #1 ("Hello world!") (post_type: post)
	 *     -- 1 of 1
	 *     -- Tue, 21 Jun 2016 05:31:12 +0000
	 *     -- Imported post as post_id #1
	 *     Success: Finished importing from 'example-site' file.
	 *
	 * @when after_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		try {
			( new WpImporterCompat() )->prepare_import();
		} catch ( Exception $exception ) {
			WP_CLI::error( 'Unable to import reference site: ' . $exception->getMessage() );
		}

		$storage = new StorageClient();
		$storage->registerStreamWrapper();

		list( $site_definition_file ) = $args;

		$empty_content    = Utils\get_flag_value( $assoc_args, 'empty-content', false );
		$empty_uploads    = Utils\get_flag_value( $assoc_args, 'empty-uploads', false );
		$empty_extensions = Utils\get_flag_value( $assoc_args, 'empty-extensions', false );
		$empty_options    = Utils\get_flag_value( $assoc_args, 'empty-options', false );

		if ( 0 !== substr_compare( $site_definition_file, '.json', -5 ) ) {
			$site_definition_file .= '.json';
		}

		if ( ! path_is_absolute( $site_definition_file ) ) {
			$site_definition_file = ReferenceSiteCommandNamespace::REFERENCE_SITES_ROOT . $site_definition_file;
		}

		if ( ! file_exists( $site_definition_file )
			|| ! is_readable( $site_definition_file ) ) {
			WP_CLI::error( "The provided site definition file '{$site_definition_file}' could not be read." );
		}

		WP_CLI::log( 'Starting the reference site import process...' );

		try {
			$site_definition = $this->load_site_definition( $site_definition_file );
		} catch ( Exception $exception ) {
			WP_CLI::error( "The provided site definition file '{$site_definition_file}' could not be parsed: {$exception->getMessage()}" );
		}

		if ( $empty_extensions ) {
			$this->empty_extensions();
		}

		if ( $empty_content ) {
			$this->empty_site( $empty_uploads );
		}

		if ( $empty_options ) {
			$this->empty_options();
		}

		WP_CLI::log(
			'Importing reference site: '
			. WP_CLI::colorize( "%y{$site_definition->get_name()}%n (%gv{$site_definition->get_version()}%n)" )
		);

		WP_CLI::log( $site_definition->get_description() );

		foreach ( $site_definition->get_attributions() as $attribution ) {
			WP_CLI::log( WP_CLI::colorize( "%b{$attribution}%n" ) );
		}

		$this->import_site( $site_definition, $assoc_args );
	}

	/**
	 * Load and parse the site definition file.
	 *
	 * @param string $site_definition_file
	 * @return SiteDefinition Parsed site definition.
	 */
	private function load_site_definition( $site_definition_file ) {
		try {
			$site_definition_json = file_get_contents( $site_definition_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents -- Needed for stream wrapper support.
		} catch ( Exception $exception ) {
			$site_definition_json = false;
		}

		if ( empty( $site_definition_json ) ) {
			throw new RuntimeException( 'Failed to load the site definition file into memory.' );
		}

		$site_definition = json_decode( $site_definition_json, true );
		if ( JSON_ERROR_NONE !== json_last_error() ) {
			throw new RuntimeException( 'Failed to parse the site definition JSON data - ' . json_last_error_msg() );
		}

		return new SiteDefinition( $site_definition );
	}

	/**
	 * Import an entire site.
	 *
	 * @param SiteDefinition $site_definition Site definition of the site to
	 *                                        import.
	 * @param array          $assoc_args      Associative array of associative
	 *                                        arguments.
	 */
	private function import_site( SiteDefinition $site_definition, $assoc_args ) {
		if ( ! Utils\get_flag_value( $assoc_args, 'skip-site-meta', false ) ) {
			$this->add_result(
				'Site Meta',
				( new Import\ImportSiteMeta( $site_definition ) )->process()
			);
		}

		foreach ( $site_definition->get_import_steps() as $import_step ) {
			switch ( $import_step['type'] ) {
				case 'activate_theme':
					$this->add_result(
						'Theme',
						( new Import\ActivateTheme( $import_step['theme'] ) )->process()
					);
					break;
				case 'activate_plugin':
					$this->add_result(
						'Plugin',
						( new Import\ActivatePlugin( $import_step['plugin'] ) )->process()
					);
					break;
				case 'import_wxr_file':
					$wxr_path = $import_step['filename'];

					if (
						! path_is_absolute( $wxr_path )
						&&
						! preg_match( ImportWxrFile::IS_REMOTE_URL, $wxr_path )
					) {
						$wxr_path = ReferenceSiteCommandNamespace::REFERENCE_SITES_ROOT . $wxr_path;
					}

					$this->add_result(
						'Post',
						( new Import\ImportWxrFile( $wxr_path ) )->process()
					);
					break;
				case 'import_options':
					$this->add_result(
						'Option',
						( new Import\ImportOptions( $import_step['options'] ) )->process()
					);
					break;
				case 'import_theme_mods':
					$this->add_result(
						'Theme Mod',
						( new Import\ImportThemeMods( $import_step['theme_mods'] ) )->process()
					);
					break;
				case 'import_widgets':
					$this->add_result(
						'Widget',
						( new Import\ImportWidgets( $import_step['widgets'] ) )->process()
					);
					break;
				case 'import_customizer_settings':
					$this->add_result(
						'Customizer Setting',
						( new Import\ImportCustomizerSettings( $import_step['settings'] ) )->process()
					);
					break;
			}
		}

		$this->print_results( $assoc_args );
	}

	/**
	 * Empty the site content.
	 *
	 * @param bool $empty_uploads Whether to empty the uploads folder as well.
	 */
	private function empty_site( $empty_uploads ) {
		$command = 'site empty --yes';

		if ( $empty_uploads ) {
			$command .= ' --uploads';
		}

		WP_CLI::log(
			$empty_uploads
				? 'Emptying the site content and uploads...'
				: 'Emptying the site content...'
		);

		WP_CLI::runcommand( $command );
	}

	/**
	 * Empty the site's extension folders.
	 *
	 * This removes all plugins & themes except for the AMP and the WordPress
	 * Importer plugins.
	 */
	private function empty_extensions() {
		WP_CLI::log( 'Emptying the site extensions...' );

		$plugins = json_decode(
			WP_CLI::runcommand(
				'plugin list --field=name --format=json',
				[ 'return' => true ]
			),
			JSON_OBJECT_AS_ARRAY
		);

		$plugins = array_filter(
			$plugins,
			static function ( $plugin ) {
				return ! in_array( $plugin, [ 'amp', 'wordpress-importer' ], true );
			}
		);

		WP_CLI::runcommand( 'plugin delete ' . implode( ' ', $plugins ) );
	}

		$themes = json_decode(
			WP_CLI::runcommand(
				'theme list --fields=name,status --format=json',
				[ 'return' => true ]
			),
			JSON_OBJECT_AS_ARRAY
		);

		$themes = array_reduce(
			$themes,
			static function ( $themes, $theme ) {
				return 'inactive' === $theme['status'] ? $themes . ' ' . $theme['name'] : $themes;
			},
			''
		);

		WP_CLI::runcommand( 'theme delete ' . $themes );
	}

	/**
	 * Empty the site's options.
	 *
	 * This removes all options and then populates the database with the default
	 * options for an empty WordPress site.
	 */
	private function empty_options() {
		global $wpdb;

		WP_CLI::log( 'Emptying the site options...' );

		if ( ! function_exists( '__get_option' ) ) {
			/** WordPress Administration API */
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		if ( ! function_exists( 'populate_options' ) ) {
			/** WordPress Schema API */
			require_once ABSPATH . 'wp-admin/includes/schema.php';
		}

		$siteurl = get_option( 'siteurl' );
		$home    = get_option( 'home' );

		$wpdb->query( sprintf( 'TRUNCATE `%s`;', $wpdb->options ) );

		wp_cache_flush();

		populate_options(
			[
				'siteurl'             => $siteurl,
				'home'                => $home,
				'permalink_structure' => '%postname%',
			]
		);

		populate_roles();

		WP_CLI::runcommand( 'plugin activate amp' );

		wp_cache_flush();
	}

	/**
	 * Add a result to the array of logged results.
	 *
	 * @param string $type  Type of result to add.
	 * @param int    $count Result count.
	 */
	private function add_result( $type, $count ) {
		if ( $count > 0 ) {
			if ( ! array_key_exists( $type, $this->results ) ) {
				$this->results[ $type ] = 0;
			}

			$this->results[ $type ] += $count;
		} else {
			if ( ! array_key_exists( $type, $this->errors ) ) {
				$this->errors[ $type ] = 0;
			}

			$this->errors[ $type ] += abs( $count );
		}
	}

	/**
	 * Print the import summary as a table with counts per object type.
	 *
	 * @param array $assoc_args Associative array of associative arguments.
	 */
	private function print_results( $assoc_args ) {
		$keys = array_unique(
			array_merge(
				array_keys( $this->results ),
				array_keys( $this->errors )
			)
		);

		$results = [];

		foreach ( $keys as $key ) {
			$count = array_key_exists( $key, $this->results )
				? $this->results[ $key ]
				: 0;

			if ( $count > 0 ) {
				$count = "%G{$count}%n";
			}

			$errors = array_key_exists( $key, $this->errors )
				? $this->errors[ $key ]
				: 0;

			if ( $errors > 0 ) {
				$errors = "%R{$errors}%n";
			}

			$results[ $key ] = [
				'Type'              => WP_CLI::colorize( "%b{$key}%n" ),
				'Imported elements' => WP_CLI::colorize( $count ),
				'Errors'            => WP_CLI::colorize( $errors ),
			];
		}

		WP_CLI::log( WP_CLI::colorize( "\n%BSummary:%n" ) );

		$formatter = new WP_CLI\Formatter( $assoc_args, [ 'Type', 'Imported elements', 'Errors' ] );
		$formatter->display_items( $results, true );
	}
}
