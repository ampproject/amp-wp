/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { isFunction, isObject, isString } from 'lodash';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { SelectControl, ToggleControl, Notice, PanelBody } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { select } from '@wordpress/data';
import { cloneElement, isValidElement } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { TEXT_BLOCKS, MEDIA_BLOCKS, DEFAULT_HEIGHT, DEFAULT_WIDTH } from '../constants';
import { MIN_FONT_SIZE, MAX_FONT_SIZE } from '../../common/constants';

const ampLayoutOptions = [
	{
		value: 'nodisplay',
		label: __( 'No Display', 'amp' ),
		notAvailable: [
			'core-embed/vimeo',
			'core-embed/dailymotion',
			'core-embed/reddit',
			'core-embed/soundcloud',
		],
	},
	{
		// Not supported by amp-audio and amp-pixel.
		value: 'fixed',
		label: __( 'Fixed', 'amp' ),
		notAvailable: [
			'core-embed/soundcloud',
		],
	},
	{
		// To ensure your AMP element displays, you must specify a width and height for the containing element.
		value: 'responsive',
		label: __( 'Responsive', 'amp' ),
		notAvailable: [
			'core-embed/soundcloud',
		],
	},
	{
		value: 'fixed-height',
		label: __( 'Fixed Height', 'amp' ),
		notAvailable: [],
	},
	{
		value: 'fill',
		label: __( 'Fill', 'amp' ),
		notAvailable: [
			'core-embed/soundcloud',
		],
	},
	{
		value: 'flex-item',
		label: __( 'Flex Item', 'amp' ),
		notAvailable: [
			'core-embed/soundcloud',
		],
	},
	{
		value: 'intrinsic',
		label: __( 'Intrinsic', 'amp' ),
		notAvailable: [
			'core-embed/youtube',
			'core-embed/facebook',
			'core-embed/instagram',
			'core-embed/vimeo',
			'core-embed/dailymotion',
			'core-embed/reddit',
			'core-embed/soundcloud',
		],
	},
];

/**
 * Add AMP attributes to every core block.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 * @return {Object} Modified block settings.
 */
export const addAMPAttributes = ( settings, name ) => {
	if ( ! isObject( settings ) || ! isString( name ) ) {
		return settings;
	}

	// AMP Carousel settings.
	if ( 'core/gallery' === name ) {
		if ( ! settings.attributes ) {
			settings.attributes = {};
		}
		settings.attributes.ampCarousel = {
			type: 'boolean',
			default: ! select( 'amp/block-editor' )?.hasThemeSupport(), // @todo We could just default this to false now even in Reader mode since block styles are loaded.
		};
		settings.attributes.ampLightbox = {
			type: 'boolean',
			default: false,
		};
	}

	// Add AMP Lightbox settings.
	if ( 'core/image' === name ) {
		if ( ! settings.attributes ) {
			settings.attributes = {};
		}
		settings.attributes.ampLightbox = {
			type: 'boolean',
			default: false,
		};
	}

	// Layout settings for embeds and media blocks.
	if ( 0 === name.indexOf( 'core-embed' ) || MEDIA_BLOCKS.includes( name ) ) {
		if ( ! settings.attributes ) {
			settings.attributes = {};
		}
		settings.attributes.ampLayout = {
			type: 'string',
		};
		settings.attributes.ampNoLoading = {
			type: 'boolean',
		};
	}
	return settings;
};

/**
 * Removes `amp-fit-text` related attributes on blocks via block deprecation.
 *
 * @param {Object} settings Block settings.
 * @param {string} name     Block name.
 * @return {Object} Modified block settings.
 */
