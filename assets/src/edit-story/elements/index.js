/**
 * External dependencies
 */
import uuid from 'uuid/v4';

/**
 * Internal dependencies
 */
<<<<<<< HEAD
import * as textElement from './text';
import * as imageElement from './image';
import * as squareElement from './square';
=======
import Text from './text';
import Image from './image';
import Square from './square';
import Video from './video';
>>>>>>> develop-stories

export const createNewElement = ( type, attributes = {} ) => {
	const element = elementTypes.find( ( el ) => el.type === type );
	const defaultAttributes = element ? element.defaultAttributes : {};
	return {
		type,
		id: uuid(),
		...defaultAttributes,
		...attributes,
	};
};

export const createPage = ( attributes ) => createNewElement( 'page', attributes );

export const elementTypes = [
	{ type: 'page', defaultAttributes: { elements: [] }, name: 'Page' },
	{ type: 'text', name: 'Text', ...textElement },
	{ type: 'image', name: 'Image', ...imageElement },
	{ type: 'square', name: 'Square', ...squareElement },
	{ type: 'video', name: 'Video', ...videoElement },
];

export const getDefinitionForType =
	( type ) => elementTypes.find( ( el ) => el.type === type );
