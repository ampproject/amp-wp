/**
 * External dependencies
 */
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { cloneElement } from '@wordpress/element';

/**
 * Helper to add an `aria-label` to video elements when saved.
 *
 * This helper is designed as a filter for `blocks.getSaveElement`.
 *
 * @param {ReactElement}  element     Previously generated React element
 * @param {Object}        type        Block type definition.
 * @param {string}        type.name   Name of block type
 * @param {Object}        attributes  Block attributes.
 *
 * @return {ReactElement}  New React element
 */
const addVideoAriaLabel = ( element, { name }, attributes ) => {
	// this filter only applies to core video objects (which always has children) where an aria label has been set
	if ( name !== 'core/video' || ! element.props.children || ! attributes.ampAriaLabel ) {
		return element;
	}

	/* `element` will be a react structure like:

	<figure>
		<amp-video|video>
			Fallback content
		</amp-video|video>
		[<figcaption>Caption</figcaption>]
	</figure>

	The video element can be either an `<amp-video>`` or a regular `<video>`.

	`<figcaption>` is not necessarily present.

	We need to hook into this element and add an `aria-label` on the `<amp-video|video>` element.
	*/

	const isFigure = element.type === 'figure';
	const childNodes = Array.isArray( element.props.children ) ? element.props.children : [ element.props.children ];
	const videoTypes = [ 'amp-video', 'video' ];
	const isFirstChildVideoType = videoTypes.includes( childNodes[ 0 ].type );
	if ( ! isFigure || ! isFirstChildVideoType ) {
		return element;
	}

	const figure = element;
	const [ video, ...rest ] = childNodes;

	const newVideo = cloneElement(
		video,
		{ 'aria-label': attributes.ampAriaLabel },
		video.props.children,
	);

	return cloneElement(
		figure,
		{},
		newVideo,
		...rest
	);
};

export default addVideoAriaLabel;
