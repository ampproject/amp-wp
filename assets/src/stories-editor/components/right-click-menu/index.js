/**
 * External dependencies
 */
import { castArray } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useEffect, useState } from '@wordpress/element';
import {
	MenuGroup,
	MenuItem,
	NavigableMenu,
	Popover,
} from '@wordpress/components';
import { withDispatch } from '@wordpress/data';
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import './edit.css';

const POPOVER_PROPS = {
	className: 'amp-story-right-click-menu__popover block-editor-block-settings-menu__popover editor-block-settings-menu__popover',
	position: 'top left',
};

const RightClickMenu = ( { clientIds, clientX, clientY, removeBlock } ) => {
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

	return (
		<>
			{ isOpen && (
				<Popover
					className={ POPOVER_PROPS.className }
					position={ POPOVER_PROPS.position }
					onClose={ onClose }
					onFocusOutside={ onClose }
					headerTitle={ 'Hello' }
					focusOnMount={ true }
				>
					<NavigableMenu
						role="menu"
					>
						<MenuGroup>
							<MenuItem
								className="editor-block-settings-menu__control block-editor-block-settings-menu__control"
								onClick={ () => {
									onClose();
								} }
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

const applyDispatch = withDispatch( ( dispatch ) => {
	const { removeBlock } = dispatch( 'core/block-editor' );
	return {
		removeBlock,
	};
} );

export default compose(
	applyDispatch,
)( RightClickMenu );
