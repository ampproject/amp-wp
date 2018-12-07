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
 * @since 1.0
 * @see https://www.ampproject.org/docs/reference/components/amp-o2-player
 */
class AMP_O2_Player_Sanitizer extends AMP_Base_Sanitizer {
	/**
	 * Pattern to extract the information required for amp-o2-player element: data-pid, data-vid, data-bcid.
	 *
	 * @since 1.0
	 */
	const URL_PATTERN = '#.*delivery.vidible.tv\/jsonp\/pid=(?<data_pid>.*)\/vid=(?<data_vid>.*)\/(?<data_bcid>.*).js.*#i';

	/**
	 * AMP Tag.
	 *
	 * @since 1.0
	 * @var string AMP Tag.
	 */
	private static $amp_tag = 'amp-o2-player';

	/**
	 * Amp O2 Player class.
	 *
	 * @since 1.0
	 * @var string CSS class to identify O2 Player <div> to replace with AMP version.
	 */
	private static $xpath_selector = '//div[ contains( @class, \'vdb_player\' ) ]/script';

	/**
	 * Height to set for O2 Player elements.
	 *
	 * @since 1.0
	 * @var string
	 */
	private static $height = '270';

	/**
	 * Width to set for O2 Player elements.
	 *
	 * @since 1.0
	 * @var string
	 */
	private static $width = '480';

	/**
	 * Sanitize the O2 Player elements from the HTML contained in this instance's DOMDocument.
	 *
	 * @since 1.0
	 */
	public function sanitize() {
		/**
		 * XPath.
		 *
		 * @var DOMXPath $xpath
		 */
		$xpath = new DOMXPath( $this->dom );

		/**
		 * Node list.
		 *
		 * @var DOMNodeList $nodes
		 */
		$nodes     = $xpath->query( self::$xpath_selector );
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
	 * @since 1.0
	 * @param DOMDocument $dom  The HTML Document.
	 * @param DOMElement  $node The DOMNode to adjust and replace.
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
	 * @since 1.0
	 * @param string $src Script src.
	 *
	 * @return array The data-* attributes for o2 player.
	 */
	private function get_o2_player_attributes( $src ) {
		$found = preg_match( self::URL_PATTERN, $src, $matches );
		if ( $found ) {
			return array(
				'data-pid'  => $matches['data_pid'],
				'data-vid'  => $matches['data_vid'],
				'data-bcid' => $matches['data_bcid'],
			);
		}
		return array();
	}

}
