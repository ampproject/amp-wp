<?php
/**
 * Class GenerateCommand.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Cli;

use AmpProject\AmpWP\Documentation\Model\Root;
use AmpProject\AmpWP\Documentation\Parser\Parser;
use AmpProject\AmpWP\Documentation\Templating\Markdown;
use AmpProject\AmpWP\Documentation\Templating\MustacheTemplateEngine;
use AmpProject\AmpWP\Documentation\Templating\TemplateEngine;
use Exception;
use Generator;
use WP_CLI;
use WP_Error;

/**
 * Generate the reference documentation by parsing the source code.
 *
 * @package AmpProject\AmpWP\Docs\Cli
 */
final class GenerateCommand {

	/**
	 * Generates the reference documentation by parsing the source code.
	 *
	 * Uses phpDocumentor and custom reflector from the WP DevHub parser plugin.
	 *
	 * ## OPTIONS
	 *
	 * [<source_folder>]
	 * : Path to the source folder that contains the source files to be parsed. Defaults to AMP plugin directory.
	 *
	 * [<destination_folder>]
	 * : Path to the destination folder where the output should be written to. Defaults to docs subdirectory of AMP plugin directory.
	 *
	 * @when before_wp_load
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Flags.
	 */
	public function __invoke( $args, $assoc_args ) {
		if ( empty( $args[0] ) ) {
			$args[0] = AMP__DIR__;
		}
		if ( empty( $args[1] ) ) {
			$args[1] = AMP__DIR__ . '/docs';
		}
		list( $source_folder, $destination_folder ) = $args;

		$source_folder      = realpath( $source_folder );
		$destination_folder = realpath( $destination_folder );

		$data = $this->get_phpdoc_data( $source_folder );

		$output_file = $destination_folder . '/docs.json';
		$json        = wp_json_encode( $data, JSON_PRETTY_PRINT );
		$result      = file_put_contents( $output_file, $json ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
		if ( false === $result ) {
			WP_CLI::error( "Problem writing data to file '{$output_file}'" );
			exit;
		}
		WP_CLI::line();
		WP_CLI::success( "Generated JSON data saved to '{$output_file}'." );

		try {
			$doc_tree = new Root( $data );
		} catch ( Exception $exception ) {
			WP_CLI::error(
				"Failed to build documentation object tree: {$exception->getMessage()}\n{$exception->getTraceAsString()}",
				false // Using separate exit for PHPStan.
			);
			exit;
		}

		$template_engine = new MustacheTemplateEngine();

		try {
			foreach (
				$this->generate_markdown( $doc_tree, $template_engine ) as $markdown
			) {
				/** @var Markdown $markdown */
				$filepath = "{$destination_folder}/{$markdown->get_filename()}";
				$this->ensure_dir_exists( dirname( $filepath ) );
				$result = file_put_contents( $filepath, $markdown->get_contents() ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
				if ( false === $result ) {
					WP_CLI::error( "Problem writing data to file '{$filepath}'" );
					exit;
				}
			}
		} catch ( Exception $exception ) {
			WP_CLI::error(
				"Failed to generate markdown files: {$exception->getMessage()}\n{$exception->getTraceAsString()}"
			);
		}

		WP_CLI::success( "Generated Markdown files stored in '{$destination_folder}'." );
	}

	/**
	 * Generate the data from the PHPDoc markup.
	 *
	 * @param string $path Directory or file to scan for PHPDoc.
	 *
	 * @return string|array
	 */
	private function get_phpdoc_data( $path ) {
		WP_CLI::line( sprintf( 'Extracting PHPDoc from %1$s. This may take a few minutes...', $path ) );
		$parser  = new Parser();
		$is_file = is_file( $path );
		$files   = $is_file ? [ $path ] : $parser->get_files( $path, $this->get_included_dirs() );
		$path    = $is_file ? dirname( $path ) : $path;

		if ( $files instanceof WP_Error ) {
			WP_CLI::error( sprintf( 'Problem with %1$s: %2$s', $path, $files->get_error_message() ) );
			exit;
		}

		$parsed_data = array_map( [ $this, 'filter_internal_data' ], $parser->parse_files( $files, $path ) );

		return array_filter(
			$parsed_data,
			static function ( $file ) {
				return ! empty( $file['classes'] )
				|| ! empty( $file['functions'] )
				|| ! empty( $file['hooks'] );
			}
		);
	}

	/**
	 * Filter the parsed data to remove internal and deprecated elements.
	 *
	 * @param array $file Individual file data to filter.
	 * @return array File data without internal and deprecated elements.
	 */
	private function filter_internal_data( $file ) {
		$file['hooks'] = [];

		if ( isset( $file['classes'] ) ) {
			foreach ( $file['classes'] as $index => $class ) {
				if ( isset( $class['methods'] ) ) {
					foreach ( $class['methods'] as $method_index => $method ) {
						if ( isset( $method['hooks'] ) ) {
							$file['hooks'] = array_merge( $file['hooks'], $method['hooks'] );
						}

						if ( ! $this->is_not_internal( $method ) ) {
							unset( $file['classes'][ $index ]['methods'][ $method_index ] );
						}
					}
				}

				if ( ! $this->is_not_internal( $class ) ) {
					unset( $file['classes'][ $index ] );
				}
			}
		}

		if ( isset( $file['functions'] ) ) {
			foreach ( $file['functions'] as $index => $function ) {
				if ( isset( $function['hooks'] ) ) {
					$file['hooks'] = array_merge( $file['hooks'], $function['hooks'] );
				}

				if ( ! $this->is_not_internal( $function ) ) {
					unset( $file['functions'][ $index ] );
				}
			}
		}

		if ( ! empty( $file['hooks'] ) ) {
			$file['hooks'] = array_filter(
				$file['hooks'],
				[ $this, 'is_not_internal' ]
			);
		}

		return $file;
	}

	/**
	 * Ensure a checked element is not internal.
	 *
	 * @param array $parsed Parsed element.
	 * @return bool Whether element is not internal.
	 */
	private function is_not_internal( $parsed ) {
		if (
			isset( $parsed['visibility'] )
			&& 'private' === $parsed['visibility']
		) {
			return false;
		}

		if (
			isset( $parsed['doc']['description'] )
			&& preg_match( '/This (filter|action) is documented in/', $parsed['doc']['description'] )
		) {
			return false;
		}

		if ( empty( $parsed['doc']['tags'] ) ) {
			return true;
		}

		foreach ( $parsed['doc']['tags'] as $tag ) {
			if ( 'internal' === $tag['name'] ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Get the list of regex patterns of folders to include.
	 *
	 * This corresponds to the `productionIncludedRootFiles` array in the project Gruntfile.
	 *
	 * @link https://github.com/ampproject/amp-wp/blob/b3d0f71027fad4498348d04d90357eae615c2665/Gruntfile.js#L6-L16
	 *
	 * @return string[] Array of regex patterns.
	 */
	private function get_included_dirs() {
		return [
			'#^.*/amp/(back-compat|includes|src|templates)/*#',
		];
	}

	/**
	 * Generate all of the markdown files.
	 *
	 * @param Root           $doc_tree        Reference object tree.
	 * @param TemplateEngine $template_engine Templating engine to use.
	 * @return Generator Generator producing Markdown objects.
	 */
	private function generate_markdown( Root $doc_tree, TemplateEngine $template_engine ) {
		$classes  = $doc_tree->get_classes();
		$filename = 'class/README.md';
		$contents = $template_engine->render( 'class_index', $classes );
		yield new Markdown( $filename, $contents );

		foreach ( $classes as $class ) {
			$filename = "class/{$class->get_filename()}.md";
			$contents = $template_engine->render( 'class', $class );
			yield new Markdown( $filename, $contents );
		}

		$methods  = $doc_tree->get_methods();
		$filename = 'method/README.md';
		$contents = $template_engine->render( 'method_index', $methods );
		yield new Markdown( $filename, $contents );

		foreach ( $methods as $method ) {
			$filename = "method/{$method->get_filename()}.md";
			$contents = $template_engine->render( 'method', $method );
			yield new Markdown( $filename, $contents );
		}

		$functions = $doc_tree->get_functions();
		$filename  = 'function/README.md';
		$contents  = $template_engine->render( 'function_index', $functions );
		yield new Markdown( $filename, $contents );

		foreach ( $functions as $function ) {
			$filename = "function/{$function->get_filename()}.md";
			$contents = $template_engine->render( 'function', $function );
			yield new Markdown( $filename, $contents );
		}

		$hooks    = $doc_tree->get_hooks();
		$filename = 'hook/README.md';
		$contents = $template_engine->render( 'hook_index', $hooks );
		yield new Markdown( $filename, $contents );

		foreach ( $hooks as $hook ) {
			$filename = "hook/{$hook->get_filename()}.md";
			$contents = $template_engine->render( 'hook', $hook );
			yield new Markdown( $filename, $contents );
		}

		$filename = 'README.md';
		$contents = $template_engine->render(
			'index',
			compact( 'classes', 'methods', 'functions', 'hooks' )
		);
		yield new Markdown( $filename, $contents );
	}

	/**
	 * Ensure a provided directory does exist on the filesystem.
	 *
	 * @param string $directory Directory to ensure the existence of.
	 */
	private function ensure_dir_exists( $directory ) {
		$parent = dirname( $directory );

		if ( ! empty( $parent ) && ! is_dir( $parent ) ) {
			$this->ensure_dir_exists( $parent );
		}

		if ( ! is_dir( $directory ) && ! mkdir( $directory ) && ! is_dir( $directory ) ) {
			WP_CLI::error( "Couldn't create directory '{$directory}'." );
		}
	}
}