export const removeAmpFitTextFromBlocks = ( settings, name ) => {
	if ( ! isObject( settings ) || ! isString( name ) ) {
		return settings;
	}

	if ( TEXT_BLOCKS.includes( name ) ) {
		if ( ! settings.deprecated ) {
			settings.deprecated = [];
		}

		settings.deprecated.unshift( {
			supports: settings.supports,
			attributes: {
				...( settings.attributes || {} ),
				ampFitText: {
					type: 'boolean',
					default: false,
				},
				minFont: {
					default: MIN_FONT_SIZE,
					source: 'attribute',
					selector: 'amp-fit-text',
					attribute: 'min-font-size',
				},
				maxFont: {
					default: MAX_FONT_SIZE,
					source: 'attribute',
					selector: 'amp-fit-text',
					attribute: 'max-font-size',
				},
				height: {
					// Needs to be higher than the maximum font size, which defaults to MAX_FONT_SIZE
					default: 'core/image' === name ? 200 : Math.ceil( MAX_FONT_SIZE / 10 ) * 10,
					source: 'attribute',
					selector: 'amp-fit-text',
					attribute: 'height',
				},
			},
			save( props ) {
				/* eslint-disable react/prop-types */
				const { attributes } = props;
				const fitTextProps = { layout: 'fixed-height' };

				if ( attributes.minFont ) {
					fitTextProps[ 'min-font-size' ] = attributes.minFont;
				}
				if ( attributes.maxFont ) {
					fitTextProps[ 'max-font-size' ] = attributes.maxFont;
				}
				if ( attributes.height ) {
					fitTextProps.height = attributes.height;
				}
				/* eslint-enable react/prop-types */

				fitTextProps.children = settings.save( props );

				return <amp-fit-text { ...fitTextProps } />;
			},
			isEligible( { ampFitText } ) {
				return undefined !== ampFitText;
			},
			migrate( attributes ) {
				const deprecatedAttrs = [ 'ampFitText', 'minFont', 'maxFont', 'height' ];
				deprecatedAttrs.forEach( ( attr ) => delete attributes[ attr ] );
				return attributes;
			},
		} );
	}

	return settings;
};

/**
 * Remove the `class` attribute from `amp-fit-text` elements so that it can be deprecated successfully.
 *
 * The `class` attribute is added by the `core/generated-class-name/save-props` block editor filter; it is unwanted and
 * interferes with successful deprecation of the block. By filtering the saved element the `class` attribute can be
 * removed and the deprecation of the block and proceed without error.
 *
 * @see removeAmpFitTextFromBlocks
 * @param {JSX.Element} element Block save result.
 * @return {JSX.Element} Modified block if it is of `amp-fit-text` type, otherwise the  original element is returned.
 */
export const removeClassFromAmpFitTextBlocks = ( element ) => {
	if ( isValidElement( element ) && 'amp-fit-text' === element.type && undefined !== element.props.className ) {
		const { className, ...props } = element.props;
		props.className = null;
		element = cloneElement( element, props );
	}

	return element;
};

/**
 * Get layout options depending on the block.
 *
 * @param {string} block Block name.
 * @return {Object[]} Options.
 */
export const getLayoutOptions = ( block ) => {
	const layoutOptions = [
		{
			value: '',
			label: __( 'Default', 'amp' ),
		},
	];

	for ( const option of ampLayoutOptions ) {
		const isLayoutAvailable = ! option.notAvailable.includes( block );

		if ( isLayoutAvailable ) {
			layoutOptions.push( {
				value: option.value,
				label: option.label,
			} );
		}
	}

	return layoutOptions;
};

/**
 * Filters blocks edit function of all blocks.
 *
 * @param {Function} BlockEdit function.
 * @return {Function} Edit function.
 */
export const filterBlocksEdit = ( BlockEdit ) => {
	if ( ! isFunction( BlockEdit ) ) {
		return BlockEdit;
	}

	const EnhancedBlockEdit = function( props ) {
		const { attributes: { ampLayout }, name } = props;

		let inspectorControls;

		if ( 'core/gallery' === name ) {
			inspectorControls = setUpGalleryInspectorControls( props );
		} else if ( 'core/image' === name ) {
			inspectorControls = setUpImageInspectorControls( props );
		} else if ( MEDIA_BLOCKS.includes( name ) || 0 === name.indexOf( 'core-embed/' ) ) {
			inspectorControls = setUpInspectorControls( props );
		}

		// Return just inspector controls in case of 'nodisplay'.
		if ( ampLayout && 'nodisplay' === ampLayout ) {
			return [
				inspectorControls,
			];
		}

		return (
			<>
				<BlockEdit { ...props } />
				{ inspectorControls }
			</>
		);
	};

	EnhancedBlockEdit.propTypes = {
		attributes: PropTypes.shape( {
			text: PropTypes.string,
			ampLayout: PropTypes.string,
		} ),
		setAttributes: PropTypes.func.isRequired,
		name: PropTypes.string,
	};

	return EnhancedBlockEdit;
};

