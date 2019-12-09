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
import Video from './video';

export const createNewElement = ( type, props = {} ) => {
	const element = elementTypes.find( ( el ) => el.type === type );
	const defaultProps = element ? element.defaultProps : {};
	return {
		type,
		id: uuid(),
		...defaultProps,
		...props,
	};
};

export const createPage = ( props ) => createNewElement(
	'page',
	{
		elements: [],
		...props,
	},
);

export const elementTypes = [
	{ type: 'page', defaultProps: { elements: [] }, name: 'Page' },
	{ type: 'text', defaultProps: Text.defaultProps, component: Text, name: 'Text', panels: Text.panels },
	{ type: 'image', defaultProps: Image.defaultProps, component: Image, name: 'Image', panels: Image.panels },
	{ type: 'square', defaultProps: Square.defaultProps, component: Square, name: 'Square', panels: Square.panels },
	{ type: 'video', defaultProps: Video.defaultProps, component: Video, name: 'Video', panels: Video.panels },
];

export const getComponentForType =
	( type ) => elementTypes.find( ( el ) => el.type === type ).component;

export {
	Text,
	Image,
	Square,
	Video,
};
