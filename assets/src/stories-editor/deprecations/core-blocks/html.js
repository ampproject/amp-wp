/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { migrateV120 } from '../shared';

/**
 * WordPress dependencies
 */
import { RawHTML } from '@wordpress/element';

const blockAttributes = {
	content: {
		type: 'string',
		source: 'html',
	},
};

const saveV120 = ( { attributes } ) => {
	return <RawHTML>{ attributes.content }</RawHTML>;
};

saveV120.propTypes = {
	attributes: PropTypes.shape( {
		content: PropTypes.string,
	} ).isRequired,
};

const deprecated = [
	{
		attributes: {
			...blockAttributes,
			deprecated: {
				default: '1.2.0',
			},
		},
		save: saveV120,
		migrate: migrateV120,
	},
];

export default deprecated;
