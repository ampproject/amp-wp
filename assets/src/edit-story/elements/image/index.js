/**
 * Internal dependencies
 */
import { PanelTypes } from '../../panels';
export { default as Display } from './display';
export { default as Edit } from './edit';
export { default as Output } from './output';
export { default as Frame } from './frame';
export { default as TextContent } from './textContent';

export const defaultAttributes = {
};

export const hasEditMode = true;

export const editModeGrayout = true;

export const panels = [
	PanelTypes.SIZE,
	PanelTypes.POSITION,
	PanelTypes.SCALE,
	PanelTypes.ROTATION_ANGLE,
	PanelTypes.FULLBLEED,
];
