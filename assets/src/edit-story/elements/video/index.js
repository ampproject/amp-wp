/**
 * Internal dependencies
 */
import { PanelTypes } from '../../panels';
export { default as Display } from './display';
export { default as Save } from './save';
export { default as Preview } from './preview';

export const defaultAttributes = {
	controls: false,
	loop: false,
	autoPlay: true,
	featuredMedia: 0,
	featuredMediaSrc: '',
	videoId: 0,
};

export const hasEditMode = false;

export const panels = [
	PanelTypes.SIZE,
	PanelTypes.POSITION,
	PanelTypes.SCALE,
	PanelTypes.ROTATION_ANGLE,
	PanelTypes.VIDEO_POSTER,
];
