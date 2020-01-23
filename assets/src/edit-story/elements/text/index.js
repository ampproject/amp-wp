/**
 * Internal dependencies
 */
import { PanelTypes } from '../../panels';
export { default as Display } from './display';
export { default as Edit } from './edit';
export { default as Frame } from './frame';
export { default as Output } from './output';
export { default as TextContent } from './textContent';

export const defaultAttributes = {
	fontFamily: 'Arial',
	fontFallback: [ 'Helvetica Neue', 'Helvetica', 'sans-serif' ],
	fontWeight: 400,
	fontSize: 14,
	fontStyle: 'normal',
	color: '#000000',
	backgroundColor: '#ffffff',
	letterSpacing: 'normal',
	lineHeight: 1.3,
	textAlign: 'initial',
};

export const hasEditMode = true;

export const panels = [
	PanelTypes.TEXT,
	PanelTypes.SIZE,
	PanelTypes.POSITION,
	PanelTypes.FONT,
	PanelTypes.STYLE,
	PanelTypes.COLOR,
	PanelTypes.BACKGROUND_COLOR,
	PanelTypes.ROTATION_ANGLE,
];
