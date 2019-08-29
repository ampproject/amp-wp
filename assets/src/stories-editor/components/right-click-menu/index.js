/**
 * External dependencies
 */
import { castArray } from 'lodash';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { cloneBlock, serialize } from '@wordpress/blocks';
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
import { copyTextToClipBoard } from '../../helpers';

const POPOVER_PROPS = {
	className: 'amp-story-right-click-menu__popover block-editor-block-settings-menu__popover editor-block-settings-menu__popover',
	position: 'bottom left',
};

const RightClickMenu = ( props ) => {
	const { clientIds, clientX, clientY, copyBlock, cutBlock, removeBlock, duplicateBlock } = props;
	const [ isOpen, setIsOpen ] = useState( true );

	useEffect( () => {
		setIsOpen( true );
	}, [ clientIds, clientX, clientY ] );

	const blockClientIds = castArray( clientIds );

	// @todo Make sure it's the inner block that's taken, not the Page.
	const firstBlockClientId = blockClientIds[ 0 ];

	const onClose = () => {
		setIsOpen( false );
	};

	const onCopy = () => {
		onClose();
		copyBlock( firstBlockClientId );
	};

	const onCut = () => {
		onClose();
		cutBlock( firstBlockClientId );
	};

	const onRemove = () => {
		onClose();
		removeBlock( firstBlockClientId );
	};

	const onDuplicate = () => {
		onClose();
		duplicateBlock( firstBlockClientId );
	};

	// @todo Calculate the exact position based on the wrapper and event click.
	// @todo Move this to with-right-click-handler.
	const position = {
		top: clientY - 100,
		left: clientX - 160,
	};

	const blockActions = [
		{
			name: __( 'Copy Block', 'amp' ),
			blockAction: onCopy,
		},
		{
			name: __( 'Cut Block', 'amp' ),
			blockAction: onCut,
		},
		{
			name: __( 'Duplicate Block', 'amp' ),
			blockAction: onDuplicate,
		},
		{
			name: __( 'Remove Block', 'amp' ),
			blockAction: onRemove,
		},
	];

	return (
		<div className="amp-right-click-menu__container" style={ position }>
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
						{ blockActions.map( ( action ) => (
							<MenuGroup key={ `action-${ action.name }` } >
								<MenuItem
									className="editor-block-settings-menu__control block-editor-block-settings-menu__control"
									onClick={ action.blockAction }
									icon="admin-page"
								>
									{ action.name }
								</MenuItem>
							</MenuGroup>
						) ) }
					</NavigableMenu>
				</Popover>
			) }
		</div>
	);
};

RightClickMenu.propTypes = {
	clientIds: PropTypes.array.isRequired,
	clientX: PropTypes.number.isRequired,
	clientY: PropTypes.number.isRequired,
	copyBlock: PropTypes.func.isRequired,
	cutBlock: PropTypes.func.isRequired,
	removeBlock: PropTypes.func.isRequired,
	duplicateBlock: PropTypes.func.isRequired,
};

const applyWithSelect = withSelect( ( select ) => {
	const {
		getBlock,
		getBlockRootClientId,
	} = select( 'core/block-editor' );

	return {
		getBlock,
		getBlockRootClientId,
	};
} );

const applyWithDispatch = withDispatch( ( dispatch, props ) => {
	const {
		getBlock,
		getBlockRootClientId,
	} = props;
	const {
		removeBlock,
		insertBlock,
	} = dispatch( 'core/block-editor' );

	const copyBlock = ( clientId ) => {
		const block = getBlock( clientId );
		const serialized = serialize( block );
		copyTextToClipBoard( serialized );
	};

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
		copyBlock,
		cutBlock( clientId ) {
			copyBlock( clientId );
			removeBlock( clientId );
		},
	};
} );

export default compose(
	applyWithSelect,
	applyWithDispatch,
)( RightClickMenu );
