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
	RichText,
	BlockControls,
	AlignmentToolbar,
} from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { calculateFontSize } from '../../helpers';
import { getRgbaFromHex } from '../../../common/helpers';
import './edit.css';

const maxLimitFontSize = 54;
const minLimitFontSize = 14;

class TextBlockEdit extends Component {
	constructor() {
		super( ...arguments );

		this.onReplace = this.onReplace.bind( this );
	}

	componentDidUpdate( prevProps ) {
		const { attributes, isSelected, setAttributes } = this.props;
		const {
			height,
			width,
			autoFontSize,
			ampFitText,
		} = attributes;

		// If not selected, only proceed if height or width has changed.
		if (
			! isSelected &&
			prevProps.attributes.height === height &&
			prevProps.attributes.width === width
		) {
			return;
		}

		if ( ampFitText && attributes.content.length ) {
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
			backgroundColor,
			textColor,
		} = this.props;

		const {
			placeholder,
			content,
			align,
			ampFitText,
			autoFontSize,
			tagName,
			opacity,
		} = attributes;

		let userFontSize = fontSize && fontSize.size ? fontSize.size + 'px' : undefined;
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
			</Fragment>
		);
	}
}

export default TextBlockEdit;
