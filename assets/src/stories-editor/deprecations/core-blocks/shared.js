/**
 * External dependencies
 */
import { omit } from 'lodash';

/**
 * Migration logic for migrating blocks from version 1.2.0.
 *
 * @param {object} attributes Attributes.
 */
export const migrateV120 = ( attributes ) => {
	return {
		...omit( attributes, [ 'deprecated', 'anchor' ] ),
	};
};
