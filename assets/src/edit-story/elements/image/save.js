/**
 * Internal dependencies
 */
import StoryPropTypes from '../../types';

/**
 * Returns AMP HTML for saving into post content for displaying in the FE.
 */
function ImageSave( { element: { src } } ) {
	const props = {
		layout: 'fill',
		src,
	};
	return ( <amp-img { ...props } /> );
}

ImageSave.propTypes = {
	element: StoryPropTypes.elements.image.isRequired,
};

export default ImageSave;
