<?php
/**
 * Class AmpSchemaOrgMetadata.
 *
 * @package Amp\AmpWP
 */

namespace Amp\AmpWP\Transformer;

use Amp\Attribute;
use Amp\Dom\Document;
use Amp\Optimizer\Configurable;
use Amp\Optimizer\ErrorCollection;
use Amp\Optimizer\Transformer;
use Amp\Optimizer\TransformerConfiguration;
use Amp\Tag;

/**
 * Ensure there is a schema.org script in the document.
 *
 * @package Amp\AmpWP
 */
final class AmpSchemaOrgMetadata implements Transformer, Configurable {

	/**
	 * XPath query to use for fetching the schema.org meta script.
	 *
	 * @var string
	 */
	const SCHEMA_ORG_XPATH = '//script[ @type = "application/ld+json" ][ contains( ./text(), "schema.org" ) ]';

	/**
	 * Configuration object.
	 *
	 * @var TransformerConfiguration
	 */
	private $configuration;

	/**
	 * Instantiate a TransformedIdentifier object.
	 *
	 * @param TransformerConfiguration $configuration Configuration store to use.
	 */
	public function __construct( TransformerConfiguration $configuration ) {
		$this->configuration = $configuration;
	}

	/**
	 * Apply transformations to the provided DOM document.
	 *
	 * @param Document        $document DOM document to apply the transformations to.
	 * @param ErrorCollection $errors   Collection of errors that are collected during transformation.
	 * @return void
	 */
	public function transform( Document $document, ErrorCollection $errors ) {
		// @todo How should we handle an existing schema.org script?
		$schema_org_meta_script = $document->xpath->query( self::SCHEMA_ORG_XPATH )->item( 0 );

		if ( $schema_org_meta_script ) {
			return;
		}

		$script = $document->createElement( Tag::SCRIPT );
		$script->setAttribute( Attribute::TYPE, Attribute::TYPE_LD_JSON );

		$metadata = $this->configuration->get( AmpSchemaOrgMetadataConfiguration::METADATA );
		$json     = wp_json_encode( $metadata, JSON_UNESCAPED_UNICODE );
		$script->appendChild( $document->createTextNode( $json ) );

		$document->head->appendChild( $script );
	}
}
