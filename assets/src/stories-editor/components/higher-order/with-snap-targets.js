/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';
import { compose, createHigherOrderComponent } from '@wordpress/compose';
import isShallowEqual from '@wordpress/is-shallow-equal';

/**
 * Internal dependencies
 */
import { STORY_PAGE_INNER_HEIGHT, STORY_PAGE_INNER_WIDTH } from '../../constants';
import { getBlockInnerElement, getRelativeElementPosition } from '../../helpers';
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
		horizontalSnaps: [],
		verticalSnaps: [],
		parentBlockElement: null,
	};

	if ( getCurrentPage() !== parentBlock ) {
		return defaultData;
	}

	const parentBlockElement = getBlockInnerElement( getBlock( parentBlock ) );

	if ( ! parentBlockElement ) {
		return defaultData;
	}

	const siblings = getBlocksByClientId( getBlockOrder( parentBlock ) )
		.filter( ( { clientId: blockId } ) => blockId !== clientId )
		.filter( getBlockInnerElement );

	const getVerticalLine = ( offsetX, start = 0, end = STORY_PAGE_INNER_HEIGHT ) => [ [ offsetX, start ], [ offsetX, end ] ];
	const getHorizontalLine = ( offsetY, start = 0, end = STORY_PAGE_INNER_WIDTH ) => [ [ start, offsetY ], [ end, offsetY ] ];

	// Setter used for the proxied snap target objects.
	// Prevents duplicates and sets upper and lower boundaries.
	const proxySet = ( obj, prop, value ) => {
		prop = Math.round( prop );

		if ( prop < 0 || prop > STORY_PAGE_INNER_WIDTH ) {
			return true;
		}

		const hasSnapLine = ( item ) => obj[ prop ].find( ( snapLine ) => isShallowEqual( item[ 0 ], snapLine[ 0 ] ) && isShallowEqual( item[ 1 ], snapLine[ 1 ] ) );

		if ( obj.hasOwnProperty( prop ) && ! hasSnapLine( value ) ) {
			obj[ prop ].push( value );
		} else {
			obj[ prop ] = [ value ];
		}

		obj[ prop ] = obj[ prop ].sort();

		return true;
	};

	return {
		/**
		 * Horizontal snap function.
		 *
		 * @param {number} targetTop The top position of the currently dragged/resized block.
		 * @param {number} targetBottom The bottom position of the currently dragged/resized block.
		 * @return {Object.<number,Array.<Array.<number, number>>>} Dictionary with horizontal snap targets.
		 */
		horizontalSnaps: ( targetTop, targetBottom ) => {
			const snaps = new Proxy( {
				// Left page border.
				0: [ getVerticalLine( 0 ) ],
				// Center of the page.
				[ STORY_PAGE_INNER_WIDTH / 2 ]: [ getVerticalLine( STORY_PAGE_INNER_WIDTH / 2 ) ],
				// Right page border.
				[ STORY_PAGE_INNER_WIDTH ]: [ getVerticalLine( STORY_PAGE_INNER_WIDTH ) ],
			},
			{
				set: proxySet,
			} );

			for ( const block of siblings ) {
				const blockElement = getBlockInnerElement( block );
				const { top, right, bottom, left } = getRelativeElementPosition( blockElement, parentBlockElement );
				const center = left + ( ( right - left ) / 2 );

				const start = targetTop < top ? targetTop : top;
				const end = targetBottom > bottom ? targetBottom : bottom;

				snaps[ left ] = getVerticalLine( left, start, end );
				snaps[ right ] = getVerticalLine( right, start, end );
				snaps[ center ] = getVerticalLine( center, start, end );
			}

			return snaps;
		},

		/**
		 * Vertical snap function.
		 *
		 * @param {number} targetLeft The left position of the currently dragged/resized block.
		 * @param {number} targetRight The right position of the currently dragged/resized block.
		 * @return {Object.<number,Array.<Array.<number, number>>>} Dictionary with vertical snap targets.
		 */
		verticalSnaps: ( targetLeft, targetRight ) => {
			const snaps = new Proxy( {
				// Top page border.
				0: [ getHorizontalLine( 0 ) ],
				// Center of the page.
				[ STORY_PAGE_INNER_HEIGHT / 2 ]: [ getHorizontalLine( STORY_PAGE_INNER_HEIGHT / 2 ) ],
				// Bottom page border.
				[ STORY_PAGE_INNER_HEIGHT ]: [ getHorizontalLine( STORY_PAGE_INNER_HEIGHT ) ],
			},
			{
				set: proxySet,
			} );

			for ( const block of siblings ) {
				const blockElement = getBlockInnerElement( block );
				const { top, right, bottom, left } = getRelativeElementPosition( blockElement, parentBlockElement );
				const center = top + ( ( bottom - top ) / 2 );

				const start = targetLeft < left ? targetLeft : left;
				const end = targetRight > right ? targetRight : right;

				snaps[ top ] = getHorizontalLine( top, start, end );
				snaps[ bottom ] = getHorizontalLine( bottom, start, end );
				snaps[ center ] = getHorizontalLine( center, start, end );
			}

			return snaps;
		},
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
