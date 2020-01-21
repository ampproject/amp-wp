/**
 * Internal dependencies
 */
import { PanelTypes } from '../../panels';
export { default as Display } from './display';
export { default as Save } from './save';
export { default as Create } from './create';

export const defaultAttributes = {
	controls: false,
	loop: false,
	autoPlay: true,
	featuredMedia: 0,
	featuredMediaSrc: '',
};

export const hasEditMode = false;

export const panels = [
	PanelTypes.VIDEO,
	PanelTypes.SIZE,
	PanelTypes.POSITION,
	PanelTypes.SCALE,
	PanelTypes.ROTATION_ANGLE,
];
