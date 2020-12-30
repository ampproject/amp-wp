/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { __, _x, sprintf } from '@wordpress/i18n';
import { cloneElement } from '@wordpress/element';
import { TextControl, SelectControl, ToggleControl, Notice, PanelBody, FontSizePicker } from '@wordpress/components';
import { InspectorControls } from '@wordpress/block-editor';
import { select } from '@wordpress/data';

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
 *
 * @return {Object} Modified block settings.
 */
export const addAMPAttributes = ( settings, name ) => {
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

	const isTextBlock = TEXT_BLOCKS.includes( name );

	// Fit-text for text blocks.
	if ( isTextBlock ) {
		if ( ! settings.attributes ) {
			settings.attributes = {};
		}
		settings.attributes.ampFitText = {
			type: 'boolean',
			default: false,
		};
		settings.attributes.minFont = {
			default: MIN_FONT_SIZE,
			source: 'attribute',
			selector: 'amp-fit-text',
			attribute: 'min-font-size',
		};
		settings.attributes.maxFont = {
			default: MAX_FONT_SIZE,
			source: 'attribute',
			selector: 'amp-fit-text',
			attribute: 'max-font-size',
		};
		settings.attributes.height = {
			// Needs to be higher than the maximum font size, which defaults to MAX_FONT_SIZE
			default: 'core/image' === name ? 200 : Math.ceil( MAX_FONT_SIZE / 10 ) * 10,
			source: 'attribute',
			selector: 'amp-fit-text',
			attribute: 'height',
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
 * Filters blocks' save function.
 *
 * @param {Object} element        Element to be saved.
 * @param {string} blockType      Block type.
 * @param {string} blockType.name Block type name.
 * @param {Object} attributes     Attributes.
 *
 * @return {Object} Output element.
 */
export const filterBlocksSave = ( element, blockType, attributes ) => { // eslint-disable-line complexity
	const fitTextProps = {
		layout: 'fixed-height',
	};

	if ( 'core/paragraph' === blockType.name && ! attributes.ampFitText ) {
		const content = getAmpFitTextContent( attributes.content );
		if ( content !== attributes.content ) {
			return cloneElement(
				element,
				{
					key: 'new',
					value: content,
				},
			);
		}
	} else if ( TEXT_BLOCKS.includes( blockType.name ) && attributes.ampFitText ) {
		if ( attributes.minFont ) {
			fitTextProps[ 'min-font-size' ] = attributes.minFont;
		}
		if ( attributes.maxFont ) {
			fitTextProps[ 'max-font-size' ] = attributes.maxFont;
		}
		if ( attributes.height ) {
			fitTextProps.height = attributes.height;
		}

		fitTextProps.children = element;

		return <amp-fit-text { ...fitTextProps } />;
	}

	return element;
};

/**
 * Returns the inner content of an AMP Fit Text tag.
 *
 * @param {string} content Original content.
 *
 * @return {string} Modified content.
 */
export const getAmpFitTextContent = ( content ) => {
	const contentRegex = /<amp-fit-text\b[^>]*>(.*?)<\/amp-fit-text>/;
	const match = contentRegex.exec( content );

	let newContent = content;

	if ( match && match[ 1 ] ) {
		newContent = match[ 1 ];
	}

	return newContent;
};

/**
 * Get layout options depending on the block.
 *
 * @param {string} block Block name.
 *
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
 *
 * @return {Function} Edit function.
 */
export const filterBlocksEdit = ( BlockEdit ) => {
	const EnhancedBlockEdit = function( props ) {
		const { attributes: { ampLayout }, name } = props;

		let inspectorControls;

		if ( 'core/gallery' === name ) {
			inspectorControls = setUpGalleryInspectorControls( props );
		} else if ( 'core/image' === name ) {
			inspectorControls = setUpImageInspectorControls( props );
		} else if ( MEDIA_BLOCKS.includes( name ) || 0 === name.indexOf( 'core-embed/' ) ) {
			inspectorControls = setUpInspectorControls( props );
		} else if ( TEXT_BLOCKS.includes( name ) ) {
			inspectorControls = setUpTextBlocksInspectorControls( props );
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
 * @param {Object} props Props.
 * @param {Function} props.setAttributes Callback to set attributes.
 * @param {Object} props.attributes Attributes.
 * @param {string} layout Layout.
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
 *
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
 *
 * @param {Object} props Props.
 *
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
 *
 * @param {Object} props Props.
 *
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
 * Setup inspector controls for text blocks.
 *
 * @todo Consider wrapping the render function to delete the original font size in text settings when ampFitText.
 *
 * @param {Object} props Props.
 * @param {Function} props.setAttributes Callback to set attributes.
 * @param {Object} props.attributes Attributes.
 * @param {boolean} props.isSelected Is selected.
 *
 * @return {ReactElement} Inspector Controls.
 */
const setUpTextBlocksInspectorControls = ( props ) => {
	const { isSelected, attributes, setAttributes } = props;
	const { ampFitText } = attributes;
	let { minFont, maxFont, height } = attributes;

	const FONT_SIZES = [
		{
			name: 'small',
			shortName: _x( 'S', 'font size', 'amp' ),
			size: 14,
		},
		{
			name: 'regular',
			shortName: _x( 'M', 'font size', 'amp' ),
			size: 16,
		},
		{
			name: 'large',
			shortName: _x( 'L', 'font size', 'amp' ),
			size: 36,
		},
		{
			name: 'larger',
			shortName: _x( 'XL', 'font size', 'amp' ),
			size: 48,
		},
	];

	if ( ! isSelected ) {
		return null;
	}

	const label = __( 'Automatically fit text to container', 'amp' );

	if ( ampFitText ) {
		maxFont = parseInt( maxFont );
		height = parseInt( height );
		minFont = parseInt( minFont );
	}

	return (
		<InspectorControls>
			<PanelBody
				title={ __( 'AMP Settings', 'amp' ) }
				className={ ampFitText ? 'is-amp-fit-text' : '' }
			>
				<ToggleControl
					label={ label }
					checked={ ampFitText }
					onChange={ () => setAttributes( { ampFitText: ! ampFitText } ) }
				/>
			</PanelBody>
			{ ampFitText && (
				<>
					<TextControl
						label={ __( 'Height', 'amp' ) }
						value={ height }
						min={ 1 }
						onChange={ ( nextHeight ) => {
							setAttributes( { height: nextHeight } );
						} }
					/>
					{ maxFont > height && (
						<Notice
							status="error"
							isDismissible={ false }
						>
							{ __( 'The height must be greater than the max font size.', 'amp' ) }
						</Notice>
					) }
					<PanelBody title={ __( 'Minimum font size', 'amp' ) }>
						<FontSizePicker
							fallbackFontSize={ 14 }
							value={ minFont }
							fontSizes={ FONT_SIZES }
							onChange={ ( nextMinFont ) => {
								if ( ! nextMinFont ) {
									nextMinFont = MIN_FONT_SIZE; // @todo Supplying fallbackFontSize should be done automatically by the component?
								}

								if ( parseInt( nextMinFont ) <= maxFont ) {
									setAttributes( { minFont: nextMinFont } );
								}
							} }
						/>
					</PanelBody>
					{ minFont > maxFont && (
						<Notice
							status="error"
							isDismissible={ false }
						>
							{ __( 'The min font size must less than the max font size.', 'amp' ) }
						</Notice>
					) }
					<PanelBody title={ __( 'Maximum font size', 'amp' ) }>
						<FontSizePicker
							fallbackFontSize={ 48 }
							value={ maxFont }
							fontSizes={ FONT_SIZES }
							onChange={ ( nextMaxFont ) => {
								if ( ! nextMaxFont ) {
									nextMaxFont = MAX_FONT_SIZE; // @todo Supplying fallbackFontSize should be done automatically by the component?
								}

								setAttributes( {
									maxFont: nextMaxFont,
									height: Math.max( nextMaxFont, height ),
								} );
							} }
						/>
					</PanelBody>
				</>
			) }
		</InspectorControls>
	);
};

setUpTextBlocksInspectorControls.propTypes = {
	isSelected: PropTypes.bool,
	attributes: PropTypes.shape( {
		ampFitText: PropTypes.bool,
		minFont: PropTypes.number,
		maxFont: PropTypes.number,
		height: PropTypes.number,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

/**
 * Get AMP Lightbox toggle control.
 *
 * @param {Object} props Props.
 *
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
 *
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
 *
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
 *
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
