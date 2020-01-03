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
import { getBox } from '../../elements/shared';
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
		const scaleX = WIDTH / PAGE_WIDTH;
		const scaleY = HEIGHT / PAGE_HEIGHT;
		context.resetTransform();
		context.clearRect( 0, 0, WIDTH, HEIGHT );

		if ( ! currentPage ) {
			return null;
		}

		let promise = Promise.resolve();
		currentPage.elements.forEach( ( element ) => {
			promise = promise.then( () => {
				const { type } = element;
				const { Render } = getDefinitionForType( type );
				const { x, y, width, height, rotationAngle } = getBox( element );

				context.resetTransform();
				context.scale( scaleX, scaleY );
				if ( rotationAngle !== 0 ) {
					// Rotate around center origin.
					context.translate( x + ( width / 2 ), y + ( height / 2 ) );
					context.rotate( rotationAngle * Math.PI / 180 );
					context.translate( -width / 2, -height / 2 );
				} else {
					context.translate( x, y );
				}

				if ( Render ) {
					return Render( context, { ...element, x: 0, y: 0, width, height, rotationAngle } );
				}

				// Show that a renderer is absent for debugging for now.
				context.lineWidth = 0.5;
				context.strokeStyle = 'red';
				context.strokeRect( 0, 0, width, height );
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
