/**
 * Internal dependencies
 */
import { PanelTypes } from '../../panels';
export { default as Display } from './display';
export { default as Edit } from './edit';

export const defaultAttributes = {
	controls: false,
	loop: false,
	autoPlay: true,
	videoCaption: '',
	ampAriaLabel: '',
};

export const hasEditMode = true;

export const panels = [
	PanelTypes.MEDIA,
	PanelTypes.VIDEO,
	PanelTypes.SIZE,
	PanelTypes.POSITION,
	PanelTypes.SCALE,
	PanelTypes.ROTATION_ANGLE,
];
