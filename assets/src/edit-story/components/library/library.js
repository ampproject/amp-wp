/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { createNewElement } from '../../elements';
import { useStory } from '../../app';
import Context from './context';
import MediaLibrary from './mediaLibrary';
import TextLibrary from './textLibrary';
import ShapeLibrary from './shapeLibrary';
import LinkLibrary from './linkLibrary';

const Background = styled.aside`
	background-color: ${ ( { theme } ) => theme.colors.bg.v4 };
	height: 100%;
	padding: 1em;
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
`;

function Library() {
	const { tab, tabs: { MEDIA, TEXT, SHAPES, LINKS } } = useContext( Context );
	const { actions: { appendElementToCurrentPage } } = useStory();
	const ContentLibrary = ( {
		[ MEDIA ]: MediaLibrary,
		[ TEXT ]: TextLibrary,
		[ SHAPES ]: ShapeLibrary,
		[ LINKS ]: LinkLibrary,
	} )[ tab ];
	const handleInsert = ( type, props ) => {
		const element = createNewElement( type, {
			...props,
			x: Math.round( 80 * Math.random() ),
			y: Math.round( 70 * Math.random() ),
		} );
		appendElementToCurrentPage( element );
	};
	return (
		<Background>
			<ContentLibrary onInsert={ handleInsert } />
		</Background>
	);
}

export default Library;
