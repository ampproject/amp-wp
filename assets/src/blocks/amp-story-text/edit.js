/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import { PanelBody, ResizableBox, SelectControl, withFallbackStyles } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import {
	RichText,
	InspectorControls,
	FontSizePicker,
	withFontSizes,
	withColors,
	PanelColorSettings,
	ContrastChecker,
} from '@wordpress/editor';

/**
 * Internal dependencies
 */
import { FontFamilyPicker } from '../../components';
import { maybeEnqueueFontStyle } from '../../helpers';

const { getComputedStyle } = window;

const maxLimitFontSize = 54;
const minLimitFontSize = 14;

const applyFallbackStyles = withFallbackStyles( ( node, ownProps ) => {
	const { textColor, backgroundColor, fontSize, customFontSize } = ownProps.attributes;
	const editableNode = node.querySelector( '[contenteditable="true"]' );
	const computedStyles = editableNode ? getComputedStyle( editableNode ) : null;

	return {
		fallbackBackgroundColor: backgroundColor || ! computedStyles ? undefined : computedStyles.backgroundColor,
		fallbackTextColor: textColor || ! computedStyles ? undefined : computedStyles.color,
		fallbackFontSize: fontSize || customFontSize || ! computedStyles ? undefined : parseInt( computedStyles.fontSize ) || undefined,
	};
} );

class TextBlockEdit extends Component {
	componentDidUpdate() {
		const { attributes, setFontSize, fontSize, isSelected } = this.props;
		if ( isSelected ) {
			/**
			 * Calculates font size that fits to the text element based on the element's size.
			 * Replicates amp-fit-text's logic in the editor.
			 *
			 * @see https://github.com/ampproject/amphtml/blob/e7a1b3ff97645ec0ec482192205134bd0735943c/extensions/amp-fit-text/0.1/amp-fit-text.js
			 *
			 * @param {Object} measurer HTML element.
			 * @param {number} expectedHeight Maximum height.
			 * @param {number} expectedWidth Maximum width.
			 * @param {number} maxFontSize Maximum font size.
			 * @param {number} minFontSize Minimum font size.
			 * @return {number} Calculated font size.
			 */
			const calculateFontSize = ( measurer, expectedHeight, expectedWidth, maxFontSize, minFontSize ) => {
				maxFontSize++;
				// Binomial search for the best font size.
				while ( maxFontSize - minFontSize > 1 ) {
					const mid = Math.floor( ( minFontSize + maxFontSize ) / 2 );
					measurer.style.fontSize = mid + 'px';
					const currentHeight = measurer.offsetHeight;
					const currentWidth = measurer.offsetWidth;
					if ( currentHeight > expectedHeight || currentWidth > expectedWidth ) {
						maxFontSize = mid;
					} else {
						minFontSize = mid;
					}
				}
				return minFontSize;
			};
			// Check if the font size is OK, if not, update the font size if not.
			const element = document.querySelector( `#block-${ this.props.clientId } .block-editor-rich-text__editable` );
			if ( element ) {
				const fitFontSize = calculateFontSize( element, attributes.height, attributes.width, maxLimitFontSize, minLimitFontSize );
				if ( fontSize.size !== fitFontSize ) {
					setFontSize( fitFontSize );
				}
			}
		}
	}

	render() {
		const {
			attributes,
			setAttributes,
			className,
			fontSize,
			isSelected,
			setFontSize,
			backgroundColor,
			textColor,
			setBackgroundColor,
			setTextColor,
			fallbackTextColor,
			fallbackBackgroundColor,
			toggleSelection,
		} = this.props;

		const {
			placeholder,
			content,
			type,
			ampFontFamily,
			height,
			width,
		} = attributes;

		const minTextHeight = 20;
		const minTextWidth = 30;

		return (
			<Fragment>
				<InspectorControls>
					<PanelBody title={ __( 'Text Settings', 'amp' ) }>
						<FontFamilyPicker
							name={ ampFontFamily }
							onChange={ ( value ) => {
								maybeEnqueueFontStyle( value );
								setAttributes( { ampFontFamily: value } );
							} }
						/>
						<FontSizePicker
							value={ fontSize.size }
							onChange={ setFontSize }
						/>
						<SelectControl
							label={ __( 'Select text type', 'amp' ) }
							value={ type }
							onChange={ ( selected ) => setAttributes( { type: selected } ) }
							options={ [
								{ value: 'auto', label: __( 'Automatic', 'amp' ) },
								{ value: 'p', label: __( 'Paragraph', 'amp' ) },
								{ value: 'h1', label: __( 'Heading 1', 'amp' ) },
								{ value: 'h2', label: __( 'Heading 2', 'amp' ) },
							] }
						/>
					</PanelBody>
					<PanelColorSettings
						title={ __( 'Color Settings', 'amp' ) }
						initialOpen={ false }
						colorSettings={ [
							{
								value: backgroundColor.color,
								onChange: setBackgroundColor,
								label: __( 'Background Color', 'amp' ),
							},
							{
								value: textColor.color,
								onChange: setTextColor,
								label: __( 'Text Color', 'amp' ),
							},
						] }
					>
						<ContrastChecker
							{ ...{
								textColor: textColor.color,
								backgroundColor: backgroundColor.color,
								fallbackTextColor,
								fallbackBackgroundColor,
								fontSize: fontSize.size,
							} }
						/>
					</PanelColorSettings>
				</InspectorControls>
				<ResizableBox
					className={ classnames(
						'amp-story-text__resize-container',
						{ 'is-selected': isSelected }
					) }
					size={ {
						height,
						width,
					} }
					minHeight={ minTextHeight }
					minWidth={ minTextWidth }
					// Adding only right and bottom since otherwise it needs to change the top and left position, too.
					enable={ {
						top: false,
						right: true,
						bottom: true,
						left: false,
					} }
					onResizeStop={ ( event, direction, elt, delta ) => {
						setAttributes( {
							width: parseInt( width + delta.width, 10 ),
							height: parseInt( height + delta.height, 10 ),
						} );
						toggleSelection( true );
					} }
					onResizeStart={ () => {
						toggleSelection( false );
					} }
				>
					<RichText
						identifier="content"
						wrapperClassName="wp-block-amp-story-text"
						tagName="p"
						value={ content }
						onChange={ ( value ) => setAttributes( { content: value } ) }
						style={ {
							backgroundColor: backgroundColor.color,
							color: textColor.color,
							fontSize: fontSize.size ? fontSize.size + 'px' : undefined,
						} }
						className={ classnames( className, {
							'has-text-color': textColor.color,
							'has-background': backgroundColor.color,
							[ backgroundColor.class ]: backgroundColor.class,
							[ textColor.class ]: textColor.class,
							[ fontSize.class ]: fontSize.class,
						} ) }
						placeholder={ placeholder || __( 'Write text…', 'amp' ) }
					/>
				</ResizableBox>
			</Fragment>
		);
	}
}

export default compose(
	withColors( 'backgroundColor', { textColor: 'color' } ),
	withFontSizes( 'fontSize' ),
	applyFallbackStyles
)( TextBlockEdit );
