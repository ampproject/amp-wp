/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { CustomVideoBlockEdit } from './';

/**
 * If this is a Core Video block, this uses an alternate edit component that enables previewing the video.
 *
 * The Core Video edit component wraps the <video> in <Disabled>.
 * This substitutes a forked component that doesn't disable the <video>, allowing it to play.
 *
 * @param {Function} InitialBlockEdit The BlockEdit component, passed from the filter.
 * @return {Function} The component, either unchaged, or an alternate Video block edit component.
 */
export default ( InitialBlockEdit ) => {
	const withCustomVideoBlockEdit = ( props ) => {
		if ( 'core/video' === props.name ) {
			return <CustomVideoBlockEdit { ...props } />;
		}

		return <InitialBlockEdit { ...props } />;
	};

	withCustomVideoBlockEdit.propTypes = {
		name: PropTypes.string,
	};

	return withCustomVideoBlockEdit;
};
