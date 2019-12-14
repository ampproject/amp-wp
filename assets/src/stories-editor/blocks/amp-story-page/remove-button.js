/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	PanelBody,
	Button,
} from '@wordpress/components';
import { useDispatch, useSelect } from '@wordpress/data';
import { useCallback } from '@wordpress/element';

/**.
 * Add a simple remove page link to sidebar.
 *
 * @param {Object} props Component props.
 * @param {string} props.clientId Client ID.
 */
const RemoveButton = ( { clientId } ) => {
	const { removeBlock } = useDispatch( 'core/block-editor' );
	const pages = useSelect( ( select ) => select( 'core/block-editor' ).getBlockOrder(), [] );

	const removePage = useCallback(
		() => removeBlock( clientId ),
		[ clientId, removeBlock ],
	);

	// Shouldn't allow users to remove the first and only page.
	if ( pages.length < 2 ) {
		return null;
	}

	return (
		<PanelBody className="editor-amp-story-remove-page">
			<Button isLink isDestructive onClick={ removePage } >
				{ __( 'Remove', 'amp' ) }
			</Button>
		</PanelBody>
	);
};

RemoveButton.propTypes = {
	clientId: PropTypes.string.isRequired,
};

export default RemoveButton;
