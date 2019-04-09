/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import {
	PanelBody,
	ResizableBox,
	SelectControl,
	withFallbackStyles,
	ToggleControl,
	RangeControl,
} from '@wordpress/components';
import { compose } from '@wordpress/compose';
import {
	RichText,
	InspectorControls,
	FontSizePicker,
	withFontSizes,
	withColors,
	PanelColorSettings,
	ContrastChecker,
	BlockControls,
	AlignmentToolbar,
} from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { FontFamilyPicker } from '../../components';
import { maybeEnqueueFontStyle, calculateFontSize, getRgbaFromHex } from '../../helpers';
import './edit.css';

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
	constructor() {
		super( ...arguments );

		this.onReplace = this.onReplace.bind( this );
	}

	componentDidUpdate() {
		const { attributes, isSelected, setAttributes } = this.props;
		const {
			height,
			width,
			autoFontSize,
			ampFitText,
		} = attributes;

		if ( isSelected && ampFitText ) {
			// Check if the font size is OK, if not, update the font size if not.
			const element = document.querySelector( `#block-${ this.props.clientId } .block-editor-rich-text__editable` );
			if ( element ) {
				const fitFontSize = calculateFontSize( element, height, width, maxLimitFontSize, minLimitFontSize );
				if ( autoFontSize !== fitFontSize ) {
					setAttributes( { autoFontSize: fitFontSize } );
				}
			}
		}
	}

	onReplace( blocks ) {
		const { attributes, onReplace, name } = this.props;
		onReplace( blocks.map( ( block, index ) => (
			index === 0 && block.name === name ?
				{ ...block,
					attributes: {
						...attributes,
						...block.attributes,
					},
				} :
				block
		) ) );
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
			align,
			ampFontFamily,
			ampFitText,
			autoFontSize,
			height,
			width,
			tagName,
			opacity,
		} = attributes;

		const minTextHeight = 20;
		const minTextWidth = 30;
		let userFontSize = fontSize.size ? fontSize.size + 'px' : undefined;
		if ( undefined === userFontSize ) {
			if ( 'h1' === tagName ) {
				userFontSize = 2 + 'rem';
			} else if ( 'h2' === tagName ) {
				userFontSize = 1.5 + 'rem';
			}
		}

		const [ r, g, b, a ] = getRgbaFromHex( backgroundColor.color, opacity );

		return (
			<Fragment>
				<BlockControls>
					<AlignmentToolbar
						value={ align }
						onChange={ ( value ) => setAttributes( { align: value } ) }
					/>
				</BlockControls>
				<InspectorControls>
					<PanelBody title={ __( 'Text Settings', 'amp' ) }>
						<FontFamilyPicker
							value={ ampFontFamily }
							onChange={ ( value ) => {
								maybeEnqueueFontStyle( value );
								setAttributes( { ampFontFamily: value } );
							} }
						/>
						<ToggleControl
							label={ __( 'Automatically fit text to container', 'amp' ) }
							checked={ ampFitText }
							onChange={ () => ( setAttributes( { ampFitText: ! ampFitText } ) ) }
						/>
						{ ! ampFitText && (
							<FontSizePicker
								value={ fontSize.size }
								onChange={ setFontSize }
							/>
						) }
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
								onChange: ( value ) => {
									setAttributes( { backgroundHexValue: value } );
									setBackgroundColor( value );
								},
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
						<RangeControl
							label={ __( 'Background Opacity', 'amp' ) }
							value={ opacity }
							onChange={ ( value ) => setAttributes( { opacity: value } ) }
							min={ 5 }
							max={ 100 }
							step={ 5 }
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
						// Ensure line breaks are normalised to HTML.
						value={ content }
						onChange={ ( nextContent ) => setAttributes( { content: nextContent } ) }
						onReplace={ this.onReplace }
						style={ {
							backgroundColor: ( backgroundColor.color && 100 !== opacity ) ? `rgba( ${ r }, ${ g }, ${ b }, ${ a })` : backgroundColor.color,
							color: textColor.color,
							fontSize: ampFitText ? autoFontSize : userFontSize,
							fontWeight: 'h1' === tagName || 'h2' === tagName ? 700 : 'normal',
							textAlign: align,
						} }
						className={ classnames( className, {
							'has-text-color': textColor.color,
							'has-background': backgroundColor.color,
							[ backgroundColor.class ]: backgroundColor.class,
							[ textColor.class ]: textColor.class,
							[ fontSize.class ]: autoFontSize ? undefined : fontSize.class,
							'is-amp-fit-text': ampFitText,
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
