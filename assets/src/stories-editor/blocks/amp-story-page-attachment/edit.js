/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';
import { select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import './edit.css';
import { getBackgroundColorWithOpacity } from '../../../common/helpers';
import AttachmentOpener from './attachment-opener';
import AttachmentContent from './attachment-content';

/**
 * Edit component for the page attachment block rendered in the editor.
 */
class PageAttachmentEdit extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			isOpen: false,
		};

		this.toggleAttachment = this.toggleAttachment.bind( this );
	}

	componentDidUpdate( prevProps ) {
		const {
			backgroundColor,
			customBackgroundColor,
			textColor,
			setAttributes,
		} = this.props;

		if (
			backgroundColor !== prevProps.backgroundColor ||
			customBackgroundColor !== prevProps.customBackgroundColor ||
			textColor !== prevProps.textColor
		) {
			const { style, attachmentClass } = this.getWrapperAttributes();
			const newAttributes = { wrapperStyle: style };
			if ( textColor !== prevProps.textColor ) {
				newAttributes.attachmentClass = attachmentClass;
			}
			setAttributes( newAttributes );
		}
	}

	toggleAttachment( open ) {
		if ( open !== this.state.isOpen ) {
			this.setState( { isOpen: open } );
		}
	}

	getWrapperAttributes() {
		const {
			attributes,
			backgroundColor,
			customBackgroundColor,
			textColor,
		} = this.props;

		const {
			opacity,
		} = attributes;
		const { colors } = select( 'core/block-editor' ).getSettings();
		const appliedBackgroundColor = getBackgroundColorWithOpacity( colors, backgroundColor, customBackgroundColor, opacity );

		const attachmentClass = classnames( 'amp-page-attachment-content', {
			'has-text-color': textColor.color,
			[ textColor.class ]: textColor.class,
		} );
		const attachmentStyle = {
			color: textColor.color,
			backgroundColor: appliedBackgroundColor,
		};
		return {
			style: attachmentStyle,
			attachmentClass,
		};
	}

	render() {
		const {
			attributes,
			setAttributes,
		} = this.props;

		const {
			openText,
		} = attributes;

		return (
			<>
				{ this.state.isOpen &&
					<AttachmentContent
						setAttributes={ setAttributes }
						attributes={ attributes }
						toggleAttachment={ this.toggleAttachment }
					/>
				}
				{ ! this.state.isOpen &&
					<AttachmentOpener
						setAttributes={ setAttributes }
						toggleAttachment={ this.toggleAttachment }
						openText={ openText }
					/>
				}
			</>
		);
	}
}

PageAttachmentEdit.propTypes = {
	attributes: PropTypes.shape( {
		opacity: PropTypes.number,
		openText: PropTypes.string,
	} ).isRequired,
	setAttributes: PropTypes.func.isRequired,
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

export default PageAttachmentEdit;
