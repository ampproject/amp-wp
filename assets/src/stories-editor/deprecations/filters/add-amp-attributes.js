/**
 * Internal dependencies
 */
import {
	ALLOWED_MOVABLE_BLOCKS,
	BLOCKS_WITH_RESIZING,
	BLOCKS_WITH_TEXT_SETTINGS,
} from '../../constants';
import { getDefaultMinimumBlockHeight } from '../../helpers';

/**
 * Export previous filter versions based on the Plugin version number.
 */
export default {
	'1.2.0': ( settings, name ) => {
		const isImageBlock = 'core/image' === name;
		const isVideoBlock = 'core/video' === name;

		const isMovableBlock = ALLOWED_MOVABLE_BLOCKS.includes( name );
		const needsTextSettings = BLOCKS_WITH_TEXT_SETTINGS.includes( name );
		// Image block already has width and height.
		const needsWidthHeight = BLOCKS_WITH_RESIZING.includes( name ) && ! isImageBlock;

		const addedAttributes = {
			anchor: {
				type: 'string',
				source: 'attribute',
				attribute: 'id',
				selector: 'amp-story-grid-layer .amp-story-block-wrapper > *, amp-story-cta-layer',
			},
			ampAnimationType: {
				type: 'string',
			},
			addedAttributes: {
				type: 'number',
				default: 0,
			},
			ampAnimationAfter: {
				type: 'string',
			},
			fontSize: {
				type: 'string',
			},
			customFontSize: {
				type: 'number',
			},
			ampFontFamily: {
				type: 'string',
			},
			textColor: {
				type: 'string',
			},
			customTextColor: {
				type: 'string',
			},
			backgroundColor: {
				type: 'string',
			},
			customBackgroundColor: {
				type: 'string',
			},
			opacity: {
				type: 'number',
				default: 100,
			},
		};

		if ( needsTextSettings ) {
			addedAttributes.autoFontSize = {
				type: 'number',
				default: 36,
			};
			addedAttributes.ampFitText = {
				type: 'boolean',
				default: true,
			};
		}

		if ( isMovableBlock ) {
			addedAttributes.positionTop = {
				default: 0,
				type: 'number',
			};

			addedAttributes.positionLeft = {
				default: 5,
				type: 'number',
			};

			if ( needsWidthHeight ) {
				addedAttributes.height = {
					type: 'number',
					default: getDefaultMinimumBlockHeight( name ),
				};

				addedAttributes.width = {
					type: 'number',
					default: 250,
				};
			}

			addedAttributes.rotationAngle = {
				type: 'number',
				default: 0,
			};

			addedAttributes.ampAnimationType = {
				source: 'attribute',
				selector: '.amp-story-block-wrapper',
				attribute: 'animate-in',
			};
			addedAttributes.ampAnimationDelay = {
				source: 'attribute',
				selector: '.amp-story-block-wrapper',
				attribute: 'animate-in-delay',
				default: 0,
			};
			addedAttributes.ampAnimationDuration = {
				source: 'attribute',
				selector: '.amp-story-block-wrapper',
				attribute: 'animate-in-duration',
				default: 0,
			};
			addedAttributes.ampAnimationAfter = {
				source: 'attribute',
				selector: '.amp-story-block-wrapper',
				attribute: 'animate-in-after',
			};
		}

		if ( isImageBlock ) {
			addedAttributes.ampShowImageCaption = {
				type: 'boolean',
				default: false,
			};
		}

		if ( isVideoBlock ) {
			addedAttributes.ampShowCaption = {
				type: 'boolean',
				default: false,
			};

			// Required defaults for AMP validity.
			addedAttributes.autoplay = {
				...settings.attributes.autoplay,
				default: true,
			};
			addedAttributes.playsInline = {
				...settings.attributes.playsInline,
				default: false,
			};

			// Optional defaults.
			addedAttributes.loop = {
				...settings.attributes.loop,
				default: true,
			};
			addedAttributes.controls = {
				...settings.attributes.controls,
				default: false,
			};
		}

		// Keep default values of possibly already existing default values.
		Object.keys( addedAttributes ).forEach( ( attribute ) => {
			if ( 'undefined' !== typeof addedAttributes[ attribute ].default ) {
				return;
			}

			if ( 'undefined' !== typeof settings.attributes[ attribute ] && 'undefined' !== typeof settings.attributes[ attribute ].default ) {
				addedAttributes[ attribute ].default = settings.attributes[ attribute ].default;
			}
		} );

		return {
			...settings,
			attributes: {
				...settings.attributes,
				...addedAttributes,
			},
			supports: {
				...settings.supports,
				anchor: false,
			},
		};
	},
};
