<?php
/**
 * Class FileReflector.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Documentation\Cli;

use AmpProject\AmpWP\Documentation\Model\Class_;
use AmpProject\AmpWP\Documentation\Model\Root;
use AmpProject\AmpWP\Documentation\Parser\Parser;
use AmpProject\AmpWP\Documentation\Templating\Markdown;
use AmpProject\AmpWP\Documentation\Templating\MustacheTemplateEngine;
use AmpProject\AmpWP\Documentation\Templating\TemplateEngine;
use Generator;
use WP_CLI;
use WP_CLI\Utils;
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
	 * <source_folder>
	 * : Path to the source folder that contains the source files to be parsed.
	 *
	 * <destination_folder>
	 * : Path to the destination folder where the output should be written to.
	 *
	 * [--format=<format>]
	 * : Output format to generate.
	 * ---
	 * default: markdown
	 * options:
	 *   - json
	 *   - markdown
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 * @when before_wp_load
	 */
	public function __invoke( $args, $assoc_args ) {
		list( $source_folder, $destination_folder ) = $args;
		$format = (string) Utils\get_flag_value( $assoc_args, 'format', 'json' );

		$source_folder      = realpath( $source_folder );
		$destination_folder = realpath( $destination_folder );

		$output_file = $destination_folder . '/docs.json';

		$data = $this->get_phpdoc_data( $source_folder );

		switch ( $format ) {
			case 'json':
				$json   = json_encode( $data, JSON_PRETTY_PRINT );
				$result = file_put_contents( $output_file, $json );
				break;
			case 'markdown':
				$doc_tree        = new Root( $data );
				$template_engine = new MustacheTemplateEngine();

				foreach( $this->generate_markdown( $doc_tree, $template_engine ) as $markdown ) {
					/** @var Markdown $markdown */
					$filepath = "{$destination_folder}/{$markdown->get_filename()}";
					$this->ensure_dir_exists( dirname( $filepath ) );
					file_put_contents( $filepath, $markdown->get_contents() );
				}
				break;
			case '':
				WP_CLI::error( "A value of 'json' or 'markdown' is required for the --format flag." );
			default:
				WP_CLI::error( "Invalid --format value '{$format}' provided. Possible values: json, markdown" );
		}

		WP_CLI::line();

		if ( false === $result ) {
			WP_CLI::error( "Problem writing data to file '{$output_file}'" );
			exit;
		}

		WP_CLI::success( "Data exported to {$output_file}" );
		WP_CLI::line();
	}

	/**
	 * Generate the data from the PHPDoc markup.
	 *
	 * @param string $path Directory or file to scan for PHPDoc
	 *
	 * @return string|array
	 */
	private function get_phpdoc_data( $path ) {
		WP_CLI::line( sprintf( 'Extracting PHPDoc from %1$s. This may take a few minutes...', $path ) );
		$parser  = new Parser();
		$is_file = is_file( $path );
		$files   = $is_file ? [ $path ] : $parser->get_files( $path, $this->get_excluded_dirs() );
		$path    = $is_file ? dirname( $path ) : $path;

		if ( $files instanceof WP_Error ) {
			WP_CLI::error( sprintf( 'Problem with %1$s: %2$s', $path, $files->get_error_message() ) );
			exit;
		}

		return $parser->parse_files( $files, $path );
	}

	/**
	 * Get the list of regex patterns of folders to exclude.
	 *
	 * @return string[] Array of regex patterns.
	 */
	private function get_excluded_dirs() {
		return [
			'#^.*/amp/(assets|bin|build|docs|node_modules|tests|vendor)/#',
			'#^.*/amp/lib/(common|optimizer)/#',
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
		$markdown_files = [];

		foreach ( $doc_tree->get_classes() as $class ) {
			/** @var Class_ $class */
			$filename = "class/{$class->get_filename()}.md";
			$contents = $template_engine->render( 'class', $class );
			yield new Markdown( $filename, $contents );
		}
/*
		foreach ( $doc_tree->get_functions() as $function ) {
			$filename = "function/{$function}.md";
			$contents = $template_engine->render( 'function', $function );
			yield new Markdown( $filename, $contents );
		}*/
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
