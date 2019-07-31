/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const blockAttributes = {
	content: {
		type: 'string',
		source: 'text',
		selector: 'code',
	},
};

const saveV120 = ( { attributes } ) => {
	return <pre><code>{ attributes.content }</code></pre>;
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

		migrate: ( attributes ) => {
			return {
				...omit( attributes, [ 'deprecated', 'anchor' ] ),
			};
		},
	},
];

export default deprecated;
