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

class CallToActionEdit extends Component {
	constructor( props ) {
		super( ...arguments );

		if ( ! props.attributes.anchor ) {
			this.props.setAttributes( { anchor: getUniqueId() } );
		}

		this.nodeRef = null;
		this.bindRef = this.bindRef.bind( this );
	}

	bindRef( node ) {
		if ( ! node ) {
			return;
		}
		this.nodeRef = node;
	}

	render() {
		const {
			attributes,
			backgroundColor,
			textColor,
			setAttributes,
			isSelected,
			className,
			fontSize,
		} = this.props;

		const {
			text,
			url,
			customBackgroundColor,
			opacity,
		} = attributes;

		const { colors } = select( 'core/block-editor' ).getSettings();
		const appliedBackgroundColor = getBackgroundColorWithOpacity( colors, backgroundColor, customBackgroundColor, opacity );

		return (
			<>
				<div className={ className } ref={ this.bindRef }>
					<RichText
						placeholder={ __( 'Add text…', 'amp' ) }
						value={ text }
						onChange={ ( value ) => setAttributes( { text: value } ) }
						formattingControls={ [ 'bold', 'italic', 'strikethrough' ] }
						className={ classnames(
							'amp-block-story-cta__link', {
								'has-background': backgroundColor.color,
								[ backgroundColor.class ]: backgroundColor.class,
								'has-text-color': textColor.color,
								[ textColor.class ]: textColor.class,
							}
						) }
						style={ {
							backgroundColor: appliedBackgroundColor,
							color: textColor.color,
							fontSize: fontSize.size ? fontSize.size + 'px' : undefined,
						} }
					/>
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
			</>
		);
	}
}

CallToActionEdit.propTypes = {
	attributes: PropTypes.shape( {
		text: PropTypes.string,
		url: PropTypes.string,
		anchor: PropTypes.string,
	} ).isRequired,
	setAttributes: PropTypes.func.isRequired,
	isSelected: PropTypes.bool,
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
	textColor: PropTypes.shape( {
		color: PropTypes.string,
		name: PropTypes.string,
		slug: PropTypes.string,
		class: PropTypes.string,
	} ).isRequired,
};

export default CallToActionEdit;
