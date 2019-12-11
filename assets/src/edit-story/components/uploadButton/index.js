/**
 * WordPress dependencies
 */
import { useEffect } from '@wordpress/element';
/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

const Button = styled.button`
	 background: ${ ( { theme } ) => theme.colors.bg.v3 };
	 color: ${ ( { theme } ) => theme.colors.fg.v1 };
	 padding: 5px;
	 font-weight: bold;
	 flex: 1 0 0;
	 text-align: center;
	 border: 0px none;
`;

function UploadButton( {
	mediaType,
	title,
	buttonText,
	buttonInsertText,
	multiple,
	onSelect,
	onClose,
} ) {
	useEffect( () => {
		// Work around that forces default tab as upload tab.
		wp.media.controller.Library.prototype.defaults.contentUserSetting = false;
	} );

	const mediaPicker = () => {
		// Create the media frame.
		const fileFrame = wp.media( {
			title,
			button: {
				text: buttonInsertText,
			},
			multiple,
			library: {
				type: mediaType,
			},
		} );
		let attachment;

		// When an image is selected, run a callback.
		fileFrame.on( 'select', () => {
			attachment = fileFrame.state().get( 'selection' ).first().toJSON();
			onSelect( attachment );
		} );

		fileFrame.on( 'close', onClose );

		// Finally, open the modal
		fileFrame.open();
	};

	return (
		<Button onClick={ mediaPicker }>
			{ buttonText }
		</Button>
	);
}

UploadButton.propTypes = {
	mediaType: PropTypes.string.isRequired,
	title: PropTypes.string,
	buttonInsertText: PropTypes.string,
	buttonText: PropTypes.string,
	multiple: PropTypes.bool,
	onSelect: PropTypes.func.isRequired,
	onClose: PropTypes.func.isRequired,
};

UploadButton.defaultProps = {
	title: 'Upload to Story',
	buttonText: 'Upload',
	buttonInsertText: 'Insert into page',
	multiple: false,
};

export default UploadButton;
