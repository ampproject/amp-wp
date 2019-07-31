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
import { RichText } from '@wordpress/block-editor';

const blockAttributes = {
	content: {
		type: 'string',
		source: 'html',
		selector: 'pre',
		default: '',
	},
};

const saveV120 = ( { attributes } ) => {
	const { content } = attributes;

	return <RichText.Content tagName="pre" value={ content } />;
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
