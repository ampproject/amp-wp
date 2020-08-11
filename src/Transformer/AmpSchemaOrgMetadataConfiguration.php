<?php
/**
 * Class AmpSchemaOrgMetadataConfiguration.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP\Transformer;

use AmpProject\Optimizer\Configuration\BaseTransformerConfiguration;
use AmpProject\Optimizer\Exception\InvalidConfigurationValue;
use AmpProject\Optimizer\Transformer\TransformedIdentifier;

/**
 * Configuration for the AmpSchemaOrgMetadata transformer.
 *
 * @property array $metadata Associative array of metadata.
 *
 * @package ampproject/optimizer
 * @since 2.0
 * @internal
 */
final class AmpSchemaOrgMetadataConfiguration extends BaseTransformerConfiguration {

	/**
	 * Configuration key that holds the version number to use.
	 *
	 * @var string
	 */
	const METADATA = 'metadata';

	/**
	 * Get the associative array of allowed keys and their respective default values.
	 *
	 * The array index is the key and the array value is the key's default value.
	 *
	 * @return array Associative array of allowed keys and their respective default values.
	 */
	protected function getAllowedKeys() {
		return [
			self::METADATA => (array) amp_get_schemaorg_metadata(),
		];
	}

	/**
	 * Validate an individual configuration entry.
	 *
	 * @param string $key   Key of the configuration entry to validate.
	 * @param mixed  $value Value of the configuration entry to validate.
	 * @return mixed Validated value.
	 * @throws InvalidConfigurationValue If a provided configuration value was not valid.
	 */
	protected function validate( $key, $value ) {
		switch ( $key ) {
			case self::METADATA:
				if ( ! is_array( $value ) ) {
					throw InvalidConfigurationValue::forInvalidSubValueType( self::class, self::METADATA, 'array', gettype( $value ) );
				}
				break;
		}

		return $value;
	}
}
