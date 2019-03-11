/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';
import { DropZoneProvider } from '@wordpress/components';

/**
 * Internal dependencies
 */
import ReordererItem from './reorderer-item';

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

export default withSelect( ( select ) => {
	const { getBlocksByClientId } = select( 'core/editor' );
	const { getBlockOrder } = select( 'amp/story' );

	const pages = getBlocksByClientId( getBlockOrder() );

	return {
		pages,
	};
} )( Reorderer );

