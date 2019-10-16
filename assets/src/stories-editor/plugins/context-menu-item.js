/**
 * Plugin that adds a remove page button to the "More" settings menu.
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
	const pages = useSelect( ( select ) => select( 'core/block-editor' ).getBlockOrder(), [] );

	const removePage = useCallback(
		() => removeBlock( currentPageId ),
		[ currentPageId, removeBlock ],
	);

	// Shouldn't allow users to remove the first and only page.
	if ( pages.length < 2 ) {
		return null;
	}

	return (
		<PluginBlockSettingsMenuItem
			icon="trash"
			label={ __( 'Remove Page', 'amp' ) }
			role="menuitem"
			onClick={ removePage }
		/>
	);
};

export const render = () => <RemovePageSetting />;
