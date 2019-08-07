/**
 * External dependencies
 */
import classnames from 'classnames';
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
	align: {
		type: 'string',
	},
	url: {
		type: 'string',
		source: 'attribute',
		selector: 'img',
		attribute: 'src',
	},
	alt: {
		type: 'string',
		source: 'attribute',
		selector: 'img',
		attribute: 'alt',
		default: '',
	},
	caption: {
		type: 'string',
		source: 'html',
		selector: 'figcaption',
	},
	href: {
		type: 'string',
		source: 'attribute',
		selector: 'figure > a',
		attribute: 'href',
	},
	rel: {
		type: 'string',
		source: 'attribute',
		selector: 'figure > a',
		attribute: 'rel',
	},
	linkClass: {
		type: 'string',
		source: 'attribute',
		selector: 'figure > a',
		attribute: 'class',
	},
	id: {
		type: 'number',
	},
	width: {
		type: 'number',
	},
	height: {
		type: 'number',
	},
	linkDestination: {
		type: 'string',
		default: 'none',
	},
	linkTarget: {
		type: 'string',
		source: 'attribute',
		selector: 'figure > a',
		attribute: 'target',
	},
	rotationAngle: {
		type: 'number',
		default: 0,
	},
	ampAnimationType: {
		source: 'attribute',
		selector: '.amp-story-block-wrapper',
		attribute: 'animate-in',
	},
	ampAnimationDelay: {
		source: 'attribute',
		selector: '.amp-story-block-wrapper',
		attribute: 'animate-in-delay',
		default: 0,
	},
	ampAnimationDuration: {
		source: 'attribute',
		selector: '.amp-story-block-wrapper',
		attribute: 'animate-in-duration',
		default: 0,
	},
	ampAnimationAfter: {
		source: 'attribute',
		selector: '.amp-story-block-wrapper',
		attribute: 'animate-in-after',
	},
	anchor: {
		type: 'string',
		source: 'attribute',
		attribute: 'id',
		selector: 'amp-story-grid-layer .amp-story-block-wrapper, amp-story-cta-layer',
	},
	positionTop: {
		default: 0,
		type: 'number',
	},
	positionLeft: {
		default: 5,
		type: 'number',
	},
	ampShowImageCaption: {
		type: 'boolean',
		default: false,
	},
	sizeSlug: {
		type: 'string',
	},
};

const saveV120 = ( { attributes } ) => {
	const {
		url,
		alt,
		caption,
		align,
		href,
		rel,
		linkClass,
		width,
		height,
		id,
		linkTarget,
		sizeSlug,
	} = attributes;

	const classes = classnames( {
		[ `align${ align }` ]: align,
		[ `size-${ sizeSlug }` ]: sizeSlug,
		'is-resized': width || height,
	} );

	const image = (
		<img
			src={ url }
			alt={ alt }
			className={ id ? `wp-image-${ id }` : null }
			width={ width }
			height={ height }
		/>
	);

	const figure = (
		<>
			{ href ? (
				<a
					className={ linkClass }
					href={ href }
					target={ linkTarget }
					rel={ rel }
				>
					{ image }
				</a>
			) : image }
			{ ! RichText.isEmpty( caption ) && <RichText.Content tagName="figcaption" value={ caption } /> }
		</>
	);

	if ( 'left' === align || 'right' === align || 'center' === align ) {
		return (
			<div>
				<figure className={ classes }>
					{ figure }
				</figure>
			</div>
		);
	}

	return (
		<figure className={ classes }>
			{ figure }
		</figure>
	);
};

saveV120.propTypes = {
	attributes: PropTypes.shape( {
		text: PropTypes.string,
		url: PropTypes.string,
		alt: PropTypes.string,
		caption: PropTypes.bool,
		align: PropTypes.string,
		href: PropTypes.string,
		rel: PropTypes.string,
		linkClass: PropTypes.string,
		width: PropTypes.number,
		height: PropTypes.number,
		id: PropTypes.string,
		linkTarget: PropTypes.string,
		sizeSlug: PropTypes.string,
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
