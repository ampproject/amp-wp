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

function Library() {
	const {
		state: { tab },
		data: { tabs: { MEDIA, TEXT, SHAPES, LINKS } },
	} = useLibrary();
	const {
		actions: { addElement },
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
		addElement( { element } );
	};
	return <ContentLibrary onInsert={ handleInsert } />;
}

export default Library;
