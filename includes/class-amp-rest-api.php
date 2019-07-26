<?php
/**
 * AMP REST API.
 *
 * @package AMP
 * @since   2.0
 */

/**
 * Class AMP_REST_API.
 */
class AMP_REST_API {

	/**
	 * Init.
	 */
	public static function init() {
		add_action( 'rest_api_init', [ __CLASS__, 'rest_api_init' ] );
	}

	/**
	 * Register other actions and filters to be used during the REST API initialization.
	 *
	 * @return void
	 */
	public static function rest_api_init() {
		// Register a rest_prepare_{$post_type} filter for each one of the post types supported
		// by the AMP plugin.
		foreach ( AMP_Post_Type_Support::get_eligible_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, AMP_Post_Type_Support::SLUG ) && post_type_supports( $post_type, 'editor' ) ) {
				add_filter( 'rest_prepare_' . $post_type, [ __CLASS__, 'add_content_amp_field' ], 10, 3 );

				// The rest_{$this->post_type}_item_schema filter is still a work in progress:
				// https://core.trac.wordpress.org/ticket/47779
				add_filter( 'rest_' . $post_type . '_item_schema', [ __CLASS__, 'extend_content_schema' ], 10, 2 );
			}
		}
	}

	/**
	 * Extends the schema of the content field with a new `amp` property.
	 *
	 * @param array  $schema         Post schema data.
	 * @param string $post_type_slug Post type slug.
	 */
	public static function extend_content_schema( $schema, $post_type_slug ) {
		$schema['properties']['content']['properties']['amp'] = [
			'description' => __( 'The AMP content for the object.' ),
			'type'        => 'object',
			'context'     => [ 'view', 'edit' ],
			'readonly'    => true,
			'properties'  => [
				'markup'  => [
					'description' => __( 'HTML content for the object, transformed to be valid AMP.' ),
					'type'        => 'string',
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'styles'  => [
					'description' => __( 'An array of tree-shaken CSS styles extracted from the content.' ),
					'type'        => 'array',
					'items'       => [
						'type' => 'string',
					],
					'context'     => [ 'view', 'edit' ],
					'readonly'    => true,
				],
				'scripts' => [
					'description'          => __( 'An object of scripts, extracted from the AMP elements and templates present in the content.' ),
					'type'                 => 'object',
					'context'              => [ 'view', 'edit' ],
					'readonly'             => true,
					'additionalProperties' => [
						'type'       => 'object',
						'context'    => [ 'view', 'edit' ],
						'readonly'   => true,
						'properties' => [
							'src'               => [
								'type'        => 'string',
								'description' => __( 'The source of the script.' ),
								'context'     => [ 'view', 'edit' ],
								'readonly'    => true,
							],
							'runtime_version'   => [
								'type'        => 'string',
								'description' => __( 'The runtime version of AMP used by the script.' ),
								'context'     => [ 'view', 'edit' ],
								'readonly'    => true,
							],
							'extension_version' => [
								'type'        => 'string',
								'description' => __( 'The version of the script itself.' ),
								'context'     => [ 'view', 'edit' ],
								'readonly'    => true,
							],
							'async'             => [
								'type'        => 'boolean',
								'description' => __( 'Whether or not the script should be loaded asynchronously.' ),
								'context'     => [ 'view', 'edit' ],
								'readonly'    => true,
							],
							'extension_type'    => [
								'type'        => 'string',
								'enum'        => [ 'custom-template', 'custom-element' ],
								'description' => __( 'Type of the script, either a template or an element.' ),
								'context'     => [ 'view', 'edit' ],
								'readonly'    => true,
							],
						],
					],
				],
			],
		];
		return $schema;
	}

	/**
	 * Adds a new field `amp` in the content of the REST API response.
	 *
	 * @param  WP_REST_Response $response Response object.
	 * @param  WP_Post          $post     Post object.
	 * @param  WP_REST_Request  $request  Request object.
	 * @return WP_REST_Response Response object.
	 */
	public static function add_content_amp_field( $response, $post, $request ) {
		// Skip if _amp param is not present.
		// @todo Figure out a better way to selectively include amp content without having to introduce a new query var.
		if ( null === $request->get_param( '_amp' ) ) {
			return $response;
		}

		// Skip if AMP is disabled for the post.
		if ( ! post_supports_amp( $post ) ) {
			return $response;
		}

		$sanitizers     = amp_get_content_sanitizers();
		$embed_handlers = AMP_Theme_Support::register_content_embed_handlers();
		$sanitizers['AMP_Embed_Sanitizer']['embed_handlers'] = $embed_handlers;

		$return_amp = static function() {
			return 'amp';
		};

		add_filter( 'wp_video_shortcode_library', $return_amp );
		add_filter( 'wp_audio_shortcode_library', $return_amp );

		$data = [
			'markup'  => '',
			'styles'  => '',
			'scripts' => [],
		];

		/*
		 * Note that $response->data['content']['rendered'] is not being used here because the embed handlers were not
		 * registered when the_content was previously applied.
		 */
		/** This filter is documented in wp-includes/post-template.php */
		$content = apply_filters( 'the_content', $post->post_content );

		$dom     = AMP_DOM_Utils::get_dom_from_content( $content );
		$results = AMP_Content_Sanitizer::sanitize_document( $dom, $sanitizers, [ 'return_styles' => false ] );

		$data['markup'] = AMP_DOM_Utils::get_content_from_dom( $dom );
		$data['styles'] = $results['stylesheets'];
		foreach ( $results['scripts'] as $handle => $src ) {
			if ( true === $src && wp_script_is( $handle, 'registered' ) ) {
				$src = wp_scripts()->registered[ $handle ]->src;
			}

			/*
			 * Extract both the runtime version and the extension version.
			 * The regexp pattern used comes from the official documentation:
			 * https://amp.dev/documentation/guides-and-tutorials/learn/spec/amphtml/#extended-components
			 */
			if ( preg_match( '#/v(?P<runtime_version>\d+)/[a-z-]+-(?P<extension_version>latest|\d+|\d+\.\d+)\.js#', $src, $matches ) ) {
				$data['scripts'][ $handle ] = array_merge(
					compact( 'src' ),
					wp_array_slice_assoc( $matches, [ 'runtime_version', 'extension_version' ] ),
					[
						'async'          => true,
						'extension_type' => 'amp-mustache' === $handle ? 'custom-template' : 'custom-element',
					]
				);
			}
		}

		// Remove filters that to clean up applying filters for whatever comes next.
		remove_filter( 'wp_video_shortcode_library', $return_amp );
		remove_filter( 'wp_audio_shortcode_library', $return_amp );
		foreach ( $embed_handlers as $embed_handler ) {
			$embed_handler->unregister_embed();
		}

		$response->data['content']['amp'] = $data;

		return $response;
	}
}
