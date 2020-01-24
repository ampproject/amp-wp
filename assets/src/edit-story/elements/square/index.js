/**
 * Internal dependencies
 */
import { PanelTypes } from '../../panels';
export { default as Display } from './display';
export { default as Preview } from './preview';
export { default as Save } from './save';

export const defaultAttributes = {
	backgroundColor: '#ffffff',
};

export const hasEditMode = false;

export const panels = [
	PanelTypes.SIZE,
	PanelTypes.POSITION,
	PanelTypes.BACKGROUND_COLOR,
	PanelTypes.ROTATION_ANGLE,
];
