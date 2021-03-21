<?php
/**
 * Reference site export result.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Tests\Cli\Export;

use stdClass;
use WP_CLI;

final class ExportResult {

	/**
	 * Associative array of site definition data.
	 *
	 * @var array
	 */
	private $data = [];

	/**
	 * Target path of the site definition file.
	 *
	 * @var string
	 */
	private $target_path;

	/**
	 * Site name to use.
	 *
	 * @var string
	 */
	private $site_name;

	/**
	 * Instantiate a ExportResult object.
	 *
	 * @param string $target_path Target path of the site definition file.
	 */
	public function __construct( $target_path ) {
		$this->target_path = $target_path;

		$this->site_name = preg_replace(
			'/\.json$/',
			'',
			basename( $this->get_target_path() )
		);
	}

	/**
	 * Get the target path of the site definition file.
	 *
	 * @return string Target path of the site definition file.
	 */
	public function get_target_path() {
		return $this->target_path;
	}

	/**
	 * Get the site name to use.
	 *
	 * @return string Site name to use.
	 */
	public function get_site_name() {
		return $this->site_name;
	}

	/**
	 * Add a step to the export result.
	 *
	 * @param string $type    Type of the step to add.
	 * @param mixed  $content Content of the step.
	 */
	public function add_step( $type, $content ) {
		$step       = new stdClass();
		$step->type = $type;
		foreach ( $content as $key => $value ) {
			$step->$key = $value;
		}
		$this->data['import-steps'][] = $step;
	}

	/**
	 * Return the JSON representation of the export result.
	 *
	 * @return string JSON representation of the export result.
	 */
	public function to_json() {
		$json = wp_json_encode(
			array_merge( $this->get_defaults(), $this->data ),
			JSON_PRETTY_PRINT
		);

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			WP_CLI::error( 'Failed to convert export result into JSON: ' . json_last_error_msg() );
		}

		return false === $json ? '{}' : $json;
	}

	/**
	 * Get the default values for the export fields.
	 *
	 * @return array Associative array of default values.
	 */
	private function get_defaults() {
		return [
			'name'         => get_option( 'blogname', 'Unnamed WordPress Site' ),
			'version'      => '1.0',
			'description'  => get_option( 'blogdescription', 'Just another WordPress site' ),
			'attributions' => [],
			'import-steps' => [],
		];
	}
}
