/**
 * External dependencies
 */
import uuid from 'uuid/v4';
/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
/**
 * Internal dependencies
 */
import * as textElement from './text';
import * as imageElement from './image';
import * as squareElement from './square';
import * as videoElement from './video';

export const createNewElement = ( type, attributes = {} ) => {
	const element = elementTypes.find( ( el ) => el.type === type );
	const defaultAttributes = element ? element.defaultAttributes : {};

	return {
		...defaultAttributes,
		...attributes,
		type,
		id: uuid(),
	};
};

export const createPage = ( attributes ) => createNewElement( 'page', attributes );

export const elementTypes = [
	{ type: 'page', defaultAttributes: { elements: [] }, name: __( 'Page', 'amp' ) },
	{ type: 'text', name: __( 'Text', 'amp' ), ...textElement },
	{ type: 'image', name: __( 'Image', 'amp' ), ...imageElement },
	{ type: 'square', name: __( 'Square', 'amp' ), ...squareElement },
	{ type: 'video', name: __( 'Video', 'amp' ), ...videoElement },
];

export const getDefinitionForType =
	( type ) => elementTypes.find( ( el ) => el.type === type );
