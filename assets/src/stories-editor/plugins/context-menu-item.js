/**
 * Plugin for add remove page button to more settings.
 */

/**
 * External dependencies
 */
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { PluginBlockSettingsMenuItem } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { useSelect, useDispatch } from '@wordpress/data';
import { useCallback } from '@wordpress/element';

export const name = 'amp-story-page-remove';

export const icon = 'trash';

/**
 * Renders remove page button.
 *
 * @return {ReactElement} The rendered trash menu item.
 */
const RemovePageSetting = () => {
	const { removeBlock } = useDispatch( 'core/block-editor' );
	const currentPageId = useSelect( ( select ) => select( 'amp/story' ).getCurrentPage(), [] );

	const removePage = useCallback(
		() => removeBlock( currentPageId ),
		[ currentPageId, removeBlock ],
	);

	return (
		<PluginBlockSettingsMenuItem
			icon="trash"
			label={ __( 'Remove page', 'amp' ) }
			role="menuitem"
			onClick={ removePage }
		/>
	);
};

export const render = () => <RemovePageSetting />;
