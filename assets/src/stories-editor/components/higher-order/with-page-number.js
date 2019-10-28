/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';
import { __, sprintf } from '@wordpress/i18n';
import { createHigherOrderComponent } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { ALLOWED_TOP_LEVEL_BLOCKS } from '../../constants';

const applyWithSelect = withSelect( ( select, props ) => {
	const {
		getBlockOrder,
		getBlockRootClientId,
	} = select( 'core/block-editor' );

	if ( '' !== getBlockRootClientId( props.clientId ) ) {
		return {
			pageNumber: undefined,
		};
	}

	const {	isReordering } = select( 'amp/story' );
	const currentIndex = getBlockOrder().indexOf( props.clientId );

	return {
		pageNumber: currentIndex + 1,
		isReordering: isReordering(),
	};
} );

/**
 * Higher-order component that adds a page number label to page blocks
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( BlockEdit ) => {
		return applyWithSelect( ( props ) => {
			const { name, pageNumber, isReordering } = props;

			// Not a valid top level block.
			if ( ! ALLOWED_TOP_LEVEL_BLOCKS.includes( name ) || ! pageNumber ) {
				return <BlockEdit { ...props } />;
			}

			// No page numbers needed during reordering.
			if ( isReordering ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<>
					<div className="amp-story-page-number">
						{
							/* translators: %s: Page number */
							sprintf( __( 'Page %s', 'amp' ), pageNumber )
						}
					</div>
					<BlockEdit { ...props } />
				</>
			);
		} );
	},
	'withPageNumber',
);
