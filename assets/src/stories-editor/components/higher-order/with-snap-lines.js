/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';
import { SVG } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { STORY_PAGE_INNER_HEIGHT, STORY_PAGE_INNER_WIDTH } from '../../constants';

const applyWithSelect = withSelect( ( select, { clientId } ) => {
	const {	getCurrentPage, snapLinesVisible, getSnapLines } = select( 'amp/story' );

	return {
		snapLinesVisible: snapLinesVisible(),
		snapLines: getSnapLines(),
		isActivePage: getCurrentPage() === clientId,
	};
} );

/**
 * Higher-order component that adds snap lines to page blocks
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( BlockEdit ) => {
		return applyWithSelect( ( props ) => {
			const { snapLinesVisible, snapLines, isActivePage } = props;

			if ( ! isActivePage ) {
				return <BlockEdit { ...props } />;
			}

			if ( ! snapLinesVisible || ! snapLines.length ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<>
					<BlockEdit { ...props } />
					<SVG
						viewBox={ `0 0 ${ STORY_PAGE_INNER_WIDTH } ${ STORY_PAGE_INNER_HEIGHT }` }
						style={ {
							position: 'absolute',
							top: 0,
							pointerEvents: 'none',
						} }
					>
						{ snapLines.map( ( [ start, end ], index ) => (
							<line
								key={ index }
								x1={ start[ 0 ] }
								y1={ start[ 1 ] }
								x2={ end[ 0 ] }
								y2={ end[ 1 ] }
								stroke="red"
								pointerEvents="none"
							/>
						) ) }
					</SVG>
				</>
			);
		} );
	},
	'withSnapLines'
);
