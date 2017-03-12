<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-base-sanitizer.php' );


class AMP_Playbuzz_Sanitizer extends AMP_Base_Sanitizer{

    public static $tag = 'div';
    public static $pb_class = 'pb_feed';



    private static $script_slug = 'amp-playbuzz';
    private static $height = '500';
    private static $script_src = 'https://cdn.ampproject.org/v0/amp-playbuzz-0.1.js';


    public function get_scripts() {
        if ( ! $this->did_convert_elements ) {
            return array();
        }
        return array( self::$script_slug => self::$script_src );
    }


    public function sanitize() {


        $nodes = $this->dom->getElementsByTagName( self::$tag );
        $num_nodes = $nodes->length;


        if ( 0 === $num_nodes ) {

            return;

        }


        for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
            $node = $nodes->item( $i );

            if(self::$pb_class !== $node -> getAttribute('class')){
                continue;
            }

            $old_attributes = AMP_DOM_Utils::get_node_attributes_as_assoc_array( $node );

            $new_attributes = $this->filter_attributes( $old_attributes );

            $new_node = AMP_DOM_Utils::create_node( $this->dom, self::$script_slug, $new_attributes );


            $node->parentNode->replaceChild( $new_node, $node );


            $this->did_convert_elements = true;

        }

    }


    private function filter_attributes( $attributes ) {
        $out = array();

        foreach ( $attributes as $name => $value ) {
            switch ( $name ) {
                case 'data-item':
                    $out[ 'data-item' ] = $value;
                    break;

                case 'data-game':
                    $out[ 'src' ] = $this->maybe_enforce_https_src( $value );
                    break;

                case 'data-game-info':
                    $out[ 'data-item-info' ] = $value;
                    break;

                case 'data-shares':
                    $out[ 'data-share-buttons' ] = $value;
                    break;

                case 'data-comments':
                    $out[ 'data-comments' ] = $value;
                    break;


                default;
                    break;
            }
        }

        $out[ 'height' ] = self::$height;

        return $out;
    }
}