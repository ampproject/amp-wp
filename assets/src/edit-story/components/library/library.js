/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { createNewElement } from '../../elements';
import { useStory } from '../../app';
import useLibrary from './useLibrary';
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
	const {
		state: { tab },
		data: { tabs: { MEDIA, TEXT, SHAPES, LINKS } },
	} = useLibrary();
	const {
		actions: { appendElementToCurrentPage },
	} = useStory();
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
