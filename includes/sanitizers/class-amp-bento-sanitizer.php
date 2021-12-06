<?php
/**
 * Class AMP_Bento_Sanitizer.
 *
 * @package AMP
 */

use AmpProject\AmpWP\ValidationExemption;
use AmpProject\CssLength;
use AmpProject\Dom\Element;
use AmpProject\Html\Attribute;
use AmpProject\Html\LengthUnit;
use AmpProject\Layout;

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
	 * Indicate that the selector conversion mappings do not involve light shadow DOM.
	 *
	 * For example, with `bento-base-carousel`, the descendant `h2` elements will be present in the document initially.
	 * So a selector like `bento-base-carousel h2` will not have issues with tree shaking when it is converted into
	 * `amp-base-carousel h2`. Additionally, Bento components by definition use the _actual_ real shadow DOM, so if
	 * there were a selector like `bento-foo div` then the `div` would never match an element since is beyond the
	 * shadow boundary, and tree shaking should be free to remove such a selector. Selectors that are targeting
	 * slotted elements are not inside the shadow DOM, so for example `bento-base-carousel img` will target an actual
	 * element in the initial DOM, even though `bento-base-carousel` has other elements that are beyond the shadow
	 * DOM boundary.
	 *
	 * @return false
	 */
	public function has_light_shadow_dom() {
		return false;
	}

	/**
	 * Sanitize.
	 */
	public function sanitize() {
		$bento_elements = $this->dom->xpath->query( self::XPATH_BENTO_ELEMENTS_QUERY, $this->dom->body );

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
			while ( $bento_element->attributes->length ) {
				/** @var DOMAttr $attribute */
				$attribute = $bento_element->attributes->item( 0 );

				// Essential for unique attributes like ID, or else PHP DOM will keep it referencing the old element.
				$bento_element->removeAttributeNode( $attribute );

				$amp_element->setAttributeNode( $attribute );
			}

			while ( $bento_element->firstChild instanceof DOMNode ) {
				$amp_element->appendChild( $bento_element->removeChild( $bento_element->firstChild ) );
			}

			$this->adapt_layout_styles( $amp_element );

			$bento_element->parentNode->replaceChild( $amp_element, $bento_element );

			$bento_elements_converted[ $bento_name ] = true;
		}

		// Remove the Bento external stylesheets which are no longer necessary. For the others, mark as PX-verified.
		$links = $this->dom->xpath->query(
			'//link[ @rel = "stylesheet" and starts-with( @href, "https://cdn.ampproject.org/v0/bento-" ) ]'
		);
		foreach ( $links as $link ) {
			/** @var Element $link */
			$bento_name = $this->get_bento_component_name_from_url( $link->getAttribute( Attribute::HREF ) );
			if ( ! $bento_name ) {
				continue;
			}

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
				ValidationExemption::mark_node_as_px_verified( $link->getAttributeNode( Attribute::HREF ) );
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
			$bento_name = $this->get_bento_component_name_from_url( $script->getAttribute( Attribute::SRC ) );
			if ( ! $bento_name ) {
				continue;
			}

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

		// If bento-prefixed components were discovered, then ensure that the tag-and-attribute sanitizer will prefer
		// Bento components when validating and that it will use the Bento versions of component scripts, and ultimately
		// AMP_Theme_Support::ensure_required_markup() will add the Bento experiment opt-in which is still required at
		// the moment.
		if ( count( $bento_elements_discovered ) > 0 && $this->tag_and_attribute_sanitizer ) {
			$this->tag_and_attribute_sanitizer->update_args(
				[ 'prefer_bento' => true ]
			);
		}
	}

	/**
	 * Parse Bento component name from a Bento CDN URL.
	 *
	 * @param string $url URL for script or stylesheet.
	 * @return string|null Bento component name or null if no match was made.
	 */
	private function get_bento_component_name_from_url( $url ) {
		$path = wp_parse_url( $url, PHP_URL_PATH );
		if ( $path && preg_match( '#^(bento-.*?)-\d+\.\d+\.(m?js|css)#', basename( $path ), $matches ) ) {
			return $matches[1];
		} else {
			return null;
		}
	}

	/**
	 * Adapt inline styles from Bento element to AMP layout attributes.
	 *
	 * This will try its best to convert `width`, `height`, and `aspect-ratio` inline styles over to their corresponding
	 * AMP layout attributes. In order for a Bento component to be AMP-compatible, it needs to utilize inline styles
	 * for its dimensions rather than rely on a stylesheet rule.
	 *
	 * @param Element $amp_element AMP element (converted from Bento).
	 */
	private function adapt_layout_styles( Element $amp_element ) {
		$supported_layouts = [];
		foreach ( AMP_Allowed_Tags_Generated::get_allowed_tag( $amp_element->tagName ) as $rule_spec ) {
			if ( isset( $rule_spec['tag_spec']['amp_layout']['supported_layouts'] ) ) {
				$supported_layouts = array_merge( $supported_layouts, $rule_spec['tag_spec']['amp_layout']['supported_layouts'] );
			}
		}
		$supported_layouts = array_unique( $supported_layouts );

		// If no layouts are supported, then there is nothing to do.
		if ( count( $supported_layouts ) === 0 ) {
			return;
		}

		// If nodisplay is the only supported layout or nodisplay is supported and
		// the element is hidden, then give it the nodisplay layout.
		if (
			[ Layout::TO_SPEC[ Layout::NODISPLAY ] ] === $supported_layouts
			||
			(
				$amp_element->hasAttribute( Attribute::HIDDEN )
				&&
				in_array( Layout::TO_SPEC[ Layout::NODISPLAY ], $supported_layouts, true )
			)
		) {
			$amp_element->setAttribute( Attribute::LAYOUT, Layout::NODISPLAY );
			$amp_element->removeAttribute( Attribute::HIDDEN );
			return;
		}

		// Since Bento elements don't support width/height attributes (currently),
		// obtain the inline styles or else abort if none are provided.
		$style_string = $amp_element->getAttribute( Attribute::STYLE );
		if ( ! $style_string ) {
			return;
		}

		// Re-use set_layout method to detect fill layout (only).
		if ( in_array( Layout::TO_SPEC[ Layout::FILL ], $supported_layouts, true ) ) {
			$attributes = $this->set_layout( [ Attribute::STYLE => $style_string ] );
			if ( isset( $attributes[ Attribute::LAYOUT ] ) && Layout::FILL === $attributes[ Attribute::LAYOUT ] ) {
				$amp_element->setAttributes( $attributes );
				if ( ! array_key_exists( Attribute::STYLE, $attributes ) ) {
					$amp_element->removeAttribute( Attribute::STYLE );
				}
				return;
			}
		}

		// If there are no layouts that support width and height, then abort.
		if (
			0 === count(
				array_intersect(
					$supported_layouts,
					[
						Layout::TO_SPEC[ Layout::FIXED ],
						Layout::TO_SPEC[ Layout::FIXED_HEIGHT ],
						Layout::TO_SPEC[ Layout::INTRINSIC ],
						Layout::TO_SPEC[ Layout::RESPONSIVE ],
					]
				)
			)
		) {
			return;
		}

		$styles = $this->parse_style_string( $style_string );

		$layout_attributes = [];

		// Obtain the height.
		if ( isset( $styles[ Attribute::HEIGHT ] ) ) {
			$height = new CssLength( $styles[ Attribute::HEIGHT ] );
			$height->validate( false, false );
			if ( $height->isValid() ) {
				$layout_attributes[ Attribute::HEIGHT ] = $height->getNumeral() . ( $height->getUnit() !== LengthUnit::PX ? $height->getUnit() : '' );
				unset( $styles[ Attribute::HEIGHT ] );
			}
		}

		// Obtain the width.
		if (
			in_array( Layout::TO_SPEC[ Layout::FIXED_HEIGHT ], $supported_layouts, true )
			&&
			( ! isset( $styles[ Attribute::WIDTH ] ) || '100%' === $styles[ Attribute::WIDTH ] )
		) {
			$layout_attributes[ Attribute::WIDTH ]  = CssLength::AUTO;
			$layout_attributes[ Attribute::LAYOUT ] = Layout::FIXED_HEIGHT;
			unset( $styles[ Attribute::WIDTH ] );
		} elseif ( isset( $styles[ Attribute::WIDTH ] ) ) {
			$width = new CssLength( $styles[ Attribute::WIDTH ] );
			$width->validate( false, false );
			if ( $width->isValid() ) {
				$layout_attributes[ Attribute::WIDTH ] = $width->getNumeral() . ( $width->getUnit() !== LengthUnit::PX ? $width->getUnit() : '' );
				unset( $styles[ Attribute::WIDTH ] );
			}
		}

		$supports_responsive = in_array( Layout::TO_SPEC[ Layout::RESPONSIVE ], $supported_layouts, true );
		$supports_intrinsic  = in_array( Layout::TO_SPEC[ Layout::INTRINSIC ], $supported_layouts, true );
		if (
			isset( $styles[ Attribute::ASPECT_RATIO ] )
			&&
			( $supports_responsive || $supports_intrinsic )
			&&
			preg_match( '#(?P<width>\d+(?:.\d+)?)(?:\s*/\s*(?P<height>\d+(?:.\d+)?))?#', $styles[ Attribute::ASPECT_RATIO ], $matches )
		) {
			$height = isset( $matches[ Attribute::HEIGHT ] ) ? (float) $matches[ Attribute::HEIGHT ] : 1.0;
			$width  = (float) $matches[ Attribute::WIDTH ];

			// Derive intrinsic width and height when max-width is supplied.
			$intrinsic_width = null;
			if ( isset( $styles[ Attribute::MAX_WIDTH ] ) ) {
				$intrinsic_width = new CssLength( $styles[ Attribute::MAX_WIDTH ] );
				$intrinsic_width->validate( false, false );
				if ( $intrinsic_width->isValid() && $intrinsic_width->getUnit() === LengthUnit::PX ) {
					unset( $styles[ Attribute::MAX_WIDTH ] );
				} else {
					$intrinsic_width = null;
				}
			}

			if ( $intrinsic_width ) {
				$height = ( $height / $width ) * $intrinsic_width->getNumeral();
				$width  = $intrinsic_width->getNumeral();
			} else {
				$supports_intrinsic = false;
			}

			$layout_attributes[ Attribute::LAYOUT ] = $supports_intrinsic ? Layout::INTRINSIC : Layout::RESPONSIVE;
			$layout_attributes[ Attribute::HEIGHT ] = (string) $height;
			$layout_attributes[ Attribute::WIDTH ]  = (string) $width;

			unset( $styles[ Attribute::ASPECT_RATIO ] );
		}

		if ( $layout_attributes ) {
			$amp_element->setAttribute( Attribute::STYLE, $this->reassemble_style_string( $styles ) );
			$amp_element->setAttributes( $layout_attributes );
		}
	}
}
