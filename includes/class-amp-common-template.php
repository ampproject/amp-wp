<?php

require_once( AMP__DIR__ . '/includes/utils/class-amp-dom-utils.php' );
require_once( AMP__DIR__ . '/includes/utils/class-amp-html-utils.php' );

require_once( AMP__DIR__ . '/includes/class-amp-content.php' );

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-blacklist-sanitizer.php' );
require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-img-sanitizer.php' );
require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-video-sanitizer.php' );
require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-iframe-sanitizer.php' );
require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-audio-sanitizer.php' );

require_once( AMP__DIR__ . '/includes/embeds/class-amp-twitter-embed.php' );
require_once( AMP__DIR__ . '/includes/embeds/class-amp-youtube-embed.php' );
require_once( AMP__DIR__ . '/includes/embeds/class-amp-gallery-embed.php' );
require_once( AMP__DIR__ . '/includes/embeds/class-amp-instagram-embed.php' );
require_once( AMP__DIR__ . '/includes/embeds/class-amp-vine-embed.php' );
require_once( AMP__DIR__ . '/includes/embeds/class-amp-facebook-embed.php' );

class AMP_Common_Template {
    const SITE_ICON_SIZE = 32;
    const CONTENT_MAX_WIDTH = 600;

    public $template_dir;
    public $data;

    public function __construct( $post_id ) {
        $this->template_dir = AMP__DIR__ . '/templates';
    }

    public function get( $property, $default = null ) {
        if ( isset( $this->data[ $property ] ) ) {
            return $this->data[ $property ];
        } else {
            _doing_it_wrong( __METHOD__, sprintf( __( 'Called for non-existant key ("%s").', 'amp' ), esc_html( $property ) ), '0.1' );
        }

        return $default;
    }

    public function load() {
        $this->load_parts( array( 'single' ) );
    }

    public function load_parts( $templates ) {
        foreach ( $templates as $template ) {
            $file = $this->get_template_path( $template );
            $this->verify_and_include( $file, $template );
        }
    }

    public function get_template_path( $template ) {
        return sprintf( '%s/%s.php', $this->template_dir, $template );
    }

    public function add_data( $data ) {
        $this->data = array_merge( $this->data, $data );
    }

    public function add_data_by_key( $key, $value ) {
        $this->data[ $key ] = $value;
    }

    public function merge_data_for_key( $key, $value ) {
        if ( is_array( $this->data[ $key ] ) ) {
            $this->data[ $key ] = array_merge( $this->data[ $key ], $value );
        } else {
            $this->add_data_by_key( $key, $value );
        }
    }

    public function build_post_data() {
        $post_title = get_the_title( $this->ID );
        $post_publish_timestamp = get_the_date( 'U', $this->ID );
        $post_modified_timestamp = get_post_modified_time( 'U', false, $this->post );
        $post_author = get_userdata( $this->post->post_author );

        $this->add_data( array(
            'post' => $this->post,
            'post_id' => $this->ID,
            'post_title' => $post_title,
            'post_publish_timestamp' => $post_publish_timestamp,
            'post_modified_timestamp' => $post_modified_timestamp,
            'post_author' => $post_author,
        ) );

        $metadata = array(
            '@context' => 'http://schema.org',
            '@type' => 'BlogPosting',
            'mainEntityOfPage' => $this->get( 'canonical_url' ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => $this->get( 'blog_name' ),
            ),
            'headline' => $post_title,
            'datePublished' => date( 'c', $post_publish_timestamp ),
            'dateModified' => date( 'c', $post_modified_timestamp ),
            'author' => array(
                '@type' => 'Person',
                'name' => $post_author->display_name,
            ),
        );

        $site_icon_url = $this->get( 'site_icon_url' );
        if ( $site_icon_url ) {
            $metadata['publisher']['logo'] = array(
                '@type' => 'ImageObject',
                'url' => $site_icon_url,
                'height' => self::SITE_ICON_SIZE,
                'width' => self::SITE_ICON_SIZE,
            );
        }

        $image_metadata = $this->get_post_image_metadata();
        if ( $image_metadata ) {
            $metadata['image'] = $image_metadata;
        }

        $this->add_data_by_key( 'metadata', apply_filters( 'amp_post_template_metadata', $metadata, $this->post ) );
    }

