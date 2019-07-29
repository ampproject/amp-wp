/**
 * Internal dependencies
 */
import { withMetaBlockSave } from '../../components';

/**
 * External dependencies
 */
import { omit } from 'lodash';

const blockAttributes = {
	align: {
		type: 'string',
	},
};

const deprecated = [
	{
		attributes: {
			...blockAttributes,
			deprecated: {
				default: '1.2.0',
			},
		},
		save: withMetaBlockSave( { tagName: 'div' } ),
		migrate: ( attributes ) => {
			return {
				...omit( attributes, [ 'deprecated', 'anchor' ] ),
			};
		},
	},
];

export default deprecated;
