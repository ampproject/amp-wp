/**
 * External dependencies
 */
import uuid from 'uuid/v4';

/**
 * Internal dependencies
 */
import Text from './text';
import Image from './image';
import Square from './square';

export const createElement = ( type, props = {} ) => ( {
	type,
	id: uuid(),
	...props,
} );

export const createPage = () => createElement( 'page' );

export const elementTypes = [
	{ type: 'text', component: Text, name: 'Text', panels: Text.panels },
	{ type: 'image', component: Image, name: 'Image', panels: Image.panels },
	{ type: 'square', component: Square, name: 'Square', panels: Square.panels },
];
