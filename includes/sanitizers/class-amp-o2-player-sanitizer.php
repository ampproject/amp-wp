<?php
/**
 * Class AMP_O2_Player_Sanitizer
 *
 * @package AMP
 */

/**
 * Class AMP_O2_Player_Sanitizer
 *
 * Converts <div class="vdb_player><script></script></div> embed to <amp-o2-player>
 *
 * @see https://www.ampproject.org/docs/reference/components/amp-o2-player
 */
class AMP_O2_Player_Sanitizer extends AMP_Base_Sanitizer {
	const URL_PATTERN = '#.*delivery.vidible.tv\/jsonp\/pid=(.*)\/vid=(.*)\/(.*).js.*#i';

	/**
	 * AMP Tag.
	 *
	 * @var string AMP Tag.
	 */
	public static $amp_tag = 'amp-o2-player';

	/**
	 * Amp O2 Player class.
	 *
	 * @var string CSS class to identify O2 Player <div> to replace with AMP version.
	 */
	public static $xpath_selector = '//div[ contains( @class, \'vdb_player\' ) ]/script';

	/**
	 * Hardcoded height to set for 02 Player elements.
	 *
	 * @var string
	 */
	private static $height = '270';

	/**
	 * Hardcoded height to set for 02 Player elements.
	 *
	 * @var string
	 */
	private static $width = '600';

	/**
	 * XPath.
	 *
	 * @var DOMXPath
	 */
	private $xpath;

	/**
	 * AMP_O2_Player_Sanitizer constructor.
	 *
	 * @param DOMDocument $dom  Represents the HTML document to sanitize.
	 * @param array       $args Args.
	 */
	public function __construct( DOMDocument $dom, array $args = array() ) {
		parent::__construct( $dom, $args );
		$this->xpath = new DOMXPath( $dom );
	}

	/**
	 * Sanitize the O2 Player elements from the HTML contained in this instance's DOMDocument.
	 */
	public function sanitize() {
		/**
		 * Node list.
		 *
		 * @var DOMNodeList $nodes
		 */
		$nodes     = $this->xpath->query( self::$xpath_selector );
		$num_nodes = $nodes->length;

		if ( 0 === $num_nodes ) {
			return;
		}

		for ( $i = $num_nodes - 1; $i >= 0; $i-- ) {
			$node = $nodes->item( $i );

			$this->create_amp_o2_player( $this->dom, $node );
		}

	}

	/**
	 * Replaces node with amp-o2-player
	 *
	 * @param DOMDocument $dom The HTML Document.
	 * @param DOMNode     $node The DOMNode to adjust and replace.
	 */
	private function create_amp_o2_player( $dom, $node ) {
		$o2_attributes = $this->get_o2_player_attributes( $node->getAttribute( 'src' ) );

		if ( ! empty( $o2_attributes ) ) {
			$component_attributes = array_merge(
				$o2_attributes, array(
					'data-macros' => 'm.playback=click',
					'layout'      => 'responsive',
					'width'       => self::$width,
					'height'      => self::$height,
				)
			);

			$amp_o2_player = AMP_DOM_Utils::create_node( $dom, self::$amp_tag, $component_attributes );

			$parent_node = $node->parentNode;

			// replaces the wrapper that contains the script with amp-o2-player element.
			$parent_node->parentNode->replaceChild( $amp_o2_player, $parent_node );

			$this->did_convert_elements = true;
		}
	}

	/**
	 * Gets O2 Player's required attributes from script src
	 *
	 * @param string $src script src.
	 *
	 * @return array data-attributes for o2 player.
	 */
	private function get_o2_player_attributes( $src ) {
		$found = preg_match( self::URL_PATTERN, $src, $matches );
		if ( $found ) {
			return array(
				'data-pid'  => $matches[1],
				'data-vid'  => $matches[2],
				'data-bcid' => $matches[3],
			);
		}
		return array();
	}

}
