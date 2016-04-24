<?php

class AMP_Archive_Template extends AMP_Common_Template {
    const SITE_ICON_SIZE = 32;
    const CONTENT_MAX_WIDTH = 600;

    public $template_dir;
    public $data;
    public $original_object;

    public function __construct( $post_id ) {
        parent::__construct( $post_id );

        $this->ID = $post_id;
        $this->post = get_query_var('amp-object');
        $this->original_object = get_query_var('amp-object');

        $content_max_width = self::CONTENT_MAX_WIDTH;
        if ( isset( $GLOBALS['content_width'] ) && $GLOBALS['content_width'] > 0 ) {
            $content_max_width = $GLOBALS['content_width'];
        }
        $content_max_width = apply_filters( 'amp_content_max_width', $content_max_width );

        if( $this->post === NULL) {
            $_canonical = get_term_link($post_id);
        }
        elseif ($this->post instanceof WP_Term) {
            $_canonical = get_term_link($post_id);
        }
        elseif ($this->post instanceof WP_User) {
            $_canonical = get_author_posts_url( $post_id );
        }

        $this->data = array(
            'content_max_width' => $content_max_width,

            'document_title' => function_exists( 'wp_get_document_title' ) ? wp_get_document_title() : wp_title( '', false ), // back-compat with 4.3
            'canonical_url' => $_canonical,
            'home_url' => home_url(),
            'blog_name' => get_bloginfo( 'name' ),

            'site_icon_url' => apply_filters( 'amp_site_icon_url', function_exists( 'get_site_icon_url' ) ? get_site_icon_url( self::SITE_ICON_SIZE ) : '' ),
            'placeholder_image_url' => amp_get_asset_url( 'images/placeholder-icon.png' ),

            'amp_runtime_script' => 'https://cdn.ampproject.org/v0.js',
            'amp_component_scripts' => array(),

            /**
             * Add amp-analytics tags.
             *
             * This filter allows you to easily insert any amp-analytics tags without needing much heavy lifting.
             *
             * @since 0.4
             *.
             * @param   array   $analytics  An associative array of the analytics entries we want to output. Each array entry must have a unique key, and the value should be an array with the following keys: `type`, `attributes`, `script_data`. See readme for more details.
             * @param   object  $post   The current post.
             */
            'amp_analytics' => apply_filters( 'amp_post_template_analytics', array(), $term ),
        );

        $this->build_archive_data();


    }

    public function have_posts() {
        global $wp_query;
        return $wp_query->have_posts();
    }


    public function the_post() {

        global $wp_query;
        $wp_query->the_post();

        $this->ID = $wp_query->post->ID;
        $this->post = $wp_query->post;

        $this->build_post_content();
        $this->build_post_data();

        $this->data = apply_filters( 'amp_post_template_data', $this->data, $this->post );
    }

    public function load() {
        $this->load_parts( array( 'archive' ) );
    }

    public function build_archive_data() {

        $args = false;

        if ($this->original_object instanceof WP_Term) {
            $post_title = $this->original_object->name;
            $args = array(
                'tax_query' => array(
                    array(
                        'taxonomy' => $this->original_object->taxonomy,
                        'field' => 'slug',
                        'terms' => $this->original_object->slug
                    )
                ),
                'posts_per_page' => 1
            );
        }
        elseif ($this->original_object instanceof WP_User) {
            $post_title = $this->original_object->display_name;
            $args = array(
                'author' => $this->original_object->ID,
                'posts_per_page' => 1
            );
        }

        if ($args) {
            $_posts = new WP_Query($args);
            $this->post = array_shift($_posts->posts);
            $this->ID = $this->post->ID;
        }

        $post_publish_timestamp = get_the_date( 'U', $this->post->ID );
        $post_modified_timestamp = get_post_modified_time( 'U', false, $this->post );
        $post_publish_timestamp = get_the_date( 'U', $this->ID );
        $post_modified_timestamp = get_post_modified_time( 'U', false, $this->post );

        $this->add_data( array(
            'post' => $this->post,
            'post_id' => $this->ID,
            'post_title' => $post_title,
            'post_publish_timestamp' => $post_publish_timestamp,
            'post_modified_timestamp' => $post_modified_timestamp,
            'post_author' => $this->post->post_author,
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
                'name' => $this->post->post_author,
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

        $this->add_data_by_key( 'metadata', apply_filters( 'amp_archive_template_metadata', $metadata, $this->post ) );
    }
}
