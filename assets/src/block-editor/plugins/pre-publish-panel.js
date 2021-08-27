/**
 * Internal dependencies
 */
import { PrePublishPanel } from '../../common/components';

export const name = 'amp-post-featured-image-pre-publish-panel';

// Add the featured image selection component as a pre-publish check.
export const render = () => {
	return (
		<PrePublishPanel />
	);
};
