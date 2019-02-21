/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { PanelBody, SelectControl, withFallbackStyles } from '@wordpress/components';
import { RichText, InspectorControls, FontSizePicker, withFontSizes } from '@wordpress/editor';
import { compose } from '@wordpress/compose';

const { getComputedStyle } = window;

/**
 * Internal dependencies
 */
import getTagName from './getTagName';

const applyFallbackStyles = withFallbackStyles( ( node, ownProps ) => {
	const { fontSize, customFontSize } = ownProps.attributes;
	const editableNode = node.querySelector( '[contenteditable="true"]' );
	//verify if editableNode is available, before using getComputedStyle.
	const computedStyles = editableNode ? getComputedStyle( editableNode ) : null;

	return {
		fallbackFontSize: fontSize || customFontSize || ! computedStyles ? undefined : parseInt( computedStyles.fontSize ) || undefined
	};
} );

function TextBlock( {
	attributes,
	setAttributes,
	className,
	fallbackFontSize,
	fontSize,
	setFontSize
} ) {
	const { content, type } = attributes;
	const tagName = getTagName( attributes );

	const fontSizeClass = fontSize.class || undefined;

	return (
		<Fragment>
			<InspectorControls>
				<PanelBody title={ __( 'Text Settings', 'amp' ) }>
					<FontSizePicker
						fallbackFontSize={ fallbackFontSize }
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
							{ value: 'h2', label: __( 'Heading 2', 'amp' ) }
						] }
					/>
				</PanelBody>
			</InspectorControls>
			<RichText
				identifier="content"
				wrapperClassName="wp-block-amp-story-text"
				tagName={ tagName }
				value={ content }
				onChange={ ( value ) => setAttributes( { content: value } ) }
				style={ {
					fontSize: fontSize.size ? fontSize.size + 'px' : undefined
				} }
				className={ `${className} ${fontSizeClass}` }
				placeholder={ __( 'Write text…', 'amp' ) }
			/>
		</Fragment>
	);
}

const TextEdit = compose( [
	withFontSizes( 'fontSize' ),
	applyFallbackStyles
] )( TextBlock );

export default TextEdit;
