/**
 * Internal dependencies
 */
import { ALLOWED_CHILD_BLOCKS, BLOCKS_WITH_RESIZING, BLOCKS_WITH_TEXT_SETTINGS } from '../constants';
import { addAMPAttributesDeprecations } from '../deprecations/filters';
import { isMovableBlock } from './';
import getDefaultMinimumBlockHeight from './getDefaultMinimumBlockHeight';

/**
 * Adds AMP attributes to every allowed AMP Story block.
 *
 * @param {Object} settings Settings.
 * @param {string} name     Block name.
 *
 * @return {Object} Settings.
 */
const addAMPAttributes = ( settings, name ) => {
	const isChildBlock = ALLOWED_CHILD_BLOCKS.includes( name );

	if ( ! isChildBlock || 'core/template' === name ) {
		return settings;
	}

	if ( settings.attributes.deprecated && addAMPAttributesDeprecations[ settings.attributes.deprecated.default ] ) {
		const deprecateAMPAttributes = addAMPAttributesDeprecations[ settings.attributes.deprecated.default ];
		if ( 'function' === typeof deprecateAMPAttributes ) {
			return deprecateAMPAttributes( settings, name );
		}
	}

	const isImageBlock = 'core/image' === name;
	const isVideoBlock = 'core/video' === name;
	const isCTABlock = 'amp/amp-story-cta' === name;

	const needsTextSettings = BLOCKS_WITH_TEXT_SETTINGS.includes( name );

	// Image block already has width and height.
	const needsWidthHeight = BLOCKS_WITH_RESIZING.includes( name ) && ! isImageBlock;

	const addedAttributes = {
		addedAttributes: {
			type: 'number',
			default: 0,
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

	if ( isCTABlock ) {
		addedAttributes.anchor = {
			type: 'string',
			source: 'attribute',
			attribute: 'id',
			selector: 'amp-story-cta-layer',
		};
	}

	if ( isMovableBlock( name ) ) {
		addedAttributes.anchor = {
			type: 'string',
		};

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
			type: 'string',
		};
		addedAttributes.ampAnimationDelay = {
			default: 0,
		};
		addedAttributes.ampAnimationDuration = {
			default: 0,
		};
		addedAttributes.ampAnimationAfter = {
			type: 'string',
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

		addedAttributes.ampAriaLabel = {
			type: 'string',
			default: '',
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
};

export default addAMPAttributes;
