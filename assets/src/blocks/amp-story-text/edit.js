/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
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

function TextBlock( props ) {
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
	} = props;

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
				enable={ {
					top: true,
					right: true,
					bottom: true,
					left: true,
					topRight: false,
					bottomRight: false,
					bottomLeft: false,
					topLeft: false,
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

export default compose(
	withColors( 'backgroundColor', { textColor: 'color' } ),
	withFontSizes( 'fontSize' ),
	applyFallbackStyles
)( TextBlock );
