/**
 * Internal dependencies
 */
import { PanelTypes } from '../../panels';
export { default as Display } from './display';

export const defaultAttributes = {
	fontFamily: 'Arial',
	fontWeight: 'normal',
	fontSize: 'auto',
	fontStyle: 'normal',
	color: 'black',
	backgroundColor: 'transparent',
};

export const hasEditMode = false;

export const panels = [
	PanelTypes.TEXT,
	PanelTypes.SIZE,
	PanelTypes.POSITION,
	PanelTypes.FONT,
	PanelTypes.COLOR,
	PanelTypes.BACKGROUND_COLOR,
];
