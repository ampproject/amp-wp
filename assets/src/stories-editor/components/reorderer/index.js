/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';
import { DropZoneProvider } from '@wordpress/components';

/**
 * Internal dependencies
 */
import ReordererItem from './item';
import './edit.css';

/**
 * Reorderer component.
 *
 * Displays pages as cards that can be dragged & dropped at new locations to
 * order them.
 *
 * @param {Object}   props             Indicator props.
 * @param {Array}    props.pages       Pages to list.
 *
 * @return {Object} Reorder interface.
 */
const Reorderer = ( { pages } ) => {
	return (
		<DropZoneProvider>
			<div className="amp-story-reorderer">
				{ pages.map( ( page ) => (
					<ReordererItem
						key={ `page-${ page.clientId }` }
						page={ page }
					/>
				) ) }
			</div>
		</DropZoneProvider>
	);
};

Reorderer.propTypes = {
	pages: PropTypes.arrayOf( PropTypes.shape( {
		clientId: PropTypes.string.isRequired,
	} ) ),
};

export default withSelect( ( select ) => {
	const { getBlocksByClientId } = select( 'core/block-editor' );
	const { getBlockOrder } = select( 'amp/story' );

	const pages = getBlocksByClientId( getBlockOrder() );

	return {
		pages,
	};
} )( Reorderer );

