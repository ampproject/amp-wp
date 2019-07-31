/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import { migrateV120 } from '../shared';

/**
 * WordPress dependencies
 */
import { RichText } from '@wordpress/block-editor';

const blockAttributes = {
	url: {
		type: 'string',
	},
	caption: {
		type: 'string',
		source: 'html',
		selector: 'figcaption',
	},
	type: {
		type: 'string',
	},
	providerNameSlug: {
		type: 'string',
	},
	allowResponsive: {
		type: 'boolean',
		default: true,
	},
};

const saveV120 = ( { attributes } ) => {
	const { url, caption, type, providerNameSlug } = attributes;

	if ( ! url ) {
		return null;
	}

	const embedClassName = classnames( 'wp-block-embed', {
		[ `is-type-${ type }` ]: type,
		[ `is-provider-${ providerNameSlug }` ]: providerNameSlug,
	} );

	return (
		<figure className={ embedClassName }>
			<div className="wp-block-embed__wrapper">
				{ `\n${ url }\n` /* URL needs to be on its own line. */ }
			</div>
			{ ! RichText.isEmpty( caption ) && <RichText.Content tagName="figcaption" value={ caption } /> }
		</figure>
	);
};

saveV120.propTypes = {
	attributes: PropTypes.shape( {
		url: PropTypes.string,
		caption: PropTypes.string,
		type: PropTypes.string,
		providerNameSlug: PropTypes.string,
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
