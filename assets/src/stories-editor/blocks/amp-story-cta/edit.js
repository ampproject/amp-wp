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
import { Dashicon, IconButton } from '@wordpress/components';
import { URLInput, RichText } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import './edit.css';
import { getUniqueId, setInputSelectionToEnd } from '../../helpers';
import { getBackgroundColorWithOpacity } from '../../../common/helpers';
import { DraggableText } from '../../components';

const CallToActionEdit = ( {
	attributes,
	backgroundColor,
	className,
	clientId,
	fontSize,
	isSelected,
	name,
	setAttributes,
	textColor,
} ) => {
	const {
		anchor,
		text,
		url,
		customBackgroundColor,
		opacity,
		btnPositionTop,
		btnPositionLeft,
	} = attributes;

	const [ isEditing, setIsEditing ] = useState( false );
	const [ hasOverlay, setHasOverlay ] = useState( true );

	useEffect( () => {
		if ( ! anchor ) {
			setAttributes( { anchor: getUniqueId() } );
		}
	}, [ anchor, setAttributes ] );

	useEffect( () => {
		// If the block was unselected, make sure that it's not editing anymore.
		if ( ! isSelected ) {
			setIsEditing( false );
			setHasOverlay( true );
		}
	}, [ isSelected ] );

	useEffect( () => {
		if ( isEditing ) {
			setInputSelectionToEnd( '.is-selected .amp-block-story-cta__link' );
		}
	}, [ isEditing ] );

	const colors = useSelect( ( select ) => {
		const { getSettings } = select( 'core/block-editor' );
		const settings = getSettings();

		return settings.colors;
	}, [] );

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
		<div className="amp-story-cta-button" id={ `amp-story-cta-button-${ clientId }` } style={ { top: `${ btnPositionTop }%`, left: `${ btnPositionLeft }%` } } >
			<div className={ className } style={ { backgroundColor: appliedBackgroundColor } }>
				{ isEditing ? (
					<RichText
						placeholder={ placeholder }
						value={ text }
						onChange={ ( value ) => setAttributes( { text: value } ) }
						className={ textWrapperClass }
						style={ textStyle }
					/>
				) : (
					<DraggableText
						blockElementId={ `amp-story-cta-button-${ clientId }` }
						clientId={ clientId }
						name={ name }
						isDraggable={ true }
						isEditing={ isEditing }
						isSelected={ isSelected }
						hasOverlay={ hasOverlay }
						toggleIsEditing={ setIsEditing }
						toggleOverlay={ setHasOverlay }
						text={ text }
						textStyle={ textStyle }
						textWrapperClass={ textWrapperClass }
						placeholder={ placeholder }
					/>
				) }
			</div>
			{ isSelected && isEditing && (
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
	);
};

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
