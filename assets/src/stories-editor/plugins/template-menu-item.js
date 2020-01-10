/**
 * Plugin for adding Templates (Reusable blocks) without converting the page itself to a reusable block.
 */

/**
 * WordPress dependencies
 */
import { PluginBlockSettingsMenuItem } from '@wordpress/edit-post';
import { __ } from '@wordpress/i18n';
import { select, dispatch } from '@wordpress/data';
import { cloneBlock } from '@wordpress/blocks';

/**
 * External dependencies
 */
import { uniqueId } from 'lodash';

const addTemplate = () => {
	const { getSelectedBlockClientId, getBlock } = select( 'core/block-editor' );
	const {
		__experimentalReceiveReusableBlocks: receiveReusableBlocks,
		__experimentalSaveReusableBlock: saveReusableBlock,
	} = dispatch( 'core/block-editor' );

	// @todo Allow multi-page templates.
	const parsedBlock = getBlock( getSelectedBlockClientId() );
	if ( 'amp/amp-story-page' !== parsedBlock.name ) {
		return;
	}

	// Clone for having a different ID.
	const templateBlock = cloneBlock( parsedBlock );

	// @todo Allow choosing name for the template.
	const reusableBlock = {
		id: uniqueId( 'reusable' ),
		clientId: templateBlock.clientId,
		title: __( 'Template', 'amp' ),
	};

	receiveReusableBlocks( [ {
		reusableBlock,
		parsedBlock: templateBlock,
	} ] );

	// @todo Display notice.
	saveReusableBlock( reusableBlock.id );
};

export const isActive = false;

export const name = 'amp-story';

export const render = ( ) => (
	<PluginBlockSettingsMenuItem
		allowedBlocks={ [ 'amp/amp-story-page' ] }
		icon="welcome-add-page"
		label={ __( 'Save as Template', 'amp' ) }
		role="menuitem"
		onClick={ addTemplate }
	/>
);
