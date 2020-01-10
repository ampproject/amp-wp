/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { RichText } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { migrateV120 } from '../shared';

const blockAttributes = {
	value: {
		type: 'string',
		source: 'html',
		selector: 'blockquote',
		multiline: 'p',
		default: '',
	},
	citation: {
		type: 'string',
		source: 'html',
		selector: 'cite',
		default: '',
	},
	align: {
		type: 'string',
	},
};

const saveV120 = ( { attributes } ) => {
	const { align, value, citation } = attributes;

	return (
		<blockquote style={ { textAlign: align ? align : null } }>
			<RichText.Content multiline value={ value } />
			{ ! RichText.isEmpty( citation ) && <RichText.Content tagName="cite" value={ citation } /> }
		</blockquote>
	);
};

saveV120.propTypes = {
	attributes: PropTypes.shape( {
		align: PropTypes.string,
		value: PropTypes.string,
		citation: PropTypes.string,
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
