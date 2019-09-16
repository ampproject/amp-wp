/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import Snapping from '../contexts/Snapping';

/**
 * Higher-order component that adds snap lines to page blocks
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( BlockEdit ) => {
		const Inner = ( props ) => {
			const { name } = props;

			if ( name !== 'amp/amp-story-page' ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<Snapping>
					<BlockEdit { ...props } />
				</Snapping>
			);
		};

		Inner.propTypes = { name: PropTypes.string.isRequired };

		return Inner;
	},
	'withSnapLines'
);
