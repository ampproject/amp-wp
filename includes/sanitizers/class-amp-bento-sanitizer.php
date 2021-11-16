<?php
/**
 * Class AMP_Bento_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\Attribute;
use AmpProject\Dom\Element;
use AmpProject\AmpWP\ValidationExemption;

/**
 * Convert all bento-prefixed components into amp-prefixed components, or else mark them as PX-verified if they have no
 * AMP versions. Remove Bento stylesheets and scripts if they aren't needed.
 *
 * @since 2.2
 * @internal
 */
class AMP_Bento_Sanitizer extends AMP_Base_Sanitizer {

	/** @var string */
	const XPATH_BENTO_ELEMENTS_QUERY = './/*[ starts-with( name(), "bento-" ) ]';

	/**
	 * Tag and attribute sanitizer.
	 *
	 * @var AMP_Base_Sanitizer
	 */
	protected $tag_and_attribute_sanitizer;

	/**
	 * Init.
	 *
	 * @param AMP_Base_Sanitizer[] $sanitizers Sanitizers.
	 */
	public function init( $sanitizers ) {
		parent::init( $sanitizers );

		if ( array_key_exists( AMP_Tag_And_Attribute_Sanitizer::class, $sanitizers ) ) {
			$this->tag_and_attribute_sanitizer = $sanitizers[ AMP_Tag_And_Attribute_Sanitizer::class ];
		}
	}

	/**
	 * Get mapping of HTML selectors to the AMP component selectors which they may be converted into.
	 *
	 * @return array Mapping.
	 */
	public function get_selector_conversion_mapping() {
		$mapping = [];
		foreach ( AMP_Allowed_Tags_Generated::get_extension_specs() as $amp_extension_name => $extension_spec ) {
			if ( empty( $extension_spec['bento'] ) ) {
				continue;
			}
			$bento_extension_name = str_replace( 'amp-', 'bento-', $amp_extension_name );
			if ( $bento_extension_name !== $amp_extension_name ) {
				$mapping[ $bento_extension_name ] = [ $amp_extension_name ];
			}
		}

		return $mapping;
	}

	/**
	 * Sanitize.
	 */
	public function sanitize() {
		$bento_elements = $this->dom->xpath->query( self::XPATH_BENTO_ELEMENTS_QUERY, $this->dom->body );
		if ( 0 === $bento_elements->length ) {
			return;
		}

		$bento_elements_discovered = [];
		$bento_elements_converted  = [];

		$extension_specs = AMP_Allowed_Tags_Generated::get_extension_specs();
		foreach ( $bento_elements as $bento_element ) {
			/** @var Element $bento_element */
			$bento_name = $bento_element->tagName;
			$amp_name   = str_replace( 'bento-', 'amp-', $bento_name );

			$bento_elements_discovered[ $bento_name ] = true;

			// Skip Bento components which aren't valid (yet).
			if ( ! array_key_exists( $amp_name, $extension_specs ) ) {
				ValidationExemption::mark_node_as_px_verified( $bento_element );
				continue;
			}

			$amp_element = $this->dom->createElement( $amp_name );
			foreach ( $bento_element->attributes as $attribute ) {
				/** @var DOMAttr $attribute */
				$amp_element->setAttribute( $attribute->nodeName, $attribute->nodeValue );
			}

			while ( $bento_element->firstChild instanceof DOMNode ) {
				$amp_element->appendChild( $bento_element->removeChild( $bento_element->firstChild ) );
			}

			$bento_element->parentNode->replaceChild( $amp_element, $bento_element );

			$bento_elements_converted[ $bento_name ] = true;
		}

		// Remove the Bento external stylesheets which are no longer necessary. For the others, mark as PX-verified.
		$links = $this->dom->xpath->query(
			'//link[ @rel = "stylesheet" and starts-with( @href, "https://cdn.ampproject.org/v0/bento-" ) ]'
		);
		foreach ( $links as $link ) {
			/** @var Element $link */
			$basename   = basename( wp_parse_url( $link->getAttribute( Attribute::HREF ), PHP_URL_PATH ) );
			$bento_name = preg_replace( '/-\d+\.\d+\.css$/', '', $basename );
			if (
				// If the Bento element doesn't exist in the page, remove the extraneous stylesheet.
				! array_key_exists( $bento_name, $bento_elements_discovered )
				||
				// If the Bento element was converted to AMP, then remove the now-unnecessary stylesheet.
				array_key_exists( $bento_name, $bento_elements_converted )
			) {
				$link->parentNode->removeChild( $link );
			} else {
				ValidationExemption::mark_node_as_px_verified( $link );
			}
		}

		// Keep track of the number of Bento scripts we kept, as then we'll need to make sure we keep the Bento runtime script.
		$non_amp_scripts_retained = 0;

		// Handle Bento scripts.
		$scripts = $this->dom->xpath->query(
			'//script[ starts-with( @src, "https://cdn.ampproject.org/v0/bento" ) ]'
		);
		foreach ( $scripts as $script ) {
			/** @var Element $script */
			$basename = basename( wp_parse_url( $script->getAttribute( Attribute::SRC ), PHP_URL_PATH ) );
			if ( ! preg_match( '#^(bento-.*?)-\d+\.\d+\.m?js#', $basename, $matches ) ) {
				continue;
			}
			$bento_name = $matches[1];

			if (
				// If the Bento element doesn't exist in the page, remove the extraneous script.
				! array_key_exists( $bento_name, $bento_elements_discovered )
				||
				// If the Bento element was converted to AMP, then remove the now-unnecessary script.
				array_key_exists( $bento_name, $bento_elements_converted )
			) {
				$script->parentNode->removeChild( $script );
			} else {
				ValidationExemption::mark_node_as_px_verified( $script );
				$non_amp_scripts_retained++;
			}
		}

		// Remove the Bento runtime script if it is not needed, or else mark it as PX-verified.
		$bento_runtime_scripts = $this->dom->xpath->query(
			'//script[ @src = "https://cdn.ampproject.org/bento.mjs" or @src = "https://cdn.ampproject.org/bento.js" ]'
		);
		if ( 0 === $non_amp_scripts_retained ) {
			foreach ( $bento_runtime_scripts as $bento_runtime_script ) {
				$bento_runtime_script->parentNode->removeChild( $bento_runtime_script );
			}
		} else {
			foreach ( $bento_runtime_scripts as $bento_runtime_script ) {
				ValidationExemption::mark_node_as_px_verified( $bento_runtime_script );
			}
		}

		// If bento-prefixed components were converted to amp-prefixed ones, then ensure that the tag-and-attribute
		// sanitizer will prefer Bento components when validating and that it will use the Bento versions of component
		// scripts, and ultimately AMP_Theme_Support::ensure_required_markup() will add the Bento experiment opt-in
		// which is still required at the moment.
		if ( count( $bento_elements_converted ) > 0 && $this->tag_and_attribute_sanitizer ) {
			$this->tag_and_attribute_sanitizer->update_args(
				[ 'prefer_bento' => true ]
			);
		}
	}
}
