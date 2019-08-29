/**
 * External dependencies
 */
import { castArray } from 'lodash';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { cloneBlock, pasteHandler, serialize } from '@wordpress/blocks';
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
import {
	copyTextToClipBoard,
	ensureAllowedBlocksOnPaste,
} from '../../helpers';

const POPOVER_PROPS = {
	className: 'amp-story-right-click-menu__popover block-editor-block-settings-menu__popover editor-block-settings-menu__popover',
	position: 'bottom left',
};

const RightClickMenu = ( props ) => {
	const {
		clientIds,
		clientX,
		clientY,
		copyBlock,
		cutBlock,
		getBlock,
		removeBlock,
		duplicateBlock,
		pasteBlock,
	} = props;
	const [ isOpen, setIsOpen ] = useState( true );
	const [ copiedBlock, setCopiedBlock ] = useState( '' );

	useEffect( () => {
		setIsOpen( true );
	}, [ clientIds, clientX, clientY ] );

	const blockClientIds = castArray( clientIds );
	const firstBlockClientId = blockClientIds[ 0 ];
	const block = getBlock( firstBlockClientId );
	const isPageBlock = block ? 'amp/amp-story-page' === block.name : false;

	const onClose = () => {
		setIsOpen( false );
	};

	const onCopy = () => {
		onClose();
		copyBlock( firstBlockClientId, setCopiedBlock );
	};

	const onCut = () => {
		onClose();
		cutBlock( firstBlockClientId, setCopiedBlock );
	};

	const onRemove = () => {
		onClose();
		removeBlock( firstBlockClientId );
	};

	const onDuplicate = () => {
		onClose();
		duplicateBlock( firstBlockClientId );
	};

	const onPaste = () => {
		pasteBlock( copiedBlock, firstBlockClientId );
		onClose();
	};

	// @todo Calculate the exact position based on the wrapper and event click.
	// @todo Move this to with-right-click-handler.
	const position = {
		top: clientY - 100,
		left: clientX - 160,
	};

	let blockActions = [];

	// Don't allow any actions other than pasting with Page.
	if ( ! isPageBlock ) {
		blockActions = [
			{
				name: __( 'Copy Block', 'amp' ),
				blockAction: onCopy,
				icon: 'admin-page',
			},
			{
				name: __( 'Cut Block', 'amp' ),
				blockAction: onCut,
				icon: 'clipboard',
			},
			{
				name: __( 'Duplicate Block', 'amp' ),
				blockAction: onDuplicate,
				icon: 'admin-page',
			},
			{
				name: __( 'Remove Block', 'amp' ),
				blockAction: onRemove,
				icon: 'trash',
			},
		];
	}

	// If it's Page block and clipboard is empty, don't display anything.
	if ( ! copiedBlock.length && isPageBlock ) {
		return '';
	}

	if ( copiedBlock.length ) {
		blockActions.push(
			{
				name: __( 'Paste Last Copied Block', 'amp' ),
				blockAction: onPaste,
				icon: 'pressthis',
			}
		);
	}

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
									icon={ action.icon }
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
	getBlock: PropTypes.func.isRequired,
	removeBlock: PropTypes.func.isRequired,
	duplicateBlock: PropTypes.func.isRequired,
	pasteBlock: PropTypes.func.isRequired,
};

const applyWithSelect = withSelect( ( select ) => {
	const {
		getBlock,
		getBlockOrder,
		getBlockRootClientId,
		getSettings,
	} = select( 'core/block-editor' );

	return {
		getBlock,
		getBlockOrder,
		getBlockRootClientId,
		getSettings,
	};
} );

const applyWithDispatch = withDispatch( ( dispatch, props ) => {
	const {
		getBlock,
		getBlockOrder,
		getBlockRootClientId,
		getSettings,
	} = props;
	const {
		removeBlock,
		insertBlock,
		insertBlocks,
	} = dispatch( 'core/block-editor' );

	const { __experimentalCanUserUseUnfilteredHTML: canUserUseUnfilteredHTML } = getSettings();

	const copyBlock = ( clientId, setCopiedBlock ) => {
		const block = getBlock( clientId );
		const serialized = serialize( block );

		// Set the copied block to component state for being able to Paste.
		setCopiedBlock( serialized );
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
		cutBlock( clientId, setCopiedBlock ) {
			copyBlock( clientId, setCopiedBlock );
			removeBlock( clientId );
		},
		pasteBlock( text, clientId ) {
			const mode = 'BLOCKS';

			const content = pasteHandler( {
				HTML: '',
				plainText: text,
				mode,
				tagName: null,
				canUserUseUnfilteredHTML,
			} );

			const clickedBlock = getBlock( clientId );
			let pageClientId;
			if ( 'amp/amp-story-page' === clickedBlock.name ) {
				pageClientId = clickedBlock.clientId;
			} else {
				pageClientId = getBlockRootClientId( clientId );
			}

			if ( ! pageClientId || ! content.length ) {
				return;
			}

			const isFirstPage = getBlockOrder().indexOf( pageClientId ) === 0;
			insertBlocks( ensureAllowedBlocksOnPaste( content, pageClientId, isFirstPage ), null, pageClientId );
		},
	};
} );

export default compose(
	applyWithSelect,
	applyWithDispatch,
)( RightClickMenu );
