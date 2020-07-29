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
	 * Add a step to the export result.
	 *
	 * @param string $type    Type of the step to add.
	 * @param mixed  $content Content of the step.
	 */
	public function add_step( $type, $content ) {
		$step = new stdClass();
		$step->type = $type;
		foreach ( $content as $key => $value ) {
			$step->key = $value;
		}
		$this->data['steps'][] = $step;
	}

	/**
	 * Return the JSON representation of the export result.
	 *
	 * @return string JSON representation of the export result.
	 */
	public function to_json() {
		$json = json_encode( $this->data );

		if ( JSON_ERROR_NONE !== json_last_error() ) {
			WP_CLI::error( 'Failed to convert export result into JSON: ' . json_last_error_msg() );
		}

		return false === $json ? '{}' : $json;
	}
}
