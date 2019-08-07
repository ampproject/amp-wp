/**
 * Internal dependencies
 */
import { getPercentageFromPixels } from '../../helpers';

/**
 * Export previous filter versions based on the Plugin version number.
 */
export default {
	'1.2.0': ( element, blockType, attributes ) => {
		const {
			ampAnimationType,
			ampAnimationDelay,
			ampAnimationDuration,
			ampAnimationAfter,
			positionTop,
			positionLeft,
			width,
			height,
		} = attributes;

		let style = {};

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

		return (
			<amp-story-grid-layer template="vertical" data-block-name={ blockType.name }>
				<div className="amp-story-block-wrapper" style={ style } { ...animationAtts }>
					{ element }
				</div>
			</amp-story-grid-layer>
		);
	},
};
