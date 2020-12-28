/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { BlockControls } from '@wordpress/block-editor';
import { ToolbarButton } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { ToolbarIcon } from './icon';
import { PLUGIN_NAME, SIDEBAR_NAME } from '.';

/**
 * AMP button displaying in the block toolbar.
 *
 * @param {Object} props Component props.
 * @param {string} props.clientId Block Client ID.
 * @param {number} props.count The number of errors associated with the block.
 */
export function AMPToolbarButton( { clientId, count } ) {
	const { openGeneralSidebar } = useDispatch( 'core/edit-post' );

	return (
		<BlockControls>
			<ToolbarButton
				onClick={ () => {
					openGeneralSidebar( `${ PLUGIN_NAME }/${ SIDEBAR_NAME }` );
					// eslint-disable-next-line @wordpress/react-no-unsafe-timeout
					setTimeout( () => {
						document.querySelector( `.error-${ clientId } button` )?.click();
					} );
				} }
			>
				<ToolbarIcon count={ count } />
			</ToolbarButton>
		</BlockControls>
	);
}
AMPToolbarButton.propTypes = {
	clientId: PropTypes.string.isRequired,
	count: PropTypes.number.isRequired,
};
