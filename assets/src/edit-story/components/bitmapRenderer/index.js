/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { PAGE_WIDTH, PAGE_HEIGHT } from '../../constants';
import { useStory } from '../../app';
import { getDefinitionForType } from '../../elements';
import useEffectSinglePath from '../../utils/useEffectSinglePath';

const WIDTH = 200;
const HEIGHT = Math.round( WIDTH * 16 / 9 );

const Container = styled.div`
  background: #fff;
  width: ${ WIDTH + 2 }px;
  height: ${ HEIGHT + 2 }px;
  border: 1px solid lightgray;
`;

function BitmapRenderer( {} ) {
	const canvasRef = useRef( null );
	const { state: { currentPage } } = useStory();

	useEffectSinglePath( () => {
		const canvas = canvasRef.current;
		const context = canvas.getContext( '2d' );
		context.resetTransform();
		context.scale( WIDTH / PAGE_WIDTH, HEIGHT / PAGE_HEIGHT );
		context.clearRect( 0, 0, PAGE_WIDTH, PAGE_HEIGHT );

		if ( ! currentPage ) {
			return null;
		}

		let promise = Promise.resolve();
		currentPage.elements.forEach( ( element ) => {
			promise = promise.then( () => {
				const { type, x, y, width, height } = element;
				const { Render } = getDefinitionForType( type );
				if ( Render ) {
					return Render( context, element );
				}

				// Show that a renderer is absent for debugging for now.
				context.lineWidth = 0.5;
				context.strokeStyle = 'red';
				context.strokeRect( x, y, width, height );
				return null;
			} );
		} );

		return promise;
	} );

	return (
		<Container>
			<canvas ref={ canvasRef } width={ WIDTH } height={ HEIGHT } />
		</Container>
	);
}

export default BitmapRenderer;
