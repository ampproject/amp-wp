/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

export default function Selector( { selector, isLast, isStyleAttribute } ) {
	let specificityBumper = null;
	let ampWpSelector = null;

	const specificityBumperMatch = selector.match( /(:root|html)(:not\(#_\))+/ );
	if ( specificityBumperMatch ) {
		specificityBumper = (
			<abbr
				title={ isStyleAttribute
					? __( 'Selector generated to increase specificity for important properties so that the CSS cascade is preserved. AMP does not allow important properties.', 'amp' )
					: __( 'Selector generated to increase specificity so the cascade is preserved for properties moved from style attribute to CSS rules in style[amp-custom].', 'amp' )
				}
			>
				{ specificityBumperMatch[ 0 ] }
			</abbr>
		);

		selector = selector.substring( specificityBumperMatch[ 0 ].length );
	}

	if ( isStyleAttribute ) {
		const ampWpSelectorMatch = selector.match( /\.amp-wp-\w+/ );

		if ( ampWpSelectorMatch ) {
			ampWpSelector = (
				<abbr title={ __( 'Class name generated during extraction of inline style to style[amp-custom].', 'amp' ) }>
					{ ampWpSelectorMatch[ 0 ] }
				</abbr>
			);

			selector = selector.substring( ampWpSelectorMatch[ 0 ].length );
		}
	}

	return (
		<>
			{ specificityBumper }
			{ ampWpSelector }
			{ selector }
			{ ! isLast ? ',' : '' }
		</>
	);
}
Selector.propTypes = {
	selector: PropTypes.string,
	isLast: PropTypes.bool,
	isStyleAttribute: PropTypes.bool,
};
