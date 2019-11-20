/**
 * External dependencies
 */
import uuid from 'uuid/v4';

/**
 * Internal dependencies
 */
import * as textElement from './text';
import * as imageElement from './image';
import * as squareElement from './square';

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
];

export const getComponentForType =
	( type ) => elementTypes.find( ( el ) => el.type === type ).Display;
