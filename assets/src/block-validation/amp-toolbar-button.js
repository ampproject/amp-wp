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
 * @param {number} props.count The number of errors associated with the block.
 */
export function AMPToolbarButton( { count } ) {
	const { openGeneralSidebar } = useDispatch( 'core/edit-post' );

	return (
		<BlockControls>
			<ToolbarButton
				onClick={ () => {
					openGeneralSidebar( `${ PLUGIN_NAME }/${ SIDEBAR_NAME }` );
				} }
			>
				<ToolbarIcon count={ count } />
			</ToolbarButton>
		</BlockControls>
	);
}
AMPToolbarButton.propTypes = {
	count: PropTypes.number.isRequired,
};
