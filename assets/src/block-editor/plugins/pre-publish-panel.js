/**
 * Internal dependencies
 */
// @todo Import from '../components' and use tree shaking in development mode to prevent warnings.
import PrePublishPanel from '../../components/pre-publish-panel';
import { getMinimumFeaturedImageDimensions } from '../../common/helpers';

export const name = 'amp-post-featured-image-pre-publish-panel';

// On clicking 'Publish,' display a notice if no featured image exists or its width is too small.
export const render = () => {
	return (
		<PrePublishPanel
			dimensions={ getMinimumFeaturedImageDimensions() }
			required={ false }
		/>
	);
};
