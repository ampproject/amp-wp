/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { useState, useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

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
const PageAttachmentEdit = ( {
	attributes,
	setAttributes,
	backgroundColor,
	customBackgroundColor,
	textColor,
} ) => {
	const {
		opacity,
		openText,
	} = attributes;

	const [ isOpen, setIsOpen ] = useState( false );

	const colors = useSelect( ( select ) => {
		const { getSettings } = select( 'core/block-editor' );
		const settings = getSettings();

		return settings.colors;
	} );

	const getWrapperAttributes = () => {
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
	};

	useEffect( () => {
		const { style, attachmentClass } = getWrapperAttributes();
		const newAttributes = { wrapperStyle: style };
		newAttributes.attachmentClass = attachmentClass;
		setAttributes( newAttributes );
	}, [ backgroundColor, customBackgroundColor, textColor ] );

	return (
		<>
			{ isOpen ?
				(
					<AttachmentContent
						setAttributes={ setAttributes }
						attributes={ attributes }
						toggleAttachment={ setIsOpen }
					/>
				) :
				(
					<AttachmentOpener
						setAttributes={ setAttributes }
						toggleAttachment={ setIsOpen }
						openText={ openText }
					/>
				)
			}
		</>
	);
};

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
