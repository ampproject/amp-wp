/**
 * Internal dependencies
 */
import { PanelTypes } from '../../panels';
export { default as Display } from './display';
export { default as Edit } from './edit';
export { default as Save } from './save';

export const defaultAttributes = {
	fontFamily: 'Arial',
	fontWeight: '400',
	fontSize: 'auto',
	fontStyle: 'normal',
	color: 'black',
	backgroundColor: 'transparent',
};

export const hasEditMode = true;

export const panels = [
	PanelTypes.TEXT,
	PanelTypes.SIZE,
	PanelTypes.POSITION,
	PanelTypes.FONT,
	PanelTypes.COLOR,
	PanelTypes.BACKGROUND_COLOR,
	PanelTypes.ROTATION_ANGLE,
];
