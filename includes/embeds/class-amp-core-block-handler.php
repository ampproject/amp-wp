<?php
/**
 * Class AMP_Core_Block_Handler
 *
 * @package AMP
 */

use AmpProject\Dom\Document;
use AmpProject\Html\Attribute;

/**
 * Class AMP_Core_Block_Handler
 *
 * @since 1.0
 * @internal
 */
class AMP_Core_Block_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Attribute to store the original width on a video or iframe just before WordPress removes it.
	 *
	 * @see AMP_Core_Block_Handler::preserve_widget_text_element_dimensions()
	 * @see AMP_Core_Block_Handler::process_text_widgets()
	 * @var string
	 */
	const AMP_PRESERVED_WIDTH_ATTRIBUTE_NAME = 'amp-preserved-width';

	/**
	 * Attribute to store the original height on a video or iframe just before WordPress removes it.
	 *
	 * @see AMP_Core_Block_Handler::preserve_widget_text_element_dimensions()
	 * @see AMP_Core_Block_Handler::process_text_widgets()
	 * @var string
	 */
	const AMP_PRESERVED_HEIGHT_ATTRIBUTE_NAME = 'amp-preserved-height';

	/**
	 * Count of the category widgets encountered.
	 *
	 * @var int
	 */
	private $category_widget_count = 0;

	/**
	 * Methods to ampify blocks.
	 *
	 * @var array
	 */
	protected $block_ampify_methods = [
		'core/categories' => 'ampify_categories_block',
		'core/archives'   => 'ampify_archives_block',
		'core/video'      => 'ampify_video_block',
		'core/file'       => 'ampify_file_block',
	];

	/**
	 * Register embed.
	 */
	public function register_embed() {
		add_filter( 'render_block', [ $this, 'filter_rendered_block' ], 0, 2 );
		add_filter( 'widget_text_content', [ $this, 'preserve_widget_text_element_dimensions' ], PHP_INT_MAX );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'render_block', [ $this, 'filter_rendered_block' ], 0 );
		remove_filter( 'widget_text_content', [ $this, 'preserve_widget_text_element_dimensions' ], PHP_INT_MAX );
	}

	/**
	 * Filters the content of a single block to make it AMP valid.
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array  $block         The full block, including name and attributes.
	 * @return string Filtered block content.
	 */
	public function filter_rendered_block( $block_content, $block ) {
		if ( ! isset( $block['blockName'] ) ) {
			return $block_content; // @codeCoverageIgnore
		}

		if ( isset( $block['attrs'] ) && 'core/shortcode' !== $block['blockName'] ) {
			$injected_attributes    = '';
			$prop_attribute_mapping = [
				'ampCarousel'  => 'data-amp-carousel',
				'ampLayout'    => 'data-amp-layout',
				'ampLightbox'  => 'data-amp-lightbox',
				'ampNoLoading' => 'data-amp-noloading',
			];
			foreach ( $prop_attribute_mapping as $prop => $attr ) {
				if ( isset( $block['attrs'][ $prop ] ) ) {
					$property_value = $block['attrs'][ $prop ];
					if ( is_bool( $property_value ) ) {
						$property_value = $property_value ? 'true' : 'false';
					}

					$injected_attributes .= sprintf( ' %s="%s"', $attr, esc_attr( $property_value ) );
				}
			}
			if ( $injected_attributes ) {
				$block_content = preg_replace( '/(<\w+)/', '$1' . $injected_attributes, $block_content, 1 );
			}
		}

		if ( isset( $this->block_ampify_methods[ $block['blockName'] ] ) ) {
			$method_name   = $this->block_ampify_methods[ $block['blockName'] ];
			$block_content = $this->{$method_name}( $block_content, $block );
		} elseif ( 'core/image' === $block['blockName'] || 'core/audio' === $block['blockName'] ) {
			/*
			 * While the video block placeholder just outputs an empty video element, the placeholders for image and
			 * audio blocks output empty <img> and <audio> respectively. These will result in AMP validation errors,
			 * so we need to empty out the block content to prevent this from happening. Note that <source> is used
			 * for <img> because eventually the image block could use <picture>.
			 */
			if ( ! preg_match( '/src=|<source/', $block_content ) ) {
				$block_content = '';
			}
		}
		return $block_content;
	}

	/**
	 * Fix rendering of categories block when displayAsDropdown.
	 *
	 * This excludes the disallowed JS scrips, adds <form> tags, and uses on:change for <select>.
	 *
	 * @see render_block_core_categories()
	 *
	 * @param string $block_content Block content.
	 * @return string Rendered.
	 */
	public function ampify_categories_block( $block_content ) {
		static $block_id = 0;
		$block_id++;

		$form_id = "wp-block-categories-dropdown-{$block_id}-form";

		// Remove output of build_dropdown_script_block_core_categories().
		$block_content = preg_replace( '#<script.+?</script>#s', '', $block_content );

		$form = sprintf(
			'<form action="%s" method="get" target="_top" id="%s">',
			esc_url( home_url() ),
			esc_attr( $form_id )
		);

		$block_content = preg_replace(
			'#(<select)(.+</select>)#s',
			$form . '$1' . sprintf( ' on="change:%1$s.submit"', esc_attr( $form_id ) ) . '$2</form>',
			$block_content,
			1
		);

		return $block_content;
	}

	/**
	 * Fix rendering of archives block when displayAsDropdown.
	 *
	 * This replaces disallowed script with the use of on:change for <select>.
	 *
	 * @see render_block_core_archives()
	 *
	 * @param string $block_content Block content.
	 * @return string Rendered.
	 */
	public function ampify_archives_block( $block_content ) {

		// Eliminate use of uniqid(). Core should be using wp_unique_id() here.
		static $block_id = 0;
		$block_id++;
		$block_content = preg_replace( '/(?<="wp-block-archives-)\w+(?=")/', $block_id, $block_content );

		// Replace onchange with on attribute.
		$block_content = preg_replace(
			'/onchange=".+?"/',
			'on="change:AMP.navigateTo(url=event.value)"',
			$block_content
		);

		return $block_content;
	}

	/**
	 * Ampify video block.
	 *
	 * Inject the video attachment's dimensions if available. This prevents having to try to look up the attachment
	 * post by the video URL in `\AMP_Video_Sanitizer::filter_video_dimensions()`.
	 *
	 * @see \AMP_Video_Sanitizer::filter_video_dimensions()
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array  $block         The full block, including name and attributes.
	 * @return string Filtered block content.
	 */
	public function ampify_video_block( $block_content, $block ) {
		if ( empty( $block['attrs']['id'] ) || 'attachment' !== get_post_type( $block['attrs']['id'] ) ) {
			return $block_content;
		}

		$meta_data = wp_get_attachment_metadata( $block['attrs']['id'] );
		if ( ! isset( $meta_data['width'], $meta_data['height'] ) ) {
			return $block_content;
		}

		$block_content = preg_replace_callback(
			'/(?<=<video)\s[^>]+/',
			static function ( $matches ) use ( $meta_data ) {
				$attrs = $matches[0];
				if ( ! preg_match( '/\s(width|height|style)=/', $attrs ) ) {
					$attrs .= sprintf(
						' width="%1$d" height="%2$d" style="aspect-ratio:%1$d/%2$d"',
						$meta_data['width'],
						$meta_data['height']
					);
				}
				return $attrs;
			},
			$block_content,
			1
		);

		return $block_content;
	}

	/**
	 * Ampify file block.
	 *
	 * Fix handling of PDF previews by dequeuing wp-block-library-file and ensuring preview element has 100% width.
	 *
	 * @see \AMP_Object_Sanitizer::sanitize_pdf()
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array  $block         The full block, including name and attributes.
	 * @return string Filtered block content.
	 */
	public function ampify_file_block( $block_content, $block ) {
		if (
			empty( $block['attrs']['displayPreview'] )
			||
			empty( $block['attrs']['href'] )
			||
			'.pdf' !== substr( wp_parse_url( $block['attrs']['href'], PHP_URL_PATH ), -4 )
		) {
			return $block_content;
		}

		add_action( 'wp_print_scripts', [ $this, 'dequeue_block_library_file_script' ], 0 );
		add_action( 'wp_print_footer_scripts', [ $this, 'dequeue_block_library_file_script' ], 0 );

		// In Twenty Twenty the PDF embed fails to render due to the parent of the embed having
		// the style rule `display: flex`. Ensuring the element has 100% width fixes that issue.
		$block_content = preg_replace(
			':(?=</div>):',
			'<style id="amp-wp-file-block">.wp-block-file > .wp-block-file__embed { width:100% }</style>',
			$block_content,
			1
		);

		return $block_content;
	}

	/**
	 * Dequeue wp-block-library-file script.
	 */
	public function dequeue_block_library_file_script() {
		wp_dequeue_script( 'wp-block-library-file' );
	}

	/**
	 * Sanitize widgets that are not added via Gutenberg.
	 *
	 * @param Document $dom  Document.
	 * @param array    $args Args passed to sanitizer.
	 */
	public function sanitize_raw_embeds( Document $dom, $args = [] ) {
		$this->process_categories_widgets( $dom );
		$this->process_archives_widgets( $dom, $args );
		$this->process_text_widgets( $dom );
	}

	/**
	 * Process "Categories" widgets.
	 *
	 * @since 2.0
	 *
	 * @param Document $dom Document.
	 */
	private function process_categories_widgets( Document $dom ) {
		$selects = $dom->xpath->query( '//form/select[ @name = "cat" ]' );
		foreach ( $selects as $select ) {
			if ( ! $select instanceof DOMElement ) {
				continue; // @codeCoverageIgnore
			}
			$form = $select->parentNode;
			if ( ! $form instanceof DOMElement || ! $form->parentNode instanceof DOMElement ) {
				continue; // @codeCoverageIgnore
			}
			$script = $dom->xpath->query( './/script[ contains( text(), "onCatChange" ) ]', $form->parentNode )->item( 0 );
			if ( ! $script instanceof DOMElement ) {
				continue; // @codeCoverageIgnore
			}

			$this->category_widget_count++;
			$id = sprintf( 'amp-wp-widget-categories-%d', $this->category_widget_count );

			$form->setAttribute( 'id', $id );

			AMP_DOM_Utils::add_amp_action( $select, 'change', sprintf( '%s.submit', $id ) );
			$script->parentNode->removeChild( $script );
		}
	}

	/**
	 * Process "Archives" widgets.
	 *
	 * @since 2.0
	 *
	 * @param Document $dom  Select node retrieved from the widget.
	 * @param array    $args Args passed to sanitizer.
	 */
	private function process_archives_widgets( Document $dom, $args = [] ) {
		$selects = $dom->xpath->query( '//select[ @name = "archive-dropdown" and starts-with( @id, "archives-dropdown-" ) ]' );
		foreach ( $selects as $select ) {
			if ( ! $select instanceof DOMElement ) {
				continue; // @codeCoverageIgnore
			}

			$script = $dom->xpath->query( './/script[ contains( text(), "onSelectChange" ) ]', $select->parentNode )->item( 0 );
			if ( $script ) {
				$script->parentNode->removeChild( $script );
			} elseif ( $select->hasAttribute( 'onchange' ) ) {
				// Special condition for WordPress<=5.1.
				$select->removeAttribute( 'onchange' );
			} else {
				continue;
			}

			AMP_DOM_Utils::add_amp_action( $select, 'change', 'AMP.navigateTo(url=event.value)' );

			// When AMP-to-AMP linking is enabled, ensure links go to the AMP version.
			if ( ! empty( $args['amp_to_amp_linking_enabled'] ) ) {
				foreach ( $dom->xpath->query( '//option[ @value != "" ]', $select ) as $option ) {
					/**
					 * Option element.
					 *
					 * @var DOMElement $option
					 */
					$option->setAttribute( 'value', amp_add_paired_endpoint( $option->getAttribute( 'value' ) ) );
				}
			}
		}
	}

	/**
	 * Preserve dimensions of elements in a Text widget to later restore to circumvent WordPress core stripping them out.
	 *
	 * Core strips out the dimensions to prevent the element being made too wide for the sidebar. This is not a concern
	 * in AMP because of responsive sizing. So this logic is here to undo what core is doing.
	 *
	 * @since 2.0
	 * @see WP_Widget_Text::inject_video_max_width_style()
	 * @see AMP_Core_Block_Handler::process_text_widgets()
	 *
	 * @param string $content Content.
	 * @return string Content.
	 */
	public function preserve_widget_text_element_dimensions( $content ) {
		$content = preg_replace_callback(
			'#<(video|iframe|object|embed)\s[^>]*>#si',
			static function ( $matches ) {
				$html = $matches[0];
				$html = preg_replace( '/(?=\sheight="(\d+)")/', ' ' . self::AMP_PRESERVED_HEIGHT_ATTRIBUTE_NAME . '="$1" ', $html );
				$html = preg_replace( '/(?=\swidth="(\d+)")/', ' ' . self::AMP_PRESERVED_WIDTH_ATTRIBUTE_NAME . '="$1" ', $html );
				return $html;
			},
			$content
		);

		return $content;
	}

	/**
	 * Process "Text" widgets.
	 *
	 * @since 2.0
	 * @see AMP_Core_Block_Handler::preserve_widget_text_element_dimensions()
	 *
	 * @param Document $dom Select node retrieved from the widget.
	 */
	private function process_text_widgets( Document $dom ) {
		foreach ( $dom->xpath->query( '//div[ @class = "textwidget" ]' ) as $text_widget ) {
			// Restore the width/height attributes which were preserved in preserve_widget_text_element_dimensions.
			foreach ( $dom->xpath->query( sprintf( './/*[ @%s or @%s ]', self::AMP_PRESERVED_WIDTH_ATTRIBUTE_NAME, self::AMP_PRESERVED_HEIGHT_ATTRIBUTE_NAME ), $text_widget ) as $element ) {
				if ( $element->hasAttribute( self::AMP_PRESERVED_WIDTH_ATTRIBUTE_NAME ) ) {
					$element->setAttribute( Attribute::WIDTH, $element->getAttribute( self::AMP_PRESERVED_WIDTH_ATTRIBUTE_NAME ) );
					$element->removeAttribute( self::AMP_PRESERVED_WIDTH_ATTRIBUTE_NAME );
				}
				if ( $element->hasAttribute( self::AMP_PRESERVED_HEIGHT_ATTRIBUTE_NAME ) ) {
					$element->setAttribute( Attribute::HEIGHT, $element->getAttribute( self::AMP_PRESERVED_HEIGHT_ATTRIBUTE_NAME ) );
					$element->removeAttribute( self::AMP_PRESERVED_HEIGHT_ATTRIBUTE_NAME );
				}
			}

			/*
			 * Remove inline width style which is added to video shortcode but which overruns the container.
			 * Normally this width gets overridden by wp-mediaelement.css to be max-width: 100%, but since
			 * MediaElement.js is not used in AMP this stylesheet is not included. In any case, videos in AMP are
			 * responsive so this is built-in. Note also the style rule for .wp-video in amp-default.css.
			 */
			foreach ( $dom->xpath->query( './/div[ @class = "wp-video" and @style ]', $text_widget ) as $element ) {
				$element->removeAttribute( 'style' );
			}
		}
	}
}
