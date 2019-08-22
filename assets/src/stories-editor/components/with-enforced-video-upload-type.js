/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Gets a wrapped version of the MediaPlaceholder that ensures that the Video block only accepts uploads of 'video/mp4'.
 *
 * This only modifies the Video block edit component's 'Upload' button.
 * There is separate logic to block uploading incorrect files to the Media Library.
 *
 * @param {Function} InitialMediaPlaceholder The component passed from the filter.
 * @return {Function} A MediaPlaceholder component that enforces the upload type for the Video block.
 */
export default ( InitialMediaPlaceholder ) => {
	const withEnforcedVideoUploadType = ( props ) => {
		const { accept, className } = props;
		let newProps = { ...props };

		if ( 'wp-block-video' === className && 'video/*' === accept ) {
			newProps = { ...props, accept: 'video/mp4' };
		}

		return <InitialMediaPlaceholder { ...newProps } />;
	};

	withEnforcedVideoUploadType.propTypes = {
		accept: PropTypes.string,
		className: PropTypes.string,
	};

	return withEnforcedVideoUploadType;
};
