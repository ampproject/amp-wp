/**
 * Helper to convert snake_case meta keys to key names used in the amp-story-page attributes.
 *
 * @param {Object} meta Meta object to be converted to an object with attributes key names.
 *
 * @return {Object} Processed object.
 */
const metaToAttributeNames = ( meta ) => {
	return {
		autoAdvanceAfter: meta.amp_story_auto_advance_after,
		autoAdvanceAfterDuration: meta.amp_story_auto_advance_after_duration,
	};
};

export default metaToAttributeNames;
