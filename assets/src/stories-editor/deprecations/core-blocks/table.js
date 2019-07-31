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
import { RichText, getColorClassName } from '@wordpress/block-editor';

const blockAttributes = {
	hasFixedLayout: {
		type: 'boolean',
		default: false,
	},
	backgroundColor: {
		type: 'string',
	},
	head: {
		type: 'array',
		default: [],
		source: 'query',
		selector: 'thead tr',
		query: {
			cells: {
				type: 'array',
				default: [],
				source: 'query',
				selector: 'td,th',
				query: {
					content: {
						type: 'string',
						source: 'html',
					},
					tag: {
						type: 'string',
						default: 'td',
						source: 'tag',
					},
					scope: {
						type: 'string',
						source: 'attribute',
						attribute: 'scope',
					},
				},
			},
		},
	},
	body: {
		type: 'array',
		default: [],
		source: 'query',
		selector: 'tbody tr',
		query: {
			cells: {
				type: 'array',
				default: [],
				source: 'query',
				selector: 'td,th',
				query: {
					content: {
						type: 'string',
						source: 'html',
					},
					tag: {
						type: 'string',
						default: 'td',
						source: 'tag',
					},
					scope: {
						type: 'string',
						source: 'attribute',
						attribute: 'scope',
					},
				},
			},
		},
	},
	foot: {
		type: 'array',
		default: [],
		source: 'query',
		selector: 'tfoot tr',
		query: {
			cells: {
				type: 'array',
				default: [],
				source: 'query',
				selector: 'td,th',
				query: {
					content: {
						type: 'string',
						source: 'html',
					},
					tag: {
						type: 'string',
						default: 'td',
						source: 'tag',
					},
					scope: {
						type: 'string',
						source: 'attribute',
						attribute: 'scope',
					},
				},
			},
		},
	},
};

const saveV120 = ( { attributes } ) => {
	const {
		hasFixedLayout,
		head,
		body,
		foot,
		backgroundColor,
	} = attributes;
	const isEmpty = ! head.length && ! body.length && ! foot.length;

	if ( isEmpty ) {
		return null;
	}

	const backgroundClass = getColorClassName( 'background-color', backgroundColor );

	const classes = classnames( backgroundClass, {
		'has-fixed-layout': hasFixedLayout,
		'has-background': Boolean( backgroundClass ),
	} );

	const Section = ( { type, rows } ) => {
		if ( ! rows.length ) {
			return null;
		}

		const Tag = `t${ type }`;

		return (
			<Tag>
				{ rows.map( ( { cells }, rowIndex ) => (
					<tr key={ rowIndex }>
						{ cells.map( ( { content, tag, scope }, cellIndex ) =>
							<RichText.Content
								tagName={ tag }
								value={ content }
								key={ cellIndex }
								scope={ tag === 'th' ? scope : undefined }
							/>
						) }
					</tr>
				) ) }
			</Tag>
		);
	};

	Section.propTypes = {
		rows: PropTypes.object,
		type: PropTypes.string,
	};

	return (
		<figure>
			<table className={ classes }>
				<Section type="head" rows={ head } />
				<Section type="body" rows={ body } />
				<Section type="foot" rows={ foot } />
			</table>
		</figure>
	);
};

saveV120.propTypes = {
	attributes: PropTypes.shape( {
		hasFixedLayout: PropTypes.bool,
		head: PropTypes.array,
		body: PropTypes.array,
		foot: PropTypes.array,
		backgroundColor: PropTypes.string,
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
