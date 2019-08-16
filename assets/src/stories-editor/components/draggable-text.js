/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { RawHTML } from '@wordpress/element';
import { ENTER } from '@wordpress/keycodes';

/**
 * Internal dependencies
 */
import { StoryBlockMover } from './index';

/**
 * Draggable Text: a non-editable text content which can be dragged.
 * Switches to edit mode when clicked twice.
 *
 * @param {Object} props Component props.
 *
 * @return {WPElement} Rendered element.
 */
const DraggableText = ( props ) => {
	const {
		blockClass,
		blockElementId,
		clientId,
		hasOverlay,
		isDraggable,
		isSelected,
		name,
		toggleIsEditing,
		toggleOverlay,
		text,
		textStyle,
		textWrapperClass,
		placeholder,
	} = props;
	return (
		<StoryBlockMover
			clientId={ clientId }
			blockName={ name }
			blockElementId={ blockElementId }
			isDraggable={ isDraggable }
			isMovable={ true }
		>
			<div
				role="textbox"
				tabIndex="-1"
				className={ classnames( 'is-not-editing', 'editor-rich-text', 'block-editor-rich-text', blockClass ) }
				onClick={ () => {
					if ( isSelected ) {
						toggleIsEditing( true );
					}
				} }
				onMouseDown={ ( event ) => {
					// Prevent text selection on double click.
					if ( 1 < event.detail ) {
						event.preventDefault();
					}
				} }
				onKeyDown={ ( event ) => {
					event.stopPropagation();
					if ( ENTER === event.keyCode && isSelected ) {
						toggleOverlay( false );
						toggleIsEditing( true );
					}
				} }
			>
				{ hasOverlay && ( <div
					role="textbox"
					tabIndex="-1"
					className="amp-overlay"
					onClick={ ( e ) => {
						toggleOverlay( false );
						e.stopPropagation();
					} }
					onKeyDown={ ( event ) => {
						event.stopPropagation();
						if ( ENTER === event.keyCode && isSelected ) {
							toggleOverlay( false );
							toggleIsEditing( true );
						}
					} }
				></div>
				) }
				<div
					role="textbox"
					className={ textWrapperClass }
					style={ textStyle }>
					{ text && text.length ?
						<RawHTML>{ text }</RawHTML> : (
							<span className="amp-text-placeholder">
								{ placeholder }
							</span>
						) }
				</div>
			</div>
		</StoryBlockMover>
	);
};

DraggableText.propTypes = {
	clientId: PropTypes.string.isRequired,
	name: PropTypes.string.isRequired,
	blockClass: PropTypes.string,
	blockElementId: PropTypes.string.isRequired,
	hasOverlay: PropTypes.bool.isRequired,
	isDraggable: PropTypes.bool.isRequired,
	isSelected: PropTypes.bool.isRequired,
	toggleIsEditing: PropTypes.func.isRequired,
	toggleOverlay: PropTypes.func.isRequired,
	text: PropTypes.string.isRequired,
	textStyle: PropTypes.shape( {
		color: PropTypes.string,
		fontSize: PropTypes.string,
		textAlign: PropTypes.string,
		position: PropTypes.string,
	} ).isRequired,
	textWrapperClass: PropTypes.string.isRequired,
	placeholder: PropTypes.string.isRequired,
};

export default DraggableText;
