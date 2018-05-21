<?php
/**
 * Class AMP_Script_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_Script_Sanitizer
 *
 * Collects inline styles and outputs them in the amp-custom stylesheet.
 */
class AMP_Script_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Known scripts.
	 *
	 * @since 1.0
	 * @var string[]
	 */
	private $known_scripts;

	function __construct( $dom, $args ) {
		parent::__construct( $dom, $args );

		$this->known_scripts = array(
			array(
				'pattern' => '#i,s,o,g,r,a,m.*GoogleAnalyticsObject.*google-analytics\.com/analytics\.js#s',
				'callback' => function( $text, $matches ) {
					if ( ! preg_match_all( 'ga\( \'(.+?)\', ... \);', $text, $matches ) ) {
						return null;
					}
					// $matches now now has a list of the params for Google Analytics.

					$config = array();
					// @todo Stuff $matches into $config.

					$analytics = AMP_DOM_Utils::create_node( $this->dom, 'amp-analytics', array() );
					$script    = AMP_DOM_Utils::create_node( $this->dom,'script', array(
						'type' => 'application/json',
					) );
					$script->textContent = wp_json_encode( $config );
					$analytics->appendChild( $script );
					return $analytics;
				}
			),
		);

		/*
		 *
		 * <script>
            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

            ga('create', "UA-52634867-3", 'auto');
            ga('send', 'pageview');
            </script>
		 */
	}

	/**
	 * Sanitize scripts that are commonly used and are available in AMP.
	 *
	 * @since 1.0
	 */
	public function sanitize() {
		$scripts = array();
		foreach ( $this->dom->getElementsByTagName( 'script' ) as $script ) {
			if ( $script->hasAttribute( 'src' ) ) { // @todo Add handling for external scripts.
				continue;
			}
			$scripts[] = $script;
//
//			// If known, track it
//			if ( $this->is_known_valid_script( $script ) ) {
//				array_push( $scripts, $script );
//			}
		}
		foreach ( $scripts as $script ) {
			foreach ($this->known_scripts as $known_script) {
				$text = $script->textContent;
				if (!preg_match($known_script['pattern'], $text, $matches)) {
					continue;
				}
				$replacement = call_user_func($known_script['callback'], $text, $matches);
				if (!$replacement) {
					continue;
				}
				$script->parentNode->replaceChild($replacement, $script);
			}
		}
//		$this->known_scripts = $this->get_known_scripts_from_page();
	}

	/**
	 * Check if script is a known, AMP valid script.
	 */
	private function is_known_valid_script( $script ) {
		return true;
	}

	/**
	 * Gather known scripts in page
	 */
	private function get_known_scripts_from_page() {

		$scripts = array();


		return $scripts;
	}
}

