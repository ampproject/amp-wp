/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';
import { ENTER, SPACE } from '@wordpress/keycodes';

/**
 * Attachment opener that is displayed when the attachment hasn't been opened yet.
 *
 * Displays a form with an editable label that, when being clicked on, opens the attachment content.
 *
 * @param {Object} props Component props.
 * @param {Function} props.setAttributes
 * @param {Function} props.toggleAttachment
 * @param {string} props.openText
 * @return {ReactElement} Element.
 */
const AttachmentOpener = ( { setAttributes, toggleAttachment, openText } ) => {
	return (
		<div className="open-attachment-wrapper">
			<span
				role="button"
				tabIndex="0"
				onClick={ () => {
					toggleAttachment( true );
				} }
				onKeyDown={ ( event ) => {
					if ( ENTER === event.keyCode || SPACE === event.keyCode ) {
						toggleAttachment( true );
						event.stopPropagation();
					}
				} }
				className="amp-story-page-open-attachment-icon"
			>
				<span className="amp-story-page-open-attachment-bar amp-story-page-open-attachment-bar-left" />
				<span className="amp-story-page-open-attachment-bar amp-story-page-open-attachment-bar-right" />
			</span>
			<RichText
				value={ openText }
				tagName="div"
				className="amp-story-page-attachment__text"
				onChange={ ( value ) => setAttributes( { openText: value } ) }
				placeholder={ __( 'Swipe Up', 'amp' ) }
			/>
		</div>
	);
};

AttachmentOpener.propTypes = {
	toggleAttachment: PropTypes.func.isRequired,
	openText: PropTypes.string,
	setAttributes: PropTypes.func.isRequired,
};

export default AttachmentOpener;
