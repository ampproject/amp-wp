/**
 * External dependencies
 */
import { omit } from 'lodash';

/**
 * Migration logic for migrating blocks from version 1.2.0.
 *
 * @param {Object} attributes Attributes.
 * @return {Object} Migrated attributes.
 */
export const migrateV120 = ( attributes ) => {
	return {
		...omit( attributes, [ 'anchor', 'ampAnimationAfter' ] ),
		deprecated: 'migrated', // This is needed for detecting migrated blocks in the editor.
	};
};
