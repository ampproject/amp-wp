/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { isFunction, isObject, isString } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { ToggleControl, PanelBody } from '@wordpress/components';
import { InspectorControls, store as blockEditorStore } from '@wordpress/block-editor';
import { select, useSelect } from '@wordpress/data';
import { cloneElement, isValidElement } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { TEXT_BLOCKS } from '../constants';
import { MIN_FONT_SIZE, MAX_FONT_SIZE } from '../../common/constants';

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

	// AMP Carousel and AMP Lightbox settings.
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
 * Filters blocks edit function of all blocks.
 *
 * @param {Function} BlockEdit function.
 * @return {Function} Edit function.
 */
export const filterBlocksEdit = ( BlockEdit ) => {
	if ( ! isFunction( BlockEdit ) ) {
		return BlockEdit;
	}

	const EnhancedBlockEdit = ( props ) => {
		const { isSelected, name } = props;

		if ( isSelected && 'core/image' === name ) {
			return (
				<>
					<BlockEdit { ...props } />
					<ImageBlockLayoutAttributes { ...props } />
				</>
			);
		}

		if ( isSelected && 'core/gallery' === name ) {
			return (
				<>
					<BlockEdit { ...props } />
					<GalleryBlockLayoutAttributes { ...props } />
				</>
			);
		}

		return <BlockEdit { ...props } />;
	};

	EnhancedBlockEdit.propTypes = {
		attributes: PropTypes.shape( {
			ampLightbox: PropTypes.bool,
			linkTo: PropTypes.string,
		} ),
		isSelected: PropTypes.bool,
		name: PropTypes.string,
		setAttributes: PropTypes.func.isRequired,
	};

	return EnhancedBlockEdit;
};

/**
 * Get AMP Lightbox toggle control.
 *
 * @param {Object} props Props.
 * @return {JSX.Element} Element.
 */
const AmpLightboxToggle = ( props ) => {
	const { attributes: { ampLightbox, linkTo }, setAttributes } = props;

	return (
		<ToggleControl
			label={ __( 'Add lightbox effect', 'amp' ) }
			checked={ ampLightbox }
			onChange={ ( nextValue ) => {
				setAttributes( { ampLightbox: ! ampLightbox } );
				// In case of lightbox set linking images to 'none'.
				if ( nextValue && linkTo && 'none' !== linkTo ) {
					setAttributes( { linkTo: 'none' } );
				}
			} }
		/>
	);
};

AmpLightboxToggle.propTypes = {
	attributes: PropTypes.shape( {
		ampLightbox: PropTypes.bool,
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
 * Inspector controls for Image block.
 *
 * @param {Object} props          Props.
 * @param {string} props.clientId Block client ID.
 * @return {Object} Inspector Controls.
 */
const ImageBlockLayoutAttributes = ( props ) => {
	const { clientId } = props;

	const isGalleryBlockChild = useSelect( ( _select ) => {
		return _select( blockEditorStore ).getBlockParentsByBlockName( clientId, 'core/gallery' ).length > 0;
	}, [ clientId ] );

	if ( isGalleryBlockChild ) {
		return null;
	}

	return (
		<InspectorControls>
			<PanelBody title={ __( 'AMP Settings', 'amp' ) }>
				<AmpLightboxToggle { ...props } />
			</PanelBody>
		</InspectorControls>
	);
};

ImageBlockLayoutAttributes.propTypes = {
	clientId: PropTypes.string,
};

/**
 * Inspector controls for Gallery block.
 *
 * @param {Object} props Props.
 * @return {Object} Inspector Controls.
 */
const GalleryBlockLayoutAttributes = ( props ) => (
	<InspectorControls>
		<PanelBody title={ __( 'AMP Settings', 'amp' ) }>
			<AmpLightboxToggle { ...props } />
			<AmpCarouselToggle { ...props } />
		</PanelBody>
	</InspectorControls>
);

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
