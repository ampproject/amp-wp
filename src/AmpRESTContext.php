<?php
/**
 * AmpRESTContext.
 *
 * @package AMP
 * @since   2.1
 */

namespace AmpProject\AmpWP;

use AMP_Content_Sanitizer;
use AMP_DOM_Utils;
use AMP_HTTP;
use AMP_Post_Type_Support;
use AMP_Theme_Support;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Post;
use WP_REST_Response;

/**
 * Class AmpRESTContext.
 */
final class AmpRESTContext implements Service, Delayed, Registerable {

	const AMP_CONTENT_REST_FIELD = 'amp';
	const AMP_LINKS_REST_FIELD   = 'amp_links';

	/**
	 * Provides the WordPress action on which to register.
	 *
	 * @return string
	 */
	public static function get_registration_action() {
		return 'rest_api_init';
	}

	/**
	 * Registers actions and filters to be used during the REST API initialization.
	 */
	public function register() {
		foreach ( AMP_Post_Type_Support::get_supported_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, 'editor' ) ) {
				add_filter( 'rest_prepare_' . $post_type, [ $this, 'add_content_amp_field' ], 10, 3 );
				add_filter( 'rest_' . $post_type . '_item_schema', [ $this, 'extend_content_schema' ] );

				register_rest_field(
					$post_type,
					self::AMP_LINKS_REST_FIELD,
					[
						'get_callback' => [ $this, 'get_amp_links' ],
						'schema'       => [
							'description' => __( 'Links for the AMP version.', 'amp' ),
							'type'        => 'object',
							'context'     => [ 'amp' ],
							'readonly'    => true,
							'properties'  => [
								'complete_template'  => [
									'description' => __( 'Links for the AMP version in a complete template.', 'amp' ),
									'type'        => 'object',
									'context'     => [ 'amp' ],
									'readonly'    => true,
									'properties'  => [
										'cache'  => [
											'type'        => 'string',
											'description' => __( 'Link for the AMP version in a complete template on origin.', 'amp' ),
											'context'     => [ 'amp' ],
											'readonly'    => true,
										],
										'origin' => [
											'type'        => 'string',
											'description' => __( 'Link for the AMP version in a complete template on the ampproject.org AMP Cache.', 'amp' ),
											'context'     => [ 'amp' ],
											'readonly'    => true,
										],
									],
								],
								'standalone_content' => [
									'description' => __( 'Links for the AMP version in standalone content.', 'amp' ),
									'type'        => 'object',
									'context'     => [ 'amp' ],
									'readonly'    => true,
									'properties'  => [
										'cache'  => [
											'type'        => 'string',
											'description' => __( 'Link for the AMP version in standalone content on origin.', 'amp' ),
											'context'     => [ 'amp' ],
											'readonly'    => true,
										],
										'origin' => [
											'type'        => 'string',
											'description' => __( 'Link for the AMP version in standalone content on the ampproject.org AMP Cache.', 'amp' ),
											'context'     => [ 'amp' ],
											'readonly'    => true,
										],
									],
								],
							],
						],
					]
				);
			}
		}
	}

	/**
	 * Adds "amp" to all context arrays in a schema where "view" is present.
	 *
	 * @param array $schema API schema.
	 * @return array Modified schema.
	 */
	public function add_amp_context_where_context_has_view( $schema ) {
		if ( ! is_array( $schema ) ) {
			return $schema;
		}

		if ( isset( $schema['context'] ) && in_array( 'view', $schema['context'], true ) ) {
			$schema['context'][] = 'amp';
		}

		if ( ! isset( $schema['type'] ) ) {
			return $schema;
		}

		$type           = $schema['type'];
		$is_array_type  = ( 'array' === $type || ( is_array( $type ) && in_array( 'array', $type, true ) ) ) && isset( $schema['items'] );
		$is_object_type = ( 'object' === $type || ( is_array( $type ) && in_array( 'object', $type, true ) ) ) && isset( $schema['properties'] );

		if ( ! $is_array_type && ! $is_object_type ) {
			return $schema;
		}

		if ( $is_array_type ) {
			$schema['items'] = array_map( [ $this, 'add_amp_context_where_context_has_view' ], $schema['items'] );
			return $schema;
		}

		foreach ( $schema['properties'] as $key => $value ) {
			$schema['properties'][ $key ] = $this->add_amp_context_where_context_has_view( $value );
		}

		return $schema;
	}

	/**
	 * Extends the schema of the content field with a new `amp` property.
	 *
	 * @param array $schema Post schema data.
	 * @return array Schema.
	 */
	public function extend_content_schema( $schema ) {
		$schema = $this->add_amp_context_where_context_has_view( $schema );

		if ( ! is_array( $schema ) || ! isset( $schema['properties']['content']['properties'] ) ) {
			return $schema;
		}

		$schema['properties']['content']['properties'][ self::AMP_CONTENT_REST_FIELD ] = [
			'description' => __( 'The AMP content for the object.', 'amp' ),
			'type'        => 'object',
			'context'     => [ 'amp' ],
			'readonly'    => true,
			'properties'  => [
				'markup'  => [
					'description' => __( 'HTML content for the object, transformed to be valid AMP.', 'amp' ),
					'type'        => 'string',
					'context'     => [ 'amp' ],
					'readonly'    => true,
				],
				'styles'  => [
					'description' => __( 'An array of tree-shaken CSS styles extracted from the content.', 'amp' ),
					'type'        => 'array',
					'items'       => [
						'type' => 'string',
					],
					'context'     => [ 'amp' ],
					'readonly'    => true,
				],
				'scripts' => [
					'description'          => __( 'An object of scripts, extracted from the AMP elements and templates present in the content.', 'amp' ),
					'type'                 => 'object',
					'context'              => [ 'amp' ],
					'readonly'             => true,
					'additionalProperties' => [
						'type'       => 'object',
						'context'    => [ 'amp' ],
						'readonly'   => true,
						'properties' => [
							'src'               => [
								'type'        => 'string',
								'description' => __( 'The source of the script.', 'amp' ),
								'context'     => [ 'amp' ],
								'readonly'    => true,
							],
							'runtime_version'   => [
								'type'        => 'string',
								'description' => __( 'The runtime version of AMP used by the script.', 'amp' ),
								'context'     => [ 'amp' ],
								'readonly'    => true,
							],
							'extension_version' => [
								'type'        => 'string',
								'description' => __( 'The version of the script itself.', 'amp' ),
								'context'     => [ 'amp' ],
								'readonly'    => true,
							],
							'async'             => [
								'type'        => 'boolean',
								'description' => __( 'Whether or not the script should be loaded asynchronously.', 'amp' ),
								'context'     => [ 'amp' ],
								'readonly'    => true,
							],
							'extension_type'    => [
								'type'        => 'string',
								'enum'        => [ 'custom-template', 'custom-element' ],
								'description' => __( 'Type of the script, either a template or an element.', 'amp' ),
								'context'     => [ 'amp' ],
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
	 * Callback for the amp_links REST field.
	 *
	 * @param array $post_array Array of prepared WP Post data.
	 * @return array
	 */
	public function get_amp_links( $post_array ) {
		$standalone_content_url = add_query_arg(
			StandaloneContent::STANDALONE_CONTENT_QUERY_VAR,
			'',
			get_permalink( $post_array['id'] )
		);
		$content_template_link  = amp_get_permalink( $post_array['id'] );

		return [
			'standalone_content' => [
				'origin' => $standalone_content_url,
				'cache'  => AMP_HTTP::get_amp_cache_url( $standalone_content_url ),
			],
			'complete_template'  => [
				'origin' => $content_template_link,
				'cache'  => AMP_HTTP::get_amp_cache_url( $content_template_link ),
			],
		];
	}

	/**
	 * Adds a new field `amp` in the content of the REST API response.
	 *
	 * @param  WP_REST_Response $response Response object.
	 * @param  WP_Post          $post     Post object.
	 * @param  WP_REST_Request  $request  Request object.
	 * @return WP_REST_Response Response object.
	 */
	public function add_content_amp_field( $response, $post, $request ) {
		// Skip if AMP is disabled for the post.
		if ( ! amp_is_post_supported( $post ) ) {
			return $response;
		}

		if ( 'amp' !== $request['context'] ) {
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

		// Clean up applying filters for whatever comes next.
		remove_filter( 'wp_video_shortcode_library', $return_amp );
		remove_filter( 'wp_audio_shortcode_library', $return_amp );
		foreach ( $embed_handlers as $embed_handler ) {
			$embed_handler->unregister_embed();
		}

		$response->data['content']['amp'] = $data;

		return $response;
	}
}
