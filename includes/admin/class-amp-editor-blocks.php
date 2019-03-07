<?php
/**
 * AMP Editor Blocks extending.
 *
 * @package AMP
 * @since 1.0
 */

/**
 * Class AMP_Editor_Blocks
 */
class AMP_Editor_Blocks {

	/**
	 * The image size for the Latest Stories block, which is also used for the amp_story post.
	 *
	 * @var string
	 */
	const LATEST_STORIES_IMAGE_SIZE = 'amp-story-poster-portrait';

	/**
	 * List of AMP scripts that need to be printed when AMP components are used in non-AMP document context ("dirty AMP").
	 *
	 * @var array
	 */
	public $content_required_amp_scripts = array();

	/**
	 * AMP components that have blocks.
	 *
	 * @var array
	 */
	public $amp_blocks = array(
		'amp-mathml',
		'amp-timeago',
		'amp-o2-player',
		'amp-ooyala-player',
		'amp-reach-player',
		'amp-springboard-player',
		'amp-jwplayer',
		'amp-brid-player',
		'amp-ima-video',
		'amp-fit-text',
	);

	/**
	 * Init.
	 */
	public function init() {
		if ( function_exists( 'register_block_type' ) ) {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_assets' ) );
			add_action( 'wp_loaded', array( $this, 'register_block_latest_stories' ), 11 );
			add_filter( 'wp_kses_allowed_html', array( $this, 'whitelist_block_atts_in_wp_kses_allowed_html' ), 10, 2 );
			add_action( 'enqueue_block_assets', array( $this, 'enqueue_block_assets' ) );

			/*
			 * Dirty AMP is required when a site is in native mode but not all templates are being served
			 * as AMP. In particular, if a single post is using AMP-specific Gutenberg Blocks which make
			 * use of AMP components, and the singular template is served as AMP but the blog page is not,
			 * then the non-AMP blog page need to load the AMP runtime scripts so that the AMP components
			 * in the posts displayed there will be rendered properly. This is only relevant on native AMP
			 * sites because the AMP Gutenberg blocks are only made available in that mode; they are not
			 * presented in the Gutenberg inserter in paired mode. In general, using AMP components in
			 * non-AMP documents is still not officially supported, so it's occurrence is being minimized
			 * as much as possible. For more, see <https://github.com/ampproject/amp-wp/issues/1192>.
			 */
			if ( amp_is_canonical() ) {
				add_filter( 'the_content', array( $this, 'tally_content_requiring_amp_scripts' ) );
				add_action( 'wp_print_footer_scripts', array( $this, 'print_dirty_amp_scripts' ) );
			}
		}
	}

	/**
	 * Whitelist elements and attributes used for AMP.
	 *
	 * This prevents AMP markup from being deleted in
	 *
	 * @param array  $tags    Array of allowed post tags.
	 * @param string $context Context.
	 * @return mixed Modified array.
	 */
	public function whitelist_block_atts_in_wp_kses_allowed_html( $tags, $context ) {
		if ( 'post' !== $context ) {
			return $tags;
		}

		foreach ( $tags as &$tag ) {
			$tag['data-amp-layout']              = true;
			$tag['data-amp-noloading']           = true;
			$tag['data-amp-lightbox']            = true;
			$tag['data-close-button-aria-label'] = true;
		}

		foreach ( $this->amp_blocks as $amp_block ) {
			if ( ! isset( $tags[ $amp_block ] ) ) {
				$tags[ $amp_block ] = array();
			}

			// @todo The global attributes included here should be matched up with what is actually used by each block.
			$tags[ $amp_block ] = array_merge(
				array_fill_keys(
					array(
						'layout',
						'width',
						'height',
						'class',
					),
					true
				),
				$tags[ $amp_block ]
			);

			$amp_tag_specs = AMP_Allowed_Tags_Generated::get_allowed_tag( $amp_block );
			foreach ( $amp_tag_specs as $amp_tag_spec ) {
				if ( ! isset( $amp_tag_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] ) ) {
					continue;
				}
				$tags[ $amp_block ] = array_merge(
					$tags[ $amp_block ],
					array_fill_keys( array_keys( $amp_tag_spec[ AMP_Rule_Spec::ATTR_SPEC_LIST ] ), true )
				);
			}
		}

