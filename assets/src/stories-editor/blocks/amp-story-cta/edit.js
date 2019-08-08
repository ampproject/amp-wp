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
	Dashicon,
	IconButton,
} from '@wordpress/components';
import {
	URLInput,
	RichText,
} from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import './edit.css';
import { select } from '@wordpress/data';
import { getUniqueId } from '../../helpers';
import { getBackgroundColorWithOpacity } from '../../../common/helpers';
import { StoryBlockMover } from '../../components';

class CallToActionEdit extends Component {
	constructor( props ) {
		super( props );

		if ( ! props.attributes.anchor ) {
			this.props.setAttributes( { anchor: getUniqueId() } );
		}

		this.state = {
			isEditing: false,
			hasOverlay: true,
		};

		this.toggleIsEditing = this.toggleIsEditing.bind( this );
		this.toggleOverlay = this.toggleOverlay.bind( this );

		this.nodeRef = null;
		this.bindRef = this.bindRef.bind( this );
	}

	bindRef( node ) {
		if ( ! node ) {
			return;
		}
		this.nodeRef = node;
	}

	toggleIsEditing( enable ) {
		if ( enable !== this.state.isEditing ) {
			this.setState( {
				isEditing: ! this.state.isEditing,
			} );
		}
	}

	toggleOverlay( add ) {
		if ( add !== this.state.hasOverlay ) {
			this.setState( {
				hasOverlay: ! this.state.hasOverlay,
			} );
		}
	}

	componentDidUpdate( prevProps, prevState ) {
		const { isSelected } = this.props;
		// If the block was unselected, make sure that it's not editing anymore.
		if ( ! isSelected && prevProps.isSelected ) {
			this.toggleIsEditing( false );
			this.toggleOverlay( true );
		}
		if ( this.state.isEditing && ! prevState.isEditing ) {
			const textInput = document.querySelector( '.is-selected .amp-block-story-cta__link' );
			if ( textInput ) {
				// Create selection, collapse it in the end of the content.
				const range = document.createRange();
				range.selectNodeContents( textInput );
				range.collapse( false );
				const selection = window.getSelection();
				selection.removeAllRanges();
				selection.addRange( range );
			}
		}
	}

	render() {
		const {
			attributes,
			backgroundColor,
			className,
			clientId,
			fontSize,
			isSelected,
			name,
			setAttributes,
			textColor,
		} = this.props;

		const {
			text,
			url,
			customBackgroundColor,
			opacity,
			btnPositionTop,
			btnPositionLeft,
		} = attributes;

		const { isEditing, hasOverlay } = this.state;

		const { colors } = select( 'core/block-editor' ).getSettings();
		const appliedBackgroundColor = getBackgroundColorWithOpacity( colors, backgroundColor, customBackgroundColor, opacity );

		const placeholder = __( 'Add text…', 'amp' );
		const textWrapperClass = classnames(
			'amp-block-story-cta__link', {
				'has-background': backgroundColor.color,
				'has-text-color': textColor.color,
				[ textColor.class ]: textColor.class,
			}
		);
		const textStyle = {
			color: textColor.color,
			fontSize: fontSize.size ? fontSize.size + 'px' : undefined,
		};
		return (
			<>
				<StoryBlockMover
					clientId={ clientId }
					blockName={ name }
					blockElementId={ `amp-story-cta-button-${ clientId }` }
					isDraggable={ ! isEditing }
					isMovable={ true }
				>
					<div className="amp-story-cta-button" id={ `amp-story-cta-button-${ clientId }` } style={ { top: `${ btnPositionTop }%`, left: `${ btnPositionLeft }%` } } >
						<div className={ className } ref={ this.bindRef } style={ { backgroundColor: appliedBackgroundColor } }>
							{ isEditing && (
								<RichText
									placeholder={ placeholder }
									value={ text }
									onChange={ ( value ) => setAttributes( { text: value } ) }
									className={ textWrapperClass }
									style={ textStyle }
								/>
							) }
							{ /* eslint-disable-next-line jsx-a11y/click-events-have-key-events */ }
							{ ! isEditing && <div
								role="textbox"
								tabIndex="-1"
								className="is-not-editing editor-rich-text block-editor-rich-text"
								onClick={ () => {
									if ( isSelected ) {
										this.toggleIsEditing( true );
									}
								} }
								onMouseDown={ ( event ) => {
									// Prevent text selection on double click.
									if ( 1 < event.detail ) {
										event.preventDefault();
									}
								} }
							>
								{ /* eslint-disable-next-line jsx-a11y/click-events-have-key-events */ }
								{ hasOverlay && ( <div
									role="textbox"
									tabIndex="-1"
									className="amp-overlay"
									onClick={ ( e ) => {
										this.toggleOverlay( false );
										e.stopPropagation();
									} }
								></div>
								) }
								<div
									role="textbox"
									className={ textWrapperClass }
									style={ textStyle }>
									{ text && text.length ? text : (
										<span className="amp-text-placeholder">
											{ placeholder }
										</span>
									) }
								</div>
							</div>
							}
						</div>
						{ isSelected && (
							<form
								className="amp-block-story-cta__inline-link"
								onSubmit={ ( event ) => event.preventDefault() }>
								<Dashicon icon="admin-links" />
								<URLInput
									value={ url }
									onChange={ ( value ) => setAttributes( { url: value } ) }
									autoFocus={ false /* eslint-disable-line jsx-a11y/no-autofocus */ }
								/>
								<IconButton icon="editor-break" label={ __( 'Apply', 'amp' ) } type="submit" />
							</form>
						) }
					</div>
				</StoryBlockMover>
			</>
		);
	}
}

CallToActionEdit.propTypes = {
	attributes: PropTypes.shape( {
		text: PropTypes.string,
		url: PropTypes.string,
		anchor: PropTypes.string,
		customBackgroundColor: PropTypes.string,
		opacity: PropTypes.number,
		btnPositionLeft: PropTypes.number,
		btnPositionTop: PropTypes.number,
	} ).isRequired,
	setAttributes: PropTypes.func.isRequired,
	isSelected: PropTypes.bool,
	className: PropTypes.string,
	clientId: PropTypes.string,
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
	name: PropTypes.string.isRequired,
	textColor: PropTypes.shape( {
		color: PropTypes.string,
		name: PropTypes.string,
		slug: PropTypes.string,
		class: PropTypes.string,
	} ).isRequired,
};

export default CallToActionEdit;
