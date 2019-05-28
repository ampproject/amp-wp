/**
 * External dependencies
 */
import classnames from 'classnames';
import uuid from 'uuid/v4';

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
} from '@wordpress/editor';

/**
 * Internal dependencies
 */
import './edit.css';

class CallToActionEdit extends Component {
	constructor( props ) {
		super( ...arguments );

		if ( ! props.attributes.anchor ) {
			this.props.setAttributes( { anchor: uuid() } );
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
		} = attributes;

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
							backgroundColor: backgroundColor.color,
							color: textColor.color,
							fontSize: fontSize.size ? fontSize.size + 'px' : undefined,
						} }
						keepPlaceholderOnFocus
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
						/>
						<IconButton icon="editor-break" label={ __( 'Apply', 'amp' ) } type="submit" />
					</form>
				) }
			</>
		);
	}
}

export default CallToActionEdit;
