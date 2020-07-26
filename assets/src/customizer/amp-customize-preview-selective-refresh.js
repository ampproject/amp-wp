( function( api ) {
	'use strict';

	const originalStyleAttributeName = 'data-amp-original-style';

	/**
	 * Handle rendering a placement.
	 *
	 * @param {wp.customize.selectiveRefresh.Placement} placement
	 * @param {jQuery}                                  placement.container
	 */
	function onPlacementRendered( placement ) {
		const container = placement.container[ 0 ];
		if ( ! ( container instanceof Element ) ) {
			return;
		}

		const styledElements = [ ...container.querySelectorAll( `[${ originalStyleAttributeName }]` ) ];
		if ( container.matches( `[${ originalStyleAttributeName }]` ) ) {
			styledElements.unshift( container );
		}

		for ( const styledElement of styledElements ) {
			styledElement.style.cssText = styledElement.getAttribute( originalStyleAttributeName );
		}
	}

	api.selectiveRefresh.bind( 'partial-content-rendered', onPlacementRendered );
}( wp.customize ) );
