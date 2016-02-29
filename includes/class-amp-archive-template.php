<?php

class AMP_Archive_Template extends AMP_Common_Template {
    const SITE_ICON_SIZE = 32;
    const CONTENT_MAX_WIDTH = 600;

    public $template_dir;
    public $data;

    public function __construct( $post_id ) {
        parent::__construct( $post_id );

        $this->ID = $post_id;
        $this->post = get_post( $post_id );

        $content_max_width = self::CONTENT_MAX_WIDTH;
        if ( isset( $GLOBALS['content_width'] ) && $GLOBALS['content_width'] > 0 ) {
            $content_max_width = $GLOBALS['content_width'];
        }
        $content_max_width = apply_filters( 'amp_content_max_width', $content_max_width );

        $this->data = array(
            'content_max_width' => $content_max_width,

            'document_title' => function_exists( 'wp_get_document_title' ) ? wp_get_document_title() : wp_title( '', false ), // back-compat with 4.3
            'canonical_url' => get_permalink( $post_id ),
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
            'amp_analytics' => apply_filters( 'amp_post_template_analytics', array(), $this->post ),
        );
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
}
