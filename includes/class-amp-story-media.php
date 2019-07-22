<?php

class AMP_Story_Media {
	/**
	 * The image size for the AMP story card, used in an embed and the Latest Stories block.
	 *
	 * @var string
	 */
	const STORY_CARD_IMAGE_SIZE = 'amp-story-poster-portrait';

	/**
	 * The image size for the poster-landscape-src.
	 *
	 * @var string
	 */
	const STORY_LANDSCAPE_IMAGE_SIZE = 'amp-story-poster-landscape';

	/**
	 * The image size for the poster-square-src.
	 *
	 * @var string
	 */
	const STORY_SQUARE_IMAGE_SIZE = 'amp-story-poster-square';

	/**
	 * The slug of the largest image size allowed in an AMP Story page.
	 *
	 * @var string
	 */
	const MAX_IMAGE_SIZE_SLUG = 'amp_story_page';

	/**
	 * The large dimension of the AMP Story poster images.
	 *
	 * @var int
	 */
	const STORY_LARGE_IMAGE_DIMENSION = 928;

	/**
	 * The small dimension of the AMP Story poster images.
	 *
	 * @var int
	 */
	const STORY_SMALL_IMAGE_DIMENSION = 696;

	public static function init() {
		// Used for amp-story[publisher-logo-src]: The publisher's logo in square format (1x1 aspect ratio). This will be supplied by the custom logo or else site icon.
		add_image_size( 'amp-publisher-logo', 100, 100, true );

		// Used for amp-story[poster-portrait-src]: The story poster in portrait format (3x4 aspect ratio).
		add_image_size( self::STORY_CARD_IMAGE_SIZE, self::STORY_SMALL_IMAGE_DIMENSION, self::STORY_LARGE_IMAGE_DIMENSION, true );

		// Used for amp-story[poster-square-src]: The story poster in square format (1x1 aspect ratio).
		add_image_size( self::STORY_SQUARE_IMAGE_SIZE, self::STORY_LARGE_IMAGE_DIMENSION, self::STORY_LARGE_IMAGE_DIMENSION, true );

		// Used for amp-story[poster-landscape-src]: The story poster in square format (1x1 aspect ratio).
		add_image_size( self::STORY_LANDSCAPE_IMAGE_SIZE, self::STORY_LARGE_IMAGE_DIMENSION, self::STORY_SMALL_IMAGE_DIMENSION, true );

		// The default image size for AMP Story image block and background media image.
		add_image_size( self::MAX_IMAGE_SIZE_SLUG, 99999, 1440 );

		// In case there is no featured image for the poster-portrait-src, add a fallback image.
		add_filter( 'wp_get_attachment_image_src', [ __CLASS__, 'poster_portrait_fallback' ], 10, 3 );

		// If the image is for a poster-square-src or poster-landscape-src, this ensures that it's not too small.
		add_filter( 'wp_get_attachment_image_src', [ __CLASS__, 'ensure_correct_poster_size' ], 10, 3 );

		add_filter( 'image_size_names_choose', [ __CLASS__, 'add_new_max_image_size' ] );

		// The AJAX handler for when an image is cropped and sent via POST.
		add_action( 'wp_ajax_custom-header-crop', [ __CLASS__, 'crop_featured_image' ] );

		add_action( 'rest_api_init', [ __CLASS__, 'rest_api_init' ] );
	}

	/**
	 * If there's no featured image for the poster-portrait-src, this adds a fallback.
	 *
	 * @param array|false  $image The featured image, or false.
	 * @param int          $attachment_id The ID of the image.
	 * @param string|array $size The size of the image.
	 * @return array|false The featured image, or false.
	 */
	public static function poster_portrait_fallback( $image, $attachment_id, $size ) {
		if ( ! $image && self::STORY_CARD_IMAGE_SIZE === $size ) {
			return [
				amp_get_asset_url( 'images/story-fallback-poster.jpg' ),
				self::STORY_LARGE_IMAGE_DIMENSION,
				self::STORY_SMALL_IMAGE_DIMENSION,
			];
		}

		return $image;
	}

	/**
	 * Helps to ensure that the poster-square-src and poster-landscape-src images aren't too small.
	 *
	 * These values come from the featured image.
	 * But the featured image is often cropped down to 696 x 928.
	 * So from that, it's not possible to get a 928 x 928 image, for example.
	 * So instead, use the source image that was cropped, instead of the cropped image.
	 * This is more likely to produce the right size image.
	 *
	 * @param array|false  $image The featured image, or false.
	 * @param int          $attachment_id The ID of the image.
	 * @param string|array $size The size of the image.
	 * @return array|false The featured image, or false.
	 */
	public static function ensure_correct_poster_size( $image, $attachment_id, $size ) {
		if ( self::STORY_LANDSCAPE_IMAGE_SIZE === $size || self::STORY_SQUARE_IMAGE_SIZE === $size ) {
			$attachment_meta = wp_get_attachment_metadata( $attachment_id );
			// The source image that was cropped.
			if ( ! empty( $attachment_meta['attachment_parent'] ) ) {
				return wp_get_attachment_image_src( $attachment_meta['attachment_parent'], $size );
			}
		}
		return $image;
	}

