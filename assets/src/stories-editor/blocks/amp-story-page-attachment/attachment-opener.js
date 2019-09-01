/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { RichText } from '@wordpress/block-editor';
import { ENTER, SPACE } from '@wordpress/keycodes';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

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
				tagName="span"
				wrapperClassName="amp-story-page-attachment__text"
				onChange={ ( value ) => setAttributes( { openText: value } ) }
				placeholder={ __( 'Write CTA Text', 'amp' ) }
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
