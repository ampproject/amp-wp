<?php
/**
 * Class AMP_Core_Block_Handler
 *
 * @package AMP
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_Core_Block_Handler
 *
 * @since 1.0
 */
class AMP_Core_Block_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Methods to ampify blocks.
	 *
	 * @var array
	 */
	protected $block_ampify_methods = [
		'core/categories' => 'ampify_categories_block',
		'core/archives'   => 'ampify_archives_block',
		'core/video'      => 'ampify_video_block',
		'core/cover'      => 'ampify_cover_block',
	];

	/**
	 * Register embed.
	 */
	public function register_embed() {
		add_filter( 'render_block', [ $this, 'filter_rendered_block' ], 0, 2 );
	}

	/**
	 * Unregister embed.
	 */
	public function unregister_embed() {
		remove_filter( 'render_block', [ $this, 'filter_rendered_block' ], 0 );
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
			return $block_content;
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
		if ( isset( $meta_data['width'], $meta_data['height'] ) ) {
			$block_content = preg_replace(
				'/(?<=<video\s)/',
				sprintf( 'width="%d" height="%d" ', $meta_data['width'], $meta_data['height'] ),
				$block_content
			);
		}

		return $block_content;
	}

	/**
	 * Ampify cover block.
	 *
	 * This specifically fixes the layout of the block when a background video is assigned.
	 *
	 * @see \AMP_Video_Sanitizer::filter_video_dimensions()
	 *
	 * @param string $block_content The block content about to be appended.
	 * @param array  $block         The full block, including name and attributes.
	 * @return string Filtered block content.
	 */
	public function ampify_cover_block( $block_content, $block ) {
		if ( isset( $block['attrs']['backgroundType'] ) && 'video' === $block['attrs']['backgroundType'] ) {
			$block_content = preg_replace(
				'/(?<=<video\s)/',
				'layout="fill" object-fit="cover" ',
				$block_content
			);
		}
		return $block_content;
	}

	/**
	 * Sanitize widgets that are not added via Gutenberg.
	 *
	 * @param Document $dom Document.
	 */
	public function sanitize_raw_embeds( Document $dom ) {
		$this->process_categories_widgets( $dom );
		$this->process_archives_widgets( $dom );
	}

	/**
	 * Process "Categories" widgets.
	 *
	 * @since 1.5.2
	 *
	 * @param Document $dom Document.
	 */
	private function process_categories_widgets( Document $dom ) {
		static $count = 0;

		$selects = $dom->xpath->query( '//form/select[ @name = "cat" ]' );
		foreach ( $selects as $select ) {
			if ( ! $select instanceof DOMElement ) {
				continue;
			}
			$script = $dom->xpath->query( './/script[ contains( text(), "onCatChange" ) ]', $select->parentNode->parentNode )->item( 0 );
			if ( ! $script instanceof DOMElement ) {
				continue;
			}

			$count++;
			$id = sprintf( 'amp-wp-widget-categories-%d', $count );
			$select->parentNode->setAttribute( 'id', $id );

			AMP_DOM_Utils::add_amp_action( $select, 'change', sprintf( '%s.submit', $id ) );
			$script->parentNode->removeChild( $script );
		}
	}

	/**
	 * Process "Archives" widgets.
	 *
	 * @since 1.5.2
	 *
	 * @param Document $dom Select node retrieved from the widget.
	 */
	private function process_archives_widgets( Document $dom ) {

		$selects = $dom->xpath->query( '//select[ @name = "archive-dropdown" and starts-with( @id, "archives-dropdown-" ) ]' );
		foreach ( $selects as $select ) {
			if ( ! $select instanceof DOMElement ) {
				continue;
			}
			$script = $dom->xpath->query( './/script[ contains( text(), "onSelectChange" ) ]', $select->parentNode )->item( 0 );
			if ( ! $script instanceof DOMElement ) {
				continue;
			}

			AMP_DOM_Utils::add_amp_action( $select, 'change', 'AMP.navigateTo(url=event.value)' );
			$script->parentNode->removeChild( $script );
		}
	}
}
