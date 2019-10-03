/**
 * External dependencies
 */
import uuid from 'uuid/v4';

/**
 * Returns a unique ID that is guaranteed to not start with a number.
 *
 * Useful for using in HTML attributes.
 *
 * @return {string} Unique ID.
 */
const getUniqueId = () => {
	return uuid().replace( /^\d/, 'a' );
};

export default getUniqueId;
