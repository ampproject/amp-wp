/**
 * Internal dependencies
 */
import { PanelTypes } from '../../panels';
export { default as Display } from './display';

export const defaultAttributes = {
};

export const hasEditMode = false;

export const panels = [
	PanelTypes.SIZE,
	PanelTypes.POSITION,
];