/**
 * Set width and height in case of image block.
 *
 * @param {Object}   props               Props.
 * @param {Function} props.setAttributes Callback to set attributes.
 * @param {Object}   props.attributes    Attributes.
 * @param {string}   layout              Layout.
 */
export const setImageBlockLayoutAttributes = ( props, layout ) => {
	const { attributes, setAttributes } = props;
	switch ( layout ) {
		case 'fixed-height':
			if ( ! attributes.height ) {
				setAttributes( { height: DEFAULT_HEIGHT } );
			}
			// Lightbox doesn't work with fixed height, so unset it.
			if ( attributes.ampLightbox ) {
				setAttributes( { ampLightbox: false } );
			}
			break;

		case 'fixed':
			if ( ! attributes.height ) {
				setAttributes( { height: DEFAULT_HEIGHT } );
			}
			if ( ! attributes.width ) {
				setAttributes( { width: DEFAULT_WIDTH } );
			}
			break;

		default:
			break;
	}
};

/**
 * Default setup for inspector controls.
 *
 * @param {Object} props Props.
 * @return {ReactElement} Inspector Controls.
 */
export const setUpInspectorControls = ( props ) => {
	const { isSelected } = props;

	if ( ! isSelected ) {
		return null;
	}

	return (
		<InspectorControls>
			<PanelBody title={ __( 'AMP Settings', 'amp' ) }>
				<AmpLayoutControl { ...props } />
				<AmpNoloadingToggle { ...props } />
			</PanelBody>
		</InspectorControls>
	);
};

setUpInspectorControls.propTypes = {
	isSelected: PropTypes.bool,
};

/**
 * Get AMP Layout select control.
 *
 * @deprecated As of v2.1. Blocks with the `ampLayout` attribute will still be able to use the control.
 * @param {Object} props Props.
 * @return {ReactElement} Element.
 */
export const AmpLayoutControl = ( props ) => {
	const { name, attributes: { ampLayout }, setAttributes } = props;

	if ( undefined === ampLayout ) {
		return null;
	}

	let label = __( 'AMP Layout', 'amp' );

	if ( 'core/image' === name ) {
		label = __( 'AMP Layout (modifies width/height)', 'amp' );
	}

	return (
		<>
			<Notice
				status="warning"
				isDismissible={ false }
			>
				<span dangerouslySetInnerHTML={ {
					__html: sprintf(
						/* translators: placeholder is link to support forum. */
						__( 'The AMP Layout setting is deprecated and is slated for removal. Please <a href="%s" target="_blank" rel="noreferrer">report</a> if you need it.', 'amp' ),
						'https://wordpress.org/support/plugin/amp/#new-topic-0',
					),
				} } />
			</Notice>

			<SelectControl
				label={ label }
				value={ ampLayout }
				options={ getLayoutOptions( name ) }
				onChange={ ( value ) => {
					setAttributes( { ampLayout: value } );
					if ( 'core/image' === props.name ) {
						setImageBlockLayoutAttributes( props, value );
					}
				} }
			/>
		</>
	);
};