	/**
	 * Adds a new max image size to the image sizes available.
	 *
	 * In the AMP story editor, when selecting Background Media,
	 * it will use this custom image size.
	 * This filter will also make it available in the Image block's 'Image Size' <select> element.
	 *
	 * @param array $image_sizes {
	 *     An associative array of image sizes.
	 *
	 *     @type string $slug Image size slug, like 'medium'.
	 *     @type string $name Image size name, like 'Medium'.
	 * }
	 * @return array $image_sizes The filtered image sizes.
	 */
	public static function add_new_max_image_size( $image_sizes ) {
		$full_size_name = __( 'Story Max Size', 'amp' );

		if ( isset( $_POST['action'] ) && ( 'query-attachments' === $_POST['action'] || 'upload-attachment' === $_POST['action'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$image_sizes[ self::MAX_IMAGE_SIZE_SLUG ] = $full_size_name;
		} elseif ( get_post_type() && AMP_Story_Post_Type::POST_TYPE_SLUG === get_post_type() ) {
			$image_sizes[ self::MAX_IMAGE_SIZE_SLUG ] = $full_size_name;
			unset( $image_sizes['full'] );
		}

		return $image_sizes;
	}

	/**
	 * Create an attachment 'object'.
	 *
	 * Forked from Custom_Image_Header::create_attachment_object() in Core.
	 *
	 * @param string $cropped Cropped image URL.
	 * @param int    $parent_attachment_id Attachment ID of parent image.
	 * @return array Attachment object.
	 */
	public static function create_attachment_object( $cropped, $parent_attachment_id ) {
		$parent     = get_post( $parent_attachment_id );
		$parent_url = wp_get_attachment_url( $parent->ID );
		$url        = str_replace( basename( $parent_url ), basename( $cropped ), $parent_url );
		$size       = null;

		try {
			$size = getimagesize( $cropped );
		} catch ( Exception $error ) {
			unset( $error );
		}

		$image_type = $size ? $size['mime'] : 'image/jpeg';
		$object     = [
			'ID'             => $parent_attachment_id,
			'post_title'     => basename( $cropped ),
			'post_mime_type' => $image_type,
			'guid'           => $url,
			'context'        => 'amp-story-poster',
			'post_parent'    => $parent_attachment_id,
		];

		return $object;
	}

	/**
	 * Insert an attachment and its metadata.
	 *
	 * Forked from Custom_Image_Header::insert_attachment() in Core.
	 *
	 * @param array  $object  Attachment object.
	 * @param string $cropped Cropped image URL.
	 * @return int Attachment ID.
	 */
	public static function insert_attachment( $object, $cropped ) {
		$parent_id = isset( $object['post_parent'] ) ? $object['post_parent'] : null;
		unset( $object['post_parent'] );

		$attachment_id = wp_insert_attachment( $object, $cropped );
		$metadata      = wp_generate_attachment_metadata( $attachment_id, $cropped );

		// If this is a crop, save the original attachment ID as metadata.
		if ( $parent_id ) {
			$metadata['attachment_parent'] = $parent_id;
		}
		wp_update_attachment_metadata( $attachment_id, $metadata );

		return $attachment_id;
	}

	/**
	 * Crops the image and returns the object as JSON.
	 *
	 * Forked from Custom_Image_Header::ajax_header_crop().
	 */
	public static function crop_featured_image() {
		check_ajax_referer( 'image_editor-' . $_POST['id'], 'nonce' );

		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_send_json_error();
		}

		$crop_details = $_POST['cropDetails'];

		$dimensions = [
			'dst_width'  => self::STORY_SMALL_IMAGE_DIMENSION,
			'dst_height' => self::STORY_LARGE_IMAGE_DIMENSION,
		];

		$attachment_id = absint( $_POST['id'] );

		$cropped = wp_crop_image(
			$attachment_id,
			(int) $crop_details['x1'],
			(int) $crop_details['y1'],
			(int) $crop_details['width'],
			(int) $crop_details['height'],
			(int) $dimensions['dst_width'],
			(int) $dimensions['dst_height']
		);

		if ( ! $cropped || is_wp_error( $cropped ) ) {
			wp_send_json_error( [ 'message' => __( 'Image could not be processed. Please go back and try again.', 'default' ) ] );
		}

		/** This filter is documented in wp-admin/custom-header.php */
		$cropped = apply_filters( 'wp_create_file_in_uploads', $cropped, $attachment_id ); // For replication.
		$object  = self::create_attachment_object( $cropped, $attachment_id );
		unset( $object['ID'] );

		$new_attachment_id       = self::insert_attachment( $object, $cropped );
		$object['attachment_id'] = $new_attachment_id;
		$object['url']           = wp_get_attachment_url( $new_attachment_id );
		$object['width']         = $dimensions['dst_width'];
		$object['height']        = $dimensions['dst_height'];

		wp_send_json_success( $object );
	}

	/**
	 * Registers additional REST API fields upon API initialization.
	 */
	public static function rest_api_init() {
		register_rest_field(
			'attachment',
			'featured_media',
			[
				'schema' => [
					'description' => __( 'The ID of the featured media for the object.' ),
					'type'        => 'integer',
					'context'     => [ 'view', 'edit', 'embed' ],
				],
			]
		);
	}
}
