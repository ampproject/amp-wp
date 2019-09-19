/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { SVG } from '@wordpress/components';
import { createHigherOrderComponent } from '@wordpress/compose';
import { createContext, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { STORY_PAGE_INNER_HEIGHT, STORY_PAGE_INNER_WIDTH } from '../../constants';

const SnapContext = createContext();

const Snapping = ( { children } ) => {
	const [ snapLines, setSnapLines ] = useState( [] );
	const [ hasSnapLines, setHasSnapLines ] = useState( false );

	const showSnapLines = () => setHasSnapLines( true );
	const hideSnapLines = () => setHasSnapLines( false );
	const clearSnapLines = () => setSnapLines( [] );

	const context = {
		showSnapLines,
		hideSnapLines,
		setSnapLines,
		snapLines,
		hasSnapLines,
		clearSnapLines,
	};

	return (
		<SnapContext.Provider value={ context }>
			{ children }
			{ Boolean( hasSnapLines && snapLines.length ) && (
				<SVG
					viewBox={ `0 0 ${ STORY_PAGE_INNER_WIDTH } ${ STORY_PAGE_INNER_HEIGHT }` }
					style={ {
						position: 'absolute',
						top: 0,
						pointerEvents: 'none',
					} }
				>
					{ snapLines.map( ( [ [ x1, y1 ], [ x2, y2 ] ], index ) => (
						<line
							key={ index }
							x1={ x1 }
							y1={ y1 }
							x2={ x2 }
							y2={ y2 }
							stroke="red"
							pointerEvents="none"
						/>
					) ) }
				</SVG>
			) }
		</SnapContext.Provider>
	);
};

Snapping.propTypes = {
	children: PropTypes.object,
};

export default Snapping;

export const withSnapContext = createHigherOrderComponent(
	( WrappedComponent ) => ( props ) => (
		<SnapContext.Consumer>
			{
				( snappingProps ) => (
					<WrappedComponent { ...props } { ...snappingProps } />
				)
			}
		</SnapContext.Consumer>
	),
	'withSnapContext',
);
