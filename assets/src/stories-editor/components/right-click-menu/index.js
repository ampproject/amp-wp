/**
 * External dependencies
 */
import { castArray } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { cloneBlock } from '@wordpress/blocks';
import { useEffect, useState } from '@wordpress/element';
import {
	MenuGroup,
	MenuItem,
	NavigableMenu,
	Popover,
} from '@wordpress/components';
import { withDispatch, withSelect } from '@wordpress/data';
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import './edit.css';

const POPOVER_PROPS = {
	className: 'amp-story-right-click-menu__popover block-editor-block-settings-menu__popover editor-block-settings-menu__popover',
	position: 'top left',
};

const RightClickMenu = ( props ) => {
	const { clientIds, clientX, clientY, removeBlock, duplicateBlock } = props;
	const [ isOpen, setIsOpen ] = useState( true );

	useEffect( () => {
		setIsOpen( true );
	}, [ clientIds, clientX, clientY ] );

	const blockClientIds = castArray( clientIds );
	const firstBlockClientId = blockClientIds[ 0 ];

	const onClose = () => {
		setIsOpen( false );
	};

	const onRemove = () => {
		onClose();
		removeBlock( firstBlockClientId );
	};

	const onDuplicate = () => {
		onClose();
		duplicateBlock( firstBlockClientId );
	};

	return (
		<>
			{ isOpen && (
				<Popover
					className={ POPOVER_PROPS.className }
					position={ POPOVER_PROPS.position }
					onClose={ onClose }
					onFocusOutside={ onClose }
					focusOnMount={ true }
				>
					<NavigableMenu
						role="menu"
					>
						<MenuGroup>
							<MenuItem
								className="editor-block-settings-menu__control block-editor-block-settings-menu__control"
								onClick={ onDuplicate }
								icon="admin-page"
							>
								{ __( 'Duplicate', 'amp' ) }
							</MenuItem>
						</MenuGroup>
						<MenuGroup>
							<MenuItem
								className="editor-block-settings-menu__control block-editor-block-settings-menu__control"
								onClick={ onRemove }
								icon="trash"
							>
								{ __( 'Remove Block', 'amp' ) }
							</MenuItem>
						</MenuGroup>
					</NavigableMenu>
				</Popover>
			) }
		</>
	);
};

const applySelect = withSelect( ( select ) => {
	const {
		getBlock,
		getBlockRootClientId,
	} = select( 'core/block-editor' );

	return {
		getBlock,
		getBlockRootClientId,
	};
} );

const applyDispatch = withDispatch( ( dispatch, props ) => {
	const {
		getBlock,
		getBlockRootClientId,
	} = props;
	const {
		removeBlock,
		insertBlock,
	} = dispatch( 'core/block-editor' );
	return {
		removeBlock,
		duplicateBlock( clientId ) {
			const block = getBlock( clientId );
			if ( 'amp/amp-story-cta' === block.name ) {
				return;
			}

			const rootClientId = getBlockRootClientId( clientId );
			const clonedBlock = cloneBlock( block );
			insertBlock( clonedBlock, null, rootClientId );
		},
	};
} );

export default compose(
	applySelect,
	applyDispatch,
)( RightClickMenu );
