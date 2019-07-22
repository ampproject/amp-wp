/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';
import { isEqual } from 'lodash';

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
import { maybeUpdateFontSize, maybeUpdateBlockDimensions } from '../../helpers';
import { getBackgroundColorWithOpacity } from '../../../common/helpers';
import './edit.css';

class TextBlockEdit extends Component {
	constructor() {
		super( ...arguments );

		this.state = {
			isEditing: false,
		};

		this.onReplace = this.onReplace.bind( this );
		this.toggleIsEditing = this.toggleIsEditing.bind( this );
	}

	componentDidMount() {
		maybeUpdateFontSize( this.props );
	}

	componentDidUpdate( prevProps ) {
		const { attributes, fontSize, isSelected } = this.props;
		const {
			height,
			width,
			content,
			ampFitText,
			ampFontFamily,
		} = attributes;

		// If the block was unselected, make sure that it's not editing anymore.
		if ( ! isSelected && prevProps.isSelected ) {
			this.toggleIsEditing( false );
		}

		const checkFontSize = ampFitText && (
			prevProps.attributes.ampFitText !== ampFitText ||
			prevProps.attributes.ampFontFamily !== ampFontFamily ||
			prevProps.attributes.width !== width ||
			prevProps.attributes.height !== height ||
			prevProps.attributes.content !== content
		);

		if ( checkFontSize ) {
			maybeUpdateFontSize( this.props );
		}

		const checkBlockDimensions = ! ampFitText && (
			! isEqual( prevProps.fontSize, fontSize ) ||
			prevProps.attributes.ampFitText !== ampFitText ||
			prevProps.attributes.ampFontFamily !== ampFontFamily ||
			prevProps.attributes.content !== content
		);

		if ( checkBlockDimensions ) {
			maybeUpdateBlockDimensions( this.props );
		}
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

	toggleIsEditing( enable ) {
		if ( enable !== this.state.isEditing ) {
			this.setState( {
				isEditing: ! this.state.isEditing,
			} );
		}
	}

	render() {
		const { isEditing } = this.state;

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
			opacity,
		} = attributes;

		const userFontSize = fontSize && fontSize.size ? fontSize.size + 'px' : undefined;

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

		const textWrapperClassName = 'wp-block-amp-story-text';

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
					{ isEditing &&
						<RichText
							wrapperClassName={ textWrapperClassName }
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
					}
					{ ! isEditing &&
						<div
							className={ textWrapperClassName }
							onDoubleClick={ () => {
								this.toggleIsEditing( true );
							} }
						>
							<p
								className={ classnames( className, {
									'has-text-color': textColor.color,
									[ textColor.class ]: textColor.class,
									[ fontSize.class ]: ampFitText ? undefined : fontSize.class,
									'is-amp-fit-text': ampFitText,
								} ) }
								style={ {
									color: textColor.color,
									fontSize: ampFitText ? autoFontSize + 'px' : userFontSize,
									textAlign: align,
									position: ampFitText && content.length ? 'static' : undefined,
								} }
							>
								{ content }
							</p>
						</div>
					}
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
		className: PropTypes.string,
		ampFontFamily: PropTypes.string,
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
		class: PropTypes.string,
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
