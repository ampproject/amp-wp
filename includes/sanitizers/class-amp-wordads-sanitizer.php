<?php
/**
* Class AMP_WordAds_Sanitizer
*
*@package AMP
*/

/**
* Class AMP_WordAds_Sanitizer
*
* Converts WordAds to <amp-ad>
*
* @see https://wordads.co/
*/
class AMP_WordAds_Sanitizer extends AMP_Base_Sanitizer {

  /**
   * Sanitize the WordAds elements from the HTML contained in this instance's DOMDocument.
   *
   * @since 0.9.97.19
   */
  public function sanitize() {
    if( defined('ADCONTROL_VERSION') ){

      add_action( 'amp_post_template_css', array( $this, 'add_ad_styles' ) );

      foreach( $this->dom->getElementsByTagName( 'div' ) as $div ) {
        if( strpos( $div->getAttribute('id'), 'atatags') !== false ) {
          $section_id = substr($div->getAttribute('id'), -1);
          $blog_id = explode('-', $div->getAttribute('id'))[1];
          $blog_id =  substr($blog_id, 0, -1);

          foreach( $div->getElementsByTagName( 'div' ) as $div2 ) {
            if( strpos( $div2->getAttribute('id'), 'crt') !== false ) {
              $styles = explode( ';', $div2->getAttribute('style') );
              foreach( $styles as $style ) {
                if( (strpos( $style, 'width') !== false) or (strpos($style, 'height') !== false) ) {
                  $key = explode(':', $style)[0];
                  $$key = str_replace('px', '', explode(':', $style)[1]);
                }
              }
            }
          }

          while($div->hasChildNodes())
            $div->removeChild($div->firstChild);

          $new_node = AMP_DOM_Utils::create_node(
            $this->dom,
            'amp-ad',
            array(
              'type' => 'pubmine',
              'data-section' => $section_id,
              'data-pt' => '1',
              'data-ht' => '2',
              'data-siteid' => $blog_id,
              'width' => $width,
              'height' => $height
            )
          );

          $div->appendChild($new_node);
        }
      }
    }
  }

  public function add_ad_styles() {
    ?>
    .wpa {
      position: relative;
      overflow: hidden;
      display: inline-block;
      max-width: 100%;
    }

    .wpa-about {
      position: absolute;
      top: 5px;
      left: 0;
      right: 0;
      display: block;
      margin-top: 0;
      color: #888;
      font: 10px/1 "Open Sans",Arial,sans-serif!important;
      text-align: left!important;
      text-decoration: none!important;
      opacity: .85;
      border-bottom: none!important;
      box-shadow: none!important;
    }
    <?php
  }
}