		return $tags;
	}

	/**
	 * Enqueue filters for extending core blocks attributes.
	 * Has to be loaded before registering the blocks in registerCoreBlocks.
	 */
	public function enqueue_block_editor_assets() {

		$amp_agnostic_slug = 'amp-agnostic-blocks-compiled';
		wp_enqueue_script(
			$amp_agnostic_slug,
			amp_get_asset_url( "js/{$amp_agnostic_slug}.js" ),
			array( 'wp-editor', 'wp-blocks', 'lodash', 'wp-i18n', 'wp-element', 'wp-components' ),
			AMP__VERSION,
			false
		);

		// Enqueue script and style for AMP-specific blocks.
		if ( amp_is_canonical() && AMP_Story_Post_Type::POST_TYPE_SLUG !== get_current_screen()->post_type ) {
			wp_enqueue_style(
				'amp-editor-blocks-style',
				amp_get_asset_url( 'css/amp-editor-blocks.css' ),
				array(),
				AMP__VERSION
			);

			// phpcs:ignore WordPress.WP.EnqueuedResourceParameters.NotInFooter
			wp_enqueue_script(
				'amp-editor-blocks-build',
				amp_get_asset_url( 'js/amp-blocks-compiled.js' ),
				array( 'wp-editor', 'wp-blocks', 'lodash', 'wp-i18n', 'wp-element', 'wp-components' ),
				AMP__VERSION
			);

			if ( function_exists( 'wp_set_script_translations' ) ) {
				wp_set_script_translations( 'amp-editor-blocks-build', 'amp' );
			}
		}

		wp_enqueue_script(
			'amp-editor-blocks',
			amp_get_asset_url( 'js/amp-editor-blocks.js' ),
			array( 'wp-editor', 'underscore', 'wp-hooks', 'wp-i18n', 'wp-components' ),
			AMP__VERSION,
			true
		);

		wp_add_inline_script(
			'amp-editor-blocks',
			sprintf(
				'ampEditorBlocks.boot( %s );',
				wp_json_encode(
					array(
						'hasThemeSupport' => current_theme_supports( AMP_Theme_Support::SLUG ),
					)
				)
			)
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'amp-editor-blocks', 'amp' );
		} elseif ( function_exists( 'wp_get_jed_locale_data' ) || function_exists( 'gutenberg_get_jed_locale_data' ) ) {
			$locale_data = function_exists( 'wp_get_jed_locale_data' ) ? wp_get_jed_locale_data( 'amp' ) : gutenberg_get_jed_locale_data( 'amp' );
			wp_add_inline_script(
				'wp-i18n',
				'wp.i18n.setLocaleData( ' . wp_json_encode( $locale_data ) . ', "amp" );',
				'after'
			);
		}
	}

	/**
	 * Enqueues the front-end block stylesheet.
	 */
	public function enqueue_block_assets() {
		$stylesheet_base = 'amp-blocks';
		wp_enqueue_style( $stylesheet_base . '-style', amp_get_asset_url( "/css/{$stylesheet_base}.css" ), array(), AMP__VERSION );
	}

	/**
	 * Tally the AMP component scripts that are needed in a dirty AMP document.
	 *
	 * @param string $content Content.
	 * @return string Content (unmodified).
	 */
	public function tally_content_requiring_amp_scripts( $content ) {
		if ( ! is_amp_endpoint() ) {
			$pattern = sprintf( '/<(%s)\b.*?>/s', join( '|', $this->amp_blocks ) );
			if ( preg_match_all( $pattern, $content, $matches ) ) {
				$this->content_required_amp_scripts = array_merge(
					$this->content_required_amp_scripts,
					$matches[1]
				);
			}
		}
		return $content;
	}

	/**
	 * Print AMP scripts required for AMP components used in a non-AMP document (dirty AMP).
	 */
	public function print_dirty_amp_scripts() {
		if ( ! is_amp_endpoint() && ! empty( $this->content_required_amp_scripts ) ) {
			wp_scripts()->do_items( $this->content_required_amp_scripts );
		}
	}

	/**
	 * Registers the dynamic block Latest Stories.
	 * Much of this is taken from the Core block Latest Posts.
	 *
	 * @see register_block_core_latest_posts()
	 */
	public function register_block_latest_stories() {
		register_block_type(
			'amp/amp-latest-stories',
			array(
				'attributes'      => array(
					'className'     => array(
						'type' => 'string',
					),
					'storiesToShow' => array(
						'type'    => 'number',
						'default' => 5,
					),
					'order'         => array(
						'type'    => 'string',
						'default' => 'desc',
					),
					'orderBy'       => array(
						'type'    => 'string',
						'default' => 'date',
					),
				),
				'render_callback' => array( $this, 'render_block_latest_stories' ),
			)
		);
	}

	/**
	 * Renders the dynamic block Latest Stories.
	 * Much of this is taken from the Core block Latest Posts.
	 *
	 * @see render_block_core_latest_posts()
	 * @param array $attributes The block attributes.
	 * @return string $markup The rendered block markup.
	 */
	public function render_block_latest_stories( $attributes ) {
		$args        = array(
			'post_type'        => AMP_Story_Post_Type::POST_TYPE_SLUG,
			'posts_per_page'   => $attributes['storiesToShow'],
			'post_status'      => 'publish',
			'order'            => $attributes['order'],
			'orderby'          => $attributes['orderBy'],
			'suppress_filters' => false,
			'meta_key'         => '_thumbnail_id',
		);
		$story_query = new WP_Query( $args );
		$min_height  = $this->get_minimum_dimension( 'height', $story_query->posts );
		$class       = 'amp-block-latest-stories';
		if ( isset( $attributes['className'] ) ) {
			$class .= ' ' . $attributes['className'];
		}

		ob_start();
		?>
		<div class="<?php echo esc_attr( $class ); ?>">
			<div class="latest-stories-carousel" style="height:<?php echo esc_attr( $min_height ); ?>px;">
				<?php
				foreach ( $story_query->posts as $post ) :
					$thumbnail_id = get_post_thumbnail_id( $post );
					if ( $thumbnail_id ) :
						$author_id           = $post->post_author;
						$author_display_name = get_the_author_meta( 'display_name', $author_id );
						$author_link         = get_author_posts_url( $author_id, $author_display_name );

						?>
						<div class="latest-stories__slide">
							<?php
							echo wp_get_attachment_image(
								$thumbnail_id,
								self::LATEST_STORIES_IMAGE_SIZE,
								false,
								array(
									'alt'   => get_the_title( $post ),
									'class' => 'latest-stories__featured-img',
								)
							);
							?>
							<a href="<?php echo esc_url( get_permalink( $post ) ); ?>">
								<span class="latest-stories__title"><?php echo esc_html( get_the_title( $post ) ); ?></span>
							</a>
							<div class="latest-stories__meta">
								<a class="latest-stories__author" href="<?php echo esc_url( $author_link ); ?>">
									<?php
									echo get_avatar(
										$author_id,
										24,
										'',
										'',
										array(
											'class' => 'latest-stories__avatar',
										)
									);
									?>
									<span><?php echo esc_html( $author_display_name ); ?></span>
								</a>
								<span class="latest-stories__time">
									<?php
									printf(
										/* translators: %s: the amount of time ago */
										esc_html__( '&#8226; %s ago', 'amp' ),
										esc_html( human_time_diff( get_post_time( 'U', false, $post->ID ), current_time( 'timestamp' ) ) )
									);
									?>
								</span>
							</div>
						</div>
						<?php
					endif;
				endforeach;
				?>
			</div>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Gets the smallest of the given dimension of any of the featured images.
	 *
	 * This iterates through all of the posts, to find their featured image.
	 * Then, this returns the smallest dimension (width or height).
	 * For example, if $dimension is 'width' and the featured image widths are 100, 200 and 300,
	 * this will return 100.
	 *
	 * @param string $dimension The dimension, either 'width' or 'height'.
	 * @param array  $posts An array or WP_Post objects.
	 * @return int $minimum_dimension The smallest dimension of a featured image.
	 */
	public function get_minimum_dimension( $dimension, $posts ) {
		if ( 'width' === $dimension ) {
			$index = 1;
		} elseif ( 'height' === $dimension ) {
			$index = 2;
		} else {
			return;
		}

		$minimum_dimension = 0;
		foreach ( $posts as $post ) {
			$thumbnail_id = get_post_thumbnail_id( $post->ID );
			if ( ! $thumbnail_id ) {
				continue;
			}

			$image = wp_get_attachment_image_src( $thumbnail_id, self::LATEST_STORIES_IMAGE_SIZE );
			if (
				isset( $image[ $index ] )
				&&
				(
					! $minimum_dimension
					||
					$image[ $index ] < $minimum_dimension
				)
			) {
				$minimum_dimension = $image[ $index ];
			}
		}

		return $minimum_dimension;
	}
}
