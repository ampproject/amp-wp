/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { RichText } from '@wordpress/block-editor';
import { getPercentageFromPixels, getUniqueId } from '../helpers';

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
};

const deprecatedImage = [
	{
		attributes: blockAttributes,
		save( props ) {
			const { attributes } = props;
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
				ampAnimationType,
				ampAnimationDelay,
				ampAnimationDuration,
				ampAnimationAfter,
				positionTop,
				positionLeft,
				rotationAngle,
			} = attributes;

			let style = ! props.style ? {} : props.style;
			if ( rotationAngle ) {
				style = {
					...style,
					transform: `rotate(${ parseInt( rotationAngle ) }deg)`,
				};
			}

			if ( 'undefined' !== typeof positionTop && 'undefined' !== typeof positionLeft ) {
				style = {
					...style,
					position: 'absolute',
					top: `${ positionTop || 0 }%`,
					left: `${ positionLeft || 0 }%`,
				};
			}

			// If the block has width and height set, set responsive values. Exclude text blocks since these already have it handled.
			if ( 'undefined' !== typeof width && 'undefined' !== typeof height ) {
				style = {
					...style,
					width: width ? `${ getPercentageFromPixels( 'x', width ) }%` : '0%',
					height: height ? `${ getPercentageFromPixels( 'y', height ) }%` : '0%',
				};
			}

			const animationAtts = {};

			// Add animation if necessary.
			if ( ampAnimationType ) {
				animationAtts[ 'animate-in' ] = ampAnimationType;

				if ( ampAnimationDelay ) {
					animationAtts[ 'animate-in-delay' ] = parseInt( ampAnimationDelay ) + 'ms';
				}

				if ( ampAnimationDuration ) {
					animationAtts[ 'animate-in-duration' ] = parseInt( ampAnimationDuration ) + 'ms';
				}

				if ( ampAnimationAfter ) {
					animationAtts[ 'animate-in-after' ] = ampAnimationAfter;
				}
			}

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

			const anchorId = attributes.anchor || getUniqueId();
			if ( 'left' === align || 'right' === align || 'center' === align ) {
				return (
					<amp-story-grid-layer template="vertical" data-block-name="core/image" >
						<div className="amp-story-block-wrapper" style={ style } { ...animationAtts }>
							<div>
								<figure className={ classes } id={ anchorId }>
									{ figure }
								</figure>
							</div>
						</div>
					</amp-story-grid-layer>
				);
			}

			return (
				<amp-story-grid-layer template="vertical" data-block-name="core/image" >
					<div className="amp-story-block-wrapper" style={ style } { ...animationAtts }>
						<figure className={ classes } id={ anchorId }>
							{ figure }
						</figure>
					</div>
				</amp-story-grid-layer>
			);
		},
	},
];

export default deprecatedImage;
