/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import {
	InnerBlocks,
	InspectorControls,
} from '@wordpress/block-editor';

const PageEdit = ( {
	attributes,
} ) => {
	const {
		mediaUrl,
	} = attributes;

	return (
		<>
			<InspectorControls>
				<h1>Editing { mediaUrl }</h1>
			</InspectorControls>
			<div>
				<InnerBlocks allowedBlocks={ [] } />
			</div>
		</>
	);
};

PageEdit.propTypes = {
	attributes: PropTypes.shape( {
		anchor: PropTypes.string,
		backgroundColors: PropTypes.string,
		mediaId: PropTypes.number,
		mediaType: PropTypes.string,
		mediaUrl: PropTypes.string,
		focalPoint: PropTypes.shape( {
			x: PropTypes.number.isRequired,
			y: PropTypes.number.isRequired,
		} ),
		overlayOpacity: PropTypes.number,
		poster: PropTypes.string,
		autoAdvanceAfter: PropTypes.string,
		autoAdvanceAfterDuration: PropTypes.number,
		mediaAlt: PropTypes.string,
	} ).isRequired,
};

export default PageEdit;
