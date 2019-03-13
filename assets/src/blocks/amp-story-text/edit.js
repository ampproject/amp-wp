/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { PanelBody, SelectControl } from '@wordpress/components';
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

function TextBlock( props ) {
	const {
		attributes,
		setAttributes,
		className,
		fontSize,
		setFontSize,
		backgroundColor,
		textColor,
		setBackgroundColor,
		setTextColor,
	} = props;

	const {
		placeholder,
		content,
		type,
		ampFontFamily,
	} = attributes;

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
							// Todo: Calculate from page background.
							fallbackBackgroundColor: undefined,
						} }
						fontSize={ fontSize.size }
					/>
				</PanelColorSettings>
			</InspectorControls>
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
		</Fragment>
	);
}

export default compose(
	withColors( 'backgroundColor', { textColor: 'color' } ),
	withFontSizes( 'fontSize' ),
)( TextBlock );