    public function build_post_content() {
        $amp_content = new AMP_Content( $this->post->post_content, $this->post->post_excerpt,
            apply_filters( 'amp_content_embed_handlers', array(
                'AMP_Twitter_Embed_Handler' => array(),
                'AMP_YouTube_Embed_Handler' => array(),
                'AMP_Instagram_Embed_Handler' => array(),
                'AMP_Vine_Embed_Handler' => array(),
                'AMP_Facebook_Embed_Handler' => array(),
                'AMP_Gallery_Embed_Handler' => array(),
            ), $this->post ),
            apply_filters( 'amp_content_sanitizers', array(
                 'AMP_Blacklist_Sanitizer' => array(),
                 'AMP_Img_Sanitizer' => array(),
                 'AMP_Video_Sanitizer' => array(),
                 'AMP_Audio_Sanitizer' => array(),
                 'AMP_Iframe_Sanitizer' => array(
                     'add_placeholder' => true,
                 ),
            ), $this->post ),
            array(
                'content_max_width' => $this->get( 'content_max_width' ),
            )
        );

        $this->add_data_by_key( 'post_amp_content', $amp_content->get_amp_content() );
        $this->add_data_by_key( 'post_amp_excerpt', $amp_content->get_amp_excerpt() );
        $this->merge_data_for_key( 'amp_component_scripts', $amp_content->get_amp_scripts() );
    }

    /**
     * Grabs featured image or the first attached image for the post
     *
     * TODO: move to a utils class?
     */
    public function get_post_image_metadata() {
        $post_image_meta = null;
        $post_image_id = false;

        if ( has_post_thumbnail( $this->ID ) ) {
            $post_image_id = get_post_thumbnail_id( $this->ID );
        } else {
            $attached_image_ids = get_posts( array(
                'post_parent' => $this->ID,
                'post_type' => 'attachment',
                'post_mime_type' => 'image',
                'posts_per_page' => 1,
                'orderby' => 'menu_order',
                'order' => 'ASC',
                'fields' => 'ids',
                'suppress_filters' => false,
            ) );

            if ( ! empty( $attached_image_ids ) ) {
                $post_image_id = array_shift( $attached_image_ids );
            }
        }

        if ( ! $post_image_id ) {
            return false;
        }

        $post_image_src = wp_get_attachment_image_src( $post_image_id, 'full' );

        if ( is_array( $post_image_src ) ) {
            $post_image_meta = array(
                '@type' => 'ImageObject',
                'url' => $post_image_src[0],
                'width' => $post_image_src[1],
                'height' => $post_image_src[2],
            );
        }

        return $post_image_meta;
    }

    public function verify_and_include( $file, $template_type ) {
        $located_file = $this->locate_template( $file );
        if ( $located_file ) {
            $file = $located_file;
        }

        $file = apply_filters( 'amp_post_template_file', $file, $template_type, $this->post );
        if ( ! $this->is_valid_template( $file ) ) {
            _doing_it_wrong( __METHOD__, sprintf( __( 'Path validation for template (%s) failed. Path cannot traverse and must be located in `%s`.', 'amp' ), esc_html( $file ), 'WP_CONTENT_DIR' ), '0.1' );
            return;
        }

        include( $file );
    }


    public function locate_template( $file ) {
        $search_file = sprintf( 'amp/%s', basename( $file ) );
        return locate_template( array( $search_file ), false );
    }

    public function is_valid_template( $template ) {
        if ( 0 !== validate_file( $template ) ) {
            return false;
        }

        if ( ! file_exists( $template ) ) {
            return false;
        }

        return true;
    }
}
