/**
 * WordPress dependencies
 */
import { withDispatch, withSelect } from '@wordpress/data';
import { compose, createHigherOrderComponent } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { STORY_PAGE_INNER_HEIGHT, STORY_PAGE_INNER_WIDTH } from '../../constants';
import { getBlockInnerElement } from '../../helpers';

/**
 * Higher-order component that returns snap targets for the current block.
 *
 * Snap targets are divided into horizontal and vertical ones, and are based on the following data:
 *
 * - The page's width / height constraints and center.
 * - The block's position in relation to its siblings.
 *
 * @todo Update horizontalSnaps() and verticalSnaps() to return a map of snap targets -> snap lines in order to allow showing multiple snap lines for a single snapping point that can differ from the actual target.
 */
const applyWithSelect = withSelect( ( select, { clientId } ) => {
	const {
		getBlocksByClientId,
		getBlockRootClientId,
		getBlock,
		getBlockOrder,
	} = select( 'core/block-editor' );
	const { getCurrentPage, getSnapLines } = select( 'amp/story' );

	const parentBlock = getBlockRootClientId( clientId );

	const defaultData = {
		horizontalSnaps: [],
		verticalSnaps: [],
		snapLines: [],
		parentBlockOffsetTop: 0,
		parentBlockOffsetLeft: 0,
	};

	if ( getCurrentPage() !== parentBlock ) {
		return defaultData;
	}

	const parentBlockElement = getBlockInnerElement( getBlock( parentBlock ) );

	if ( ! parentBlockElement ) {
		return defaultData;
	}

	const { left: parentBlockOffsetLeft, top: parentBlockOffsetTop } = parentBlockElement.getBoundingClientRect();

	const siblings = getBlocksByClientId( getBlockOrder( parentBlock ) )
		.filter( ( { clientId: blockId } ) => blockId !== clientId );

	return {
		horizontalSnaps: () => {
			const pageSnaps = [
				0,
				STORY_PAGE_INNER_WIDTH / 2,
				STORY_PAGE_INNER_WIDTH,
			];

			const blockSnaps = siblings
				.map( ( block ) => {
					const blockElement = getBlockInnerElement( block );

					if ( ! blockElement ) {
						return [];
					}

					const { left, right } = blockElement.getBoundingClientRect();
					return [ Math.round( left - parentBlockOffsetLeft ), Math.round( right - parentBlockOffsetLeft ) ];
				} )
				.reduce( ( result, snaps ) => {
					for ( const snap of snaps ) {
						if ( snap < 0 || snap > STORY_PAGE_INNER_WIDTH || result.includes( snap ) || pageSnaps.includes( snap ) ) {
							continue;
						}

						result.push( snap );
					}

					return result;
				}, [] );

			return [ ...pageSnaps, ...blockSnaps ];
		},
		verticalSnaps: () => {
			const pageSnaps = [
				0,
				STORY_PAGE_INNER_HEIGHT / 2,
				STORY_PAGE_INNER_HEIGHT,
			];

			const blockSnaps = siblings
				.map( ( block ) => {
					const blockElement = getBlockInnerElement( block );

					if ( ! blockElement ) {
						return [];
					}

					const { top, bottom } = blockElement.getBoundingClientRect();
					return [ Math.round( top - parentBlockOffsetTop ), Math.round( bottom - parentBlockOffsetTop ) ];
				} )
				.reduce( ( result, snaps ) => {
					for ( const snap of snaps ) {
						if ( snap < 0 || snap > STORY_PAGE_INNER_HEIGHT || result.includes( snap ) || pageSnaps.includes( snap ) ) {
							continue;
						}

						result.push( snap );
					}

					return result;
				}, [] );

			return [ ...pageSnaps, ...blockSnaps ];
		},
		snapLines: getSnapLines(),
		parentBlockOffsetTop,
		parentBlockOffsetLeft,
	};
} );

const applyWithDispatch = withDispatch( ( dispatch ) => {
	const {
		showSnapLines,
		hideSnapLines,
		setSnapLines,
		clearSnapLines,
	} = dispatch( 'amp/story' );

	return {
		showSnapLines,
		hideSnapLines,
		setSnapLines,
		clearSnapLines,
	};
} );

const enhance = compose(
	applyWithSelect,
	applyWithDispatch,
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
