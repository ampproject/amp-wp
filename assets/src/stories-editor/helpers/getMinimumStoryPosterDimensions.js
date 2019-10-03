/**
 * Internal dependencies
 */
import { getMinimumFeaturedImageDimensions } from '../../common/helpers';

/**
 * Returns the minimum dimensions for a story poster image.
 *
 * @see https://www.ampproject.org/docs/reference/components/amp-story#poster-guidelines-(for-poster-portrait-src,-poster-landscape-src,-and-poster-square-src)
 *
 * @return {Object} Minimum dimensions including width and height.
 */
const getMinimumStoryPosterDimensions = () => {
	const posterImageWidth = 696;
	const posterImageHeight = 928;

	const expectedAspectRatio = posterImageWidth / posterImageHeight;

	const { width: featuredImageWidth } = getMinimumFeaturedImageDimensions();

	const width = Math.max( posterImageWidth, featuredImageWidth );

	// Adjust the height to make sure the aspect ratio of the poster image is preserved.
	return {
		width,
		height: ( 1 / expectedAspectRatio ) * width,
	};
};

export default getMinimumStoryPosterDimensions;
