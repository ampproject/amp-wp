/**
 * Internal dependencies
 */
import { PrePublishPanel } from '../../components';
import { getMinimumStoryPosterDimensions } from '../helpers';

export const name = 'amp-story-pre-publish-panel';

export const render = () => {
	return (
		<PrePublishPanel
			dimensions={ getMinimumStoryPosterDimensions() }
			required={ true }
		/>
	);
};