AmpLayoutControl.propTypes = {
	name: PropTypes.string,
	attributes: PropTypes.shape( {
		ampLayout: PropTypes.string,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

/**
 * Get AMP Noloading toggle control.
 *
 * @deprecated As of v2.1. Blocks with the `ampNoLoading` attribute will still be able to use the control.
 * @param {Object} props Props.
 * @return {ReactElement} Element.
 */
export const AmpNoloadingToggle = ( props ) => {
	const { attributes: { ampNoLoading }, setAttributes } = props;

	if ( undefined === ampNoLoading ) {
		return null;
	}

	const label = __( 'AMP Noloading', 'amp' );

	return (
		<>
			<Notice
				status="warning"
				isDismissible={ false }
			>
				<span dangerouslySetInnerHTML={ {
					__html: sprintf(
						/* translators: placeholder is link to support forum. */
						__( 'The AMP Noloading setting is deprecated and is slated for removal. Please <a href="%s" target="_blank" rel="noreferrer">report</a> if you need it.', 'amp' ),
						'https://wordpress.org/support/plugin/amp/#new-topic-0',
					),
				} } />
			</Notice>

			<ToggleControl
				label={ label }
				checked={ ampNoLoading }
				onChange={ () => setAttributes( { ampNoLoading: ! ampNoLoading } ) }
			/>
		</>
	);
};

AmpNoloadingToggle.propTypes = {
	attributes: PropTypes.shape( {
		ampNoLoading: PropTypes.bool,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

/**
 * Get AMP Lightbox toggle control.
 *
 * @param {Object} props Props.
 * @return {ReactElement} Element.
 */
const AmpLightboxToggle = ( props ) => {
	const { attributes: { ampLightbox, linkTo, ampLayout }, setAttributes } = props;

	return (
		<ToggleControl
			label={ __( 'Add lightbox effect', 'amp' ) }
			checked={ ampLightbox }
			onChange={ ( nextValue ) => {
				setAttributes( { ampLightbox: ! ampLightbox } );
				if ( nextValue ) {
					// Lightbox doesn't work with fixed height, so change.
					if ( 'fixed-height' === ampLayout ) {
						setAttributes( { ampLayout: 'fixed' } );
					}
					// In case of lightbox set linking images to 'none'.
					if ( linkTo && 'none' !== linkTo ) {
						setAttributes( { linkTo: 'none' } );
					}
				}
			} }
		/>
	);
};

AmpLightboxToggle.propTypes = {
	attributes: PropTypes.shape( {
		ampLightbox: PropTypes.bool,
		ampLayout: PropTypes.string,
		linkTo: PropTypes.string,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

/**
 * Get AMP Carousel toggle control.
 *
 * @param {Object}   props                        Props.
 * @param {Object}   props.attributes             Block attributes.
 * @param {Object}   props.attributes.ampCarousel AMP Carousel toggle value.
 * @param {Function} props.setAttributes          Callback to update attributes.
 * @return {Object} Element.
 */
const AmpCarouselToggle = ( props ) => {
	const { attributes: { ampCarousel }, setAttributes } = props;

	return (
		<ToggleControl
			label={ __( 'Display as carousel', 'amp' ) }
			checked={ ampCarousel }
			onChange={ () => setAttributes( { ampCarousel: ! ampCarousel } ) }
		/>
	);
};

AmpCarouselToggle.propTypes = {
	attributes: PropTypes.shape( {
		ampCarousel: PropTypes.bool,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

/**
 * Set up inspector controls for Image block.
 *
 * @param {Object}  props            Props.
 * @param {boolean} props.isSelected Whether the current block has been selected or not.
 * @return {Object} Inspector Controls.
 */
const setUpImageInspectorControls = ( props ) => {
	const { isSelected } = props;

	if ( ! isSelected ) {
		return null;
	}

	return (
		<InspectorControls>
			<PanelBody title={ __( 'AMP Settings', 'amp' ) }>
				<AmpLayoutControl { ...props } />
				<AmpNoloadingToggle { ...props } />
				<AmpLightboxToggle { ...props } />
			</PanelBody>
		</InspectorControls>
	);
};

setUpImageInspectorControls.propTypes = {
	isSelected: PropTypes.bool,
};

/**
 * Set up inspector controls for Gallery block.
 * Adds ampCarousel attribute for displaying the output as amp-carousel.
 *
 * @param {Object}  props            Props.
 * @param {boolean} props.isSelected Whether the current block has been selected or not.
 * @return {Object} Inspector controls.
 */
const setUpGalleryInspectorControls = ( props ) => {
	const { isSelected } = props;

	if ( ! isSelected ) {
		return null;
	}

	return (
		<InspectorControls>
			<PanelBody title={ __( 'AMP Settings', 'amp' ) }>
				<AmpCarouselToggle { ...props } />
				<AmpLightboxToggle { ...props } />
			</PanelBody>
		</InspectorControls>
	);
};

setUpGalleryInspectorControls.propTypes = {
	isSelected: PropTypes.bool,
};

/**
 * Determines whether AMP is enabled for the current post or not.
 *
 * For regular posts, this is based on the AMP toggle control and also
 * the default status based on the template mode.
 *
 * @return {boolean} Whether AMP is enabled.
 */
export const isAMPEnabled = () => {
	const { getEditedPostAttribute } = select( 'core/editor' );
	return getEditedPostAttribute( 'amp_enabled' ) || false;
};
