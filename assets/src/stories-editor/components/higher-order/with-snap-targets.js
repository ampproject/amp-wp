/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';
import { compose, createHigherOrderComponent } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { getBlockInnerElement, getRelativeElementPosition } from '../../helpers';
import { getHorizontalTargets, getVerticalTargets } from '../../helpers/snapping';
import { withSnapContext } from '../contexts/snapping';

/**
 * Higher-order component that returns snap targets for the current block.
 *
 * Snap targets are divided into horizontal and vertical ones, and are based on the following data:
 *
 * - The page's width / height constraints and center.
 * - The block's position in relation to its siblings.
 *
 * The snap targets are stored in a map with the snap target as the key and the snap lines to be shown as the value.
 */
const applyWithSelect = withSelect( ( select, { clientId } ) => {
	const {
		getBlocksByClientId,
		getBlockRootClientId,
		getBlock,
		getBlockOrder,
	} = select( 'core/block-editor' );
	const { getCurrentPage } = select( 'amp/story' );

	const parentBlock = getBlockRootClientId( clientId );

	const defaultData = {
		horizontalTargets: () => [],
		verticalTargets: () => [],
		parentBlockElement: null,
	};

	if ( getCurrentPage() !== parentBlock ) {
		return defaultData;
	}

	const parentBlockElement = getBlockInnerElement( getBlock( parentBlock ) );

	if ( ! parentBlockElement ) {
		return defaultData;
	}

	const siblingPositions = getBlocksByClientId( getBlockOrder( parentBlock ) )
		.filter( ( { clientId: blockId } ) => blockId !== clientId )
		.map( getBlockInnerElement )
		.filter( Boolean )
		.map( ( el ) => getRelativeElementPosition( el, parentBlockElement ) );

	const horizontalTargets = getHorizontalTargets( siblingPositions );
	const verticalTargets = getVerticalTargets( siblingPositions );

	return {
		horizontalTargets,
		verticalTargets,
		parentBlockElement,
	};
} );

const enhance = compose(
	applyWithSelect,
	withSnapContext,
);

/**
 * Higher-order component that provides snap targets.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	enhance,
	'withSnapTargets'
);
