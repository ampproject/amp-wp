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
	}
}

