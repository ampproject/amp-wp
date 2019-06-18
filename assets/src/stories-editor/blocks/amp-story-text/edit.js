/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import {
	RichText,
	BlockControls,
	AlignmentToolbar,
} from '@wordpress/block-editor';
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { maybeUpdateFontSize } from '../../helpers';
import { getBackgroundColorWithOpacity } from '../../../common/helpers';
import './edit.css';

class TextBlockEdit extends Component {
	constructor() {
		super( ...arguments );

		this.onReplace = this.onReplace.bind( this );
	}

	componentDidMount() {
		maybeUpdateFontSize( this.props );
	}

	componentDidUpdate( prevProps ) {
		const { attributes } = this.props;
		const {
			height,
			width,
			content,
		} = attributes;

		if (
			prevProps.attributes.height === height &&
			prevProps.attributes.width === width &&
			prevProps.attributes.content === content
		) {
			return;
		}

		maybeUpdateFontSize( this.props );
	}

	onReplace( blocks ) {
		const { attributes, onReplace, name } = this.props;
		// Make sure that 'undefined' values aren't passed into onReplace.
		blocks = blocks.filter( ( block ) => 'undefined' !== typeof block );
		if ( ! blocks.length ) {
			return;
		}
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
			height,
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

		const wrapperStyle = { backgroundColor: appliedBackgroundColor };
		if ( ampFitText && content.length ) {
			wrapperStyle.lineHeight = height + 'px';
		}

		const styleClasses = [];
		let wrapperClass = 'wp-block-amp-story-text-wrapper';

		// We need to assign the block styles to the wrapper, too.
		if ( attributes.className && attributes.className.length ) {
			const classNames = attributes.className.split( ' ' );
			classNames.forEach( ( value ) => {
				if ( value.includes( 'is-style' ) ) {
					styleClasses.push( value );
				}
			} );
		}

		if ( styleClasses.length ) {
			wrapperClass += ' ' + styleClasses.join( ' ' );
		}

		return (
			<>
				<BlockControls>
					<AlignmentToolbar
						value={ align }
						onChange={ ( value ) => setAttributes( { align: value } ) }
					/>
				</BlockControls>
				<div className={ classnames( wrapperClass, {
					'with-line-height': ampFitText,
				} ) } style={ wrapperStyle } >
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
							color: textColor.color,
							fontSize: ampFitText ? autoFontSize + 'px' : userFontSize,
							textAlign: align,
							position: ampFitText && content.length ? 'static' : undefined,
						} }
						className={ classnames( className, {
							'has-text-color': textColor.color,
							[ textColor.class ]: textColor.class,
							[ fontSize.class ]: ampFitText ? undefined : fontSize.class,
							'is-amp-fit-text': ampFitText,
						} ) }
						placeholder={ placeholder || __( 'Write text…', 'amp' ) }
					/>
				</div>
			</>
		);
	}
}

TextBlockEdit.propTypes = {
	attributes: PropTypes.shape( {
		width: PropTypes.number,
		height: PropTypes.number,
		placeholder: PropTypes.string,
		content: PropTypes.string,
		align: PropTypes.string,
		ampFitText: PropTypes.bool,
		autoFontSize: PropTypes.number,
		tagName: PropTypes.string,
		opacity: PropTypes.number,
	} ).isRequired,
	isSelected: PropTypes.bool.isRequired,
	onReplace: PropTypes.func.isRequired,
	name: PropTypes.string.isRequired,
	setAttributes: PropTypes.func.isRequired,
	className: PropTypes.string,
	fontSize: PropTypes.shape( {
		name: PropTypes.string,
		shortName: PropTypes.string,
		size: PropTypes.number,
		slug: PropTypes.string,
	} ).isRequired,
	backgroundColor: PropTypes.shape( {
		color: PropTypes.string,
		name: PropTypes.string,
		slug: PropTypes.string,
		class: PropTypes.string,
	} ).isRequired,
	customBackgroundColor: PropTypes.string,
	textColor: PropTypes.shape( {
		color: PropTypes.string,
		name: PropTypes.string,
		slug: PropTypes.string,
		class: PropTypes.string,
	} ).isRequired,
};

export default TextBlockEdit;
