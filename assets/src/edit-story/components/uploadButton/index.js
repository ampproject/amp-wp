/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

function UploadButton( {
	title,
	buttonText,
	buttonCSS,
	buttonInsertText,
	multiple,
	onSelect,
	onClose,
	type,
} ) {
	useEffect( () => {
		// Work around that forces default tab as upload tab.
		wp.media.controller.Library.prototype.defaults.contentUserSetting = false;
	} );

	const mediaPicker = ( evt ) => {
		// Create the media frame.
		const fileFrame = wp.media( {
			title,
			library: {
				type,
			},
			button: {
				text: buttonInsertText,
			},
			multiple,
		} );
		let attachment;

		// When an image is selected, run a callback.
		fileFrame.on( 'select', () => {
			attachment = fileFrame.state().get( 'selection' ).first().toJSON();
			onSelect( attachment );
		} );

		if ( onClose ) {
			fileFrame.on( 'close', onClose );
		}

		// Finally, open the modal
		fileFrame.open();

		evt.preventDefault();
	};

	const Button = styled.button`
		${ buttonCSS }
	`;

	return (
		<Button onClick={ mediaPicker }>
			{ buttonText }
		</Button>
	);
}

UploadButton.propTypes = {
	title: PropTypes.string,
	buttonInsertText: PropTypes.string,
	multiple: PropTypes.bool,
	onSelect: PropTypes.func.isRequired,
	onClose: PropTypes.func,
	type: PropTypes.string,
	buttonCSS: PropTypes.array,
	buttonText: PropTypes.string.isRequired,
};

UploadButton.defaultProps = {
	title: __( 'Upload to Story', 'amp' ),
	buttonText: __( 'Upload', 'amp' ),
	buttonInsertText: __( 'Insert into page', 'amp' ),
	multiple: false,
	type: '',
};

export default UploadButton;
