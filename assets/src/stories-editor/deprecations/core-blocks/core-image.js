/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { RichText } from '@wordpress/block-editor';
import { omit } from 'lodash';

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

const deprecatedImage = [
	{
		attributes: {
			...blockAttributes,
			deprecated: {
				default: '1.2.0',
			},
		},
		save( { attributes } ) {
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
		},
		migrate: ( attributes ) => {
			return {
				...omit( attributes, [ 'deprecated', 'anchor' ] ),
			};
		},
	},
];

export default deprecatedImage;
