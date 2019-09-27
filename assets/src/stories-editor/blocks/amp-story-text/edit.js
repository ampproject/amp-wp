/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import {
	RichText,
	BlockControls,
	AlignmentToolbar,
} from '@wordpress/block-editor';
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { maybeUpdateFontSize, maybeUpdateBlockDimensions, setInputSelectionToEnd } from '../../helpers';
import { getBackgroundColorWithOpacity } from '../../../common/helpers';
import './edit.css';
import { DraggableText } from '../../components';

const TextBlockEdit = ( props ) => {
	const {
		attributes,
		setAttributes,
		className,
		clientId,
		fontSize,
		isPartOfMultiSelection,
		isSelected,
		backgroundColor,
		customBackgroundColor,
		textColor,
		name,
	} = props;
	const {
		placeholder,
		content,
		align,
		ampFontFamily,
		ampFitText,
		autoFontSize,
		width,
		height,
		opacity,
	} = attributes;

	const [ isEditing, setIsEditing ] = useState( false );
	const [ hasOverlay, setHasOverlay ] = useState( true );

	const toggleIsEditing = ( enable ) => {
		if ( enable !== isEditing ) {
			setIsEditing( ! isEditing );
		}
	};

	const toggleOverlay = ( add ) => {
		if ( add !== hasOverlay ) {
			setHasOverlay( ! hasOverlay );
		}
	};

	useEffect( () => {
		if ( isEditing ) {
			setInputSelectionToEnd( '.is-selected .wp-block-amp-amp-story-text' );
		}
	}, [ isEditing ] );

	useEffect( () => {
		if ( ampFitText ) {
			maybeUpdateFontSize( props );
		}
	}, [ ampFitText, ampFontFamily, width, height, content ] );

	useEffect( () => {
		if ( ! ampFitText ) {
			maybeUpdateBlockDimensions( props );
		}
	}, [ ampFitText, fontSize , ampFontFamily, content ] );

	useEffect( () => {
		// If the block was unselected, make sure that it's not editing anymore.
		if ( ! isSelected ) {
			toggleIsEditing( false );
			toggleOverlay( true );
		}
	}, [ isSelected ] );

	const onReplace = ( blocks ) => {
		// Make sure that 'undefined' values aren't passed into onReplace.
		blocks = blocks.filter( ( block ) => 'undefined' !== typeof block );

		if ( ! blocks.length ) {
			return;
		}

		props.onReplace( blocks.map( ( block, index ) => (
			index === 0 && block.name === name ?
				{ ...block,
					attributes: {
						...attributes,
						...block.attributes,
					},
				} :
				block
		) ) );
	};

	const userFontSize = fontSize && fontSize.size ? `${ fontSize.size }px` : undefined;

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
	const textClassNames = {
		'has-text-color': textColor.color,
		[ textColor.class ]: textColor.class,
		[ fontSize.class ]: ampFitText ? undefined : fontSize.class,
		'is-amp-fit-text': ampFitText,
	};
	const textStyle = {
		color: textColor.color,
		fontSize: ampFitText ? autoFontSize + 'px' : userFontSize,
		textAlign: align,
		position: ampFitText && content.length ? 'static' : undefined,
	};

	// StoryBlockMover is added here to the Text block since it depends on isEditing state.
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
				'is-empty-draggable-text': ! isEditing && ! content.length,
			} ) } style={ wrapperStyle } >
				{ isEditing &&
				<RichText
					wrapperClassName={ textWrapperClassName }
					tagName="p"
					// Ensure line breaks are normalised to HTML.
					value={ content }
					onChange={ ( nextContent ) => setAttributes( { content: nextContent } ) }
					// The 2 following lines are necessary for pasting to work.
					onReplace={ onReplace }
					onSplit={ () => {} }
					style={ textStyle }
					className={ classnames( className, textClassNames ) }
					placeholder={ placeholder || __( 'Write text…', 'amp' ) }
				/>
				}
				{ ! isEditing &&
				<DraggableText
					blockClass="wp-block-amp-story-text"
					blockElementId={ `block-${ clientId }` }
					clientId={ clientId }
					name={ name }
					isEditing={ isEditing }
					isDraggable={ ! isPartOfMultiSelection }
					isSelected={ isSelected }
					hasOverlay={ hasOverlay }
					toggleIsEditing={ toggleIsEditing }
					toggleOverlay={ toggleOverlay }
					text={ content }
					textStyle={ textStyle }
					textWrapperClass={ classnames( className + ' block-editor-rich-text__editable editor-rich-text__editable', textClassNames ) }
					placeholder={ placeholder || __( 'Write text…', 'amp' ) }
				/>
				}
			</div>
		</>
	);
};

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
	clientId: PropTypes.string.isRequired,
	isPartOfMultiSelection: PropTypes.bool,
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
