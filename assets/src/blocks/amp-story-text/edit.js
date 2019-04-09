/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import { SelectControl } from '@wordpress/components';
import { compose } from '@wordpress/compose';
import {
	RichText,
	InspectorControls,
	withFontSizes,
	withColors,
	BlockControls,
	AlignmentToolbar,
} from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { ColorSettings, TextSettings, ResizableBox } from '../../components';
import { maybeEnqueueFontStyle, calculateFontSize, getRgbaFromHex } from '../../helpers';
import './edit.css';

const maxLimitFontSize = 54;
const minLimitFontSize = 14;

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
			customFontSize,
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
					<TextSettings
						fontFamily={ ampFontFamily }
						setFontFamily={ ( value ) => {
							maybeEnqueueFontStyle( value );
							setAttributes( { ampFontFamily: value } );
						} }
						fontSize={ fontSize }
						setFontSize={ setFontSize }
						customFontSize={ customFontSize }
						fitText={ ampFitText }
						setFitText={ () => ( setAttributes( { ampFitText: ! ampFitText } ) ) }
					>
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
					</TextSettings>
					<ColorSettings
						backgroundColor={ backgroundColor }
						setBackgroundColor={ setBackgroundColor }
						textColor={ textColor }
						setTextColor={ setTextColor }
						fontSize={ fontSize }
						opacity={ opacity }
						setOpacity={ ( value ) => setAttributes( { opacity: value } ) }
					/>
				</InspectorControls>
				<ResizableBox
					isSelected={ isSelected }
					width={ width }
					height={ height }
					minHeight={ minTextHeight }
					minWidth={ minTextWidth }
					onResizeStop={ ( value ) => {
						setAttributes( value );
						toggleSelection( true );
					} }
					onResizeStart={ () => {
						toggleSelection( false );
					} }
				>
					<RichText
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
)( TextBlockEdit );
