/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';
import { Fragment } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';
import { compose, createHigherOrderComponent } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { ALLOWED_TOP_LEVEL_BLOCKS } from '../constants';
import { withBlockName } from './';

const applyWithSelect = withSelect( ( select, props ) => {
	const {
		getBlockOrder,
		getBlockRootClientId,
	} = select( 'core/editor' );
	const {
		isReordering,
		getBlockOrder: getModifiedBlockOrder,
	} = select( 'amp/story' );

	if ( '' !== getBlockRootClientId( props.clientId ) ) {
		return {
			pageNumber: undefined,
		};
	}

	const currentIndex = isReordering() ? getModifiedBlockOrder().indexOf( props.clientId ) : getBlockOrder().indexOf( props.clientId );

	return {
		pageNumber: currentIndex + 1,
	};
} );

const wrapperWithSelect = compose(
	applyWithSelect,
	withBlockName,
);

/**
 * Add page number label to page blocks
 *
 * @param {Object} BlockListBlock BlockListBlock element.
 * @return {Function} Handler.
 */
export default createHigherOrderComponent(
	( BlockEdit ) => {
		return wrapperWithSelect( ( props ) => {
			const { blockName, pageNumber } = props;

			// Not a valid top level block.
			if ( ! ALLOWED_TOP_LEVEL_BLOCKS.includes( blockName ) || ! pageNumber ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<Fragment>
					<div className="amp-story-page-number">
						{
							/* translators: %s: Page number */
							sprintf( __( 'Page %s', 'amp' ), pageNumber )
						}
					</div>
					<BlockEdit { ...props } />
				</Fragment>
			);
		} );
	},
	'withPageNumber'
);
