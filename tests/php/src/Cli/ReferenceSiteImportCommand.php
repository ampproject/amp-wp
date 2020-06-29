<?php
/**
 * Reference site import class.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli;

use AmpProject\AmpWP\Tests\Cli\Step;
use Exception;
use RuntimeException;
use WP_CLI;
use WP_CLI_Command;

final class ReferenceSiteImportCommand extends WP_CLI_Command {

	const REFERENCE_SITES_ROOT = AMP__DIR__ . '/tests/reference-sites/';

	public $processed_posts = [];

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
	 * ## EXAMPLES
	 *
	 *     # Import content from a reference site definition file
	 *     $ wp amp reference-site import example-site.json
	 *     Starting the import process...
	 *     Processing post #1 ("Hello world!") (post_type: post)
	 *     -- 1 of 1
	 *     -- Tue, 21 Jun 2016 05:31:12 +0000
	 *     -- Imported post as post_id #1
	 *     Success: Finished importing from 'example-site.json' file.
	 *
	 * @when after_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		try {
			$this->assert_importer_plugin_is_available();
		} catch ( Exception $exception ) {
			WP_CLI::error( 'Unable to import reference site: ' . $exception->getMessage() );
		}

		list( $site_definition_file ) = $args;
		if ( ! path_is_absolute( $site_definition_file ) ) {
			$site_definition_file = self::REFERENCE_SITES_ROOT . $site_definition_file;
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

		WP_CLI::log(
			'Importing reference site: '
			. WP_CLI::colorize( "%y{$site_definition->get_name()}%n (%gv{$site_definition->get_version()}%n)" )
		);

		WP_CLI::log( $site_definition->get_description() );

		foreach ( $site_definition->get_attributions() as $attribution ) {
			WP_CLI::log( WP_CLI::colorize( "%b{$attribution}%n" ) );
		}

		$this->import_site( $site_definition );
	}

	/**
	 * Load and parse the site definition file.
	 *
	 * @param string $site_definition_file
	 * @return SiteDefinition Parsed site definition.
	 */
	private function load_site_definition( $site_definition_file ) {
		try {
			$site_definition_json = file_get_contents( $site_definition_file );
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
	 */
	private function import_site( SiteDefinition $site_definition ) {
		foreach ( $site_definition->get_steps() as $step ) {
			switch ( $step['type'] ) {
				case 'activate_theme':
					( new Step\ActivateTheme( $step['theme'] ) )->process();
					break;
				case 'activate_plugin':
					( new Step\ActivatePlugin( $step['plugin'] ) )->process();
					break;
				case 'import_wxr_file':
					$wxr_path = $step['filename'];

					if ( ! path_is_absolute( $wxr_path ) ) {
						$wxr_path = self::REFERENCE_SITES_ROOT . $wxr_path;
					}
					( new Step\ImportWxrFile( $wxr_path ) )->process();
					break;
				case 'import_options':
					( new Step\ImportOptions( $step['options'] ) )->process();
					break;
				case 'import_widgets':
					( new Step\ImportWidgets( $step['widgets'] ) )->process();
					break;
				case 'import_customizer_settings':
					( new Step\ImportCustomizerSettings( $step['settings'] ) )->process();
					break;
			}
		}
	}

	private function import_widgets( $widgets ) {
		foreach ( $widgets as $key => $value ) {
			var_dump( "$key => $value" );
		}
	}

	private function import_customizer_settings( $settings ) {
		foreach ( $settings as $key => $value ) {
			var_dump( "$key => $value" );
		}
	}

	/**
	 * Determines whether the requested importer is available.
	 */
	private function assert_importer_plugin_is_available() {
		if ( class_exists( 'WP_Import' ) ) {
			return true;
		}

		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		$plugins            = get_plugins();
		$wordpress_importer = 'wordpress-importer/wordpress-importer.php';
		if ( ! array_key_exists( $wordpress_importer, $plugins ) ) {
			throw new RuntimeException( "WordPress Importer needs to be installed. Try 'wp plugin install wordpress-importer'." );
		}

		if ( ! class_exists( 'WP_Importer' ) ) {
			$class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';
			if ( file_exists( $class_wp_importer ) )
				require $class_wp_importer;
		}

		$wordpress_importer_path = dirname( AMP__DIR__ ) . '/wordpress-importer';

		require_once "{$wordpress_importer_path}/compat.php";
		require_once "{$wordpress_importer_path}/parsers/class-wxr-parser.php";
		require_once "{$wordpress_importer_path}/parsers/class-wxr-parser-simplexml.php";
		require_once "{$wordpress_importer_path}/parsers/class-wxr-parser-xml.php";
		require_once "{$wordpress_importer_path}/parsers/class-wxr-parser-regex.php";
		require_once "{$wordpress_importer_path}/class-wp-import.php";
	}
}
