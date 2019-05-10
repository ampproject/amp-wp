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
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { calculateFontSize } from '../../helpers';
import { getBackgroundColorWithOpacity } from '../../../common/helpers';
import { MIN_FONT_SIZE, MAX_FONT_SIZE } from '../../constants';
import './edit.css';

class TextBlockEdit extends Component {
	constructor() {
		super( ...arguments );

		this.onReplace = this.onReplace.bind( this );
	}

	componentDidUpdate( prevProps ) {
		const { clientId, attributes, isSelected, setAttributes } = this.props;
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
			// Check if the font size is OK, if not, update the font size.
			const element = document.querySelector( `#block-${ clientId } .block-editor-rich-text__editable` );
			if ( element ) {
				const fitFontSize = calculateFontSize( element, height, width, MAX_FONT_SIZE, MIN_FONT_SIZE );
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
			customBackgroundColor,
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

		const { colors } = select( 'core/block-editor' ).getSettings();
		const appliedBackgroundColor = getBackgroundColorWithOpacity( colors, backgroundColor, customBackgroundColor, opacity );

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
					// The 2 following lines are necessary for pasting to work.
					onReplace={ this.onReplace }
					onSplit={ () => {} }
					style={ {
						backgroundColor: appliedBackgroundColor,
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
