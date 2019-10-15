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

/**.
 * Add a simple remove page link to sidebar.
 *
 * @param {Object} props Component props.
 * @param {string} props.clientId Client ID.
 */
const RemovePageSetting = ( { clientId } ) => {
	const { removeBlock } = useDispatch( 'core/block-editor' );
	const pages = useSelect( ( select ) => select( 'core/block-editor' ).getBlockOrder(), [] );

	const removePage = () => {
		removeBlock( clientId );
	};

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

RemovePageSetting.propTypes = {
	clientId: PropTypes.string.isRequired,
};

export default RemovePageSetting;
