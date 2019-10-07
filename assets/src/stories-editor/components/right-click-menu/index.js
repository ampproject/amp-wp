/**
 * External dependencies
 */
import { castArray } from 'lodash';
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { cloneBlock, pasteHandler, serialize } from '@wordpress/blocks';
import { useEffect, useState, useRef } from '@wordpress/element';
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
	isPageBlock,
} from '../../helpers';
import { ALLOWED_MOVABLE_BLOCKS, DISABLE_DUPLICATE_BLOCKS } from '../../constants';
import useOutsideClickChecker from './outside-click-checker';

const POPOVER_PROPS = {
	className: 'amp-story-right-click-menu__popover block-editor-block-settings-menu__popover editor-block-settings-menu__popover',
	position: 'bottom left',
};

const isBlockAllowedOnPage = ( name, pageId ) => {
	return true;
};

const RightClickMenu = ( props ) => {
	const {
		clientIds,
		clientX,
		clientY,
		insidePercentageX,
		insidePercentageY,
		copyBlock,
		cutBlock,
		getBlock,
		getCopiedMarkup,
		removeBlock,
		duplicateBlock,
		pasteBlock,
		moveBackBlock,
		moveForwardBlock,
		getBlockOrder,
		getCurrentPage,
	} = props;
	const [ isOpen, setIsOpen ] = useState( true );

	useEffect( () => {
		setIsOpen( true );
	}, [ clientIds, clientX, clientY ] );

	const blockClientIds = castArray( clientIds );
	const firstBlockClientId = blockClientIds[ 0 ];
	const block = getBlock( firstBlockClientId );

	const onClose = () => {
		setIsOpen( false );
	};

	const containerRef = useRef( null );
	useOutsideClickChecker( containerRef, onClose );

	const position = {
		top: clientY,
		left: clientX,
	};

	let blockActions = [];

	// Don't allow any actions other than pasting with Page.
	if ( ! isPageBlock( firstBlockClientId ) ) {
		blockActions = [
			{
				name: __( 'Copy Block', 'amp' ),
				blockAction: copyBlock,
				icon: 'admin-page',
				className: 'right-click-copy',
			},
			{
				name: __( 'Cut Block', 'amp' ),
				blockAction: cutBlock,
				icon: 'clipboard',
				className: 'right-click-cut',
			},

		];

		// Disable Duplicate Block option for cta and attachment blocks.
		if ( block && ! DISABLE_DUPLICATE_BLOCKS.includes( block.name ) ) {
			blockActions.push(
				{
					name: __( 'Duplicate Block', 'amp' ),
					blockAction: duplicateBlock,
					icon: 'admin-page',
					className: 'right-click-duplicate',
				},
			);
		}

		const pageList = getBlockOrder();
		const pageNumber = pageList.length;
		if ( block && pageNumber > 1 ) {
			const currentPage = getCurrentPage();
			const currentPagePosition = pageList.indexOf( currentPage );
			if ( currentPagePosition > 0 ) {
				const prevPage = currentPagePosition[ currentPagePosition - 1 ];
				if ( isBlockAllowedOnPage( block.name, prevPage ) ) {
					blockActions.push(
						{
							name: __( 'Send block to previous page', 'amp' ),
							blockAction: moveBackBlock,
							icon: 'arrow-left-alt',
							className: 'right-click-previous-page',
						},
					);
				}
			}
			if ( currentPagePosition < ( pageNumber - 1 ) ) {
				const nextPage = currentPagePosition[ currentPagePosition + 1 ];
				if ( isBlockAllowedOnPage( block.name, nextPage ) ) {
					blockActions.push(
						{
							name: __( 'Send block to next page', 'amp' ),
							blockAction: moveForwardBlock,
							icon: 'arrow-right-alt',
							className: 'right-click-next-page',
						},
					);
				}
			}
		}

		blockActions.push(
			{
				name: __( 'Remove Block', 'amp' ),
				blockAction: removeBlock,
				icon: 'trash',
				className: 'right-click-remove',
			},
		);
	}

	// If it's Page block and clipboard is empty, don't display anything.
	if ( ! getCopiedMarkup().length && isPageBlock( firstBlockClientId ) ) {
		return '';
	}

	if ( getCopiedMarkup().length ) {
		blockActions.push(
			{
				name: __( 'Paste', 'amp' ),
				blockAction: pasteBlock,
				icon: 'pressthis',
				className: 'right-click-paste',
			}
		);
	}

	return (
		<div ref={ containerRef } className="amp-right-click-menu__container" style={ position }>
			{ isOpen && (
				<Popover
					className={ POPOVER_PROPS.className }
					position={ POPOVER_PROPS.position }
					onClose={ onClose }
					focusOnMount="firstElement"
				>
					<NavigableMenu
						role="menu"
					>
						{ blockActions.map( ( action ) => (
							<MenuGroup key={ `action-${ action.name }` } >
								<MenuItem
									className={ classnames( action.className, 'editor-block-settings-menu__control block-editor-block-settings-menu__control' ) }
									onClick={ () => {
										onClose();
										action.blockAction( firstBlockClientId, insidePercentageY, insidePercentageX );
									} }
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
	clientIds: PropTypes.arrayOf( PropTypes.string ).isRequired,
	clientX: PropTypes.number.isRequired,
	clientY: PropTypes.number.isRequired,
	insidePercentageX: PropTypes.number,
	insidePercentageY: PropTypes.number,
	copyBlock: PropTypes.func.isRequired,
	cutBlock: PropTypes.func.isRequired,
	getBlock: PropTypes.func.isRequired,
	getCopiedMarkup: PropTypes.func.isRequired,
	removeBlock: PropTypes.func.isRequired,
	duplicateBlock: PropTypes.func.isRequired,
	pasteBlock: PropTypes.func.isRequired,
	getBlockOrder: PropTypes.func.isRequired,
	getCurrentPage: PropTypes.func.isRequired,
	moveBackBlock: PropTypes.func.isRequired,
	moveForwardBlock: PropTypes.func.isRequired,
};

const applyWithSelect = withSelect( ( select ) => {
	const {
		getBlock,
		getBlockOrder,
		getBlockRootClientId,
		getSettings,
	} = select( 'core/block-editor' );

	const {
		getCopiedMarkup,
		getCurrentPage,
	} = select( 'amp/story' );

	return {
		getBlock,
		getBlockOrder,
		getBlockRootClientId,
		getSettings,
		getCopiedMarkup,
		getCurrentPage,
	};
} );

const applyWithDispatch = withDispatch( ( dispatch, props ) => {
	const {
		getBlock,
		getBlockOrder,
		getBlockRootClientId,
		getCopiedMarkup,
		getSettings,
		getCurrentPage,
	} = props;
	const {
		removeBlock,
		insertBlock,
		insertBlocks,
		updateBlockAttributes,
		selectBlock,
	} = dispatch( 'core/block-editor' );

	const { setCopiedMarkup, setCurrentPage } = dispatch( 'amp/story' );

	const { __experimentalCanUserUseUnfilteredHTML: canUserUseUnfilteredHTML } = getSettings();

	const copyBlock = ( clientId ) => {
		const block = getBlock( clientId );
		const serialized = serialize( block );

		// Set the copied block to component state for being able to Paste.
		setCopiedMarkup( serialized );
		copyTextToClipBoard( serialized );
	};

	const processTextToPaste = ( text, clientId, insidePercentageY, insidePercentageX ) => {
		const mode = 'BLOCKS';
		const content = pasteHandler( {
			HTML: '',
			plainText: text,
			mode,
			tagName: null,
			canUserUseUnfilteredHTML,
		} );

		const clickedBlock = getBlock( clientId );
		// Get the page client ID to paste to.
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
		insertBlocks( ensureAllowedBlocksOnPaste( content, pageClientId, isFirstPage ), null, pageClientId ).then( ( { blocks } ) => {
			for ( const block of blocks ) {
				if ( ALLOWED_MOVABLE_BLOCKS.includes( block.name ) ) {
					updateBlockAttributes( block.clientId, {
						positionTop: insidePercentageY,
						positionLeft: insidePercentageX,
					} );
				}
			}
		} ).catch( () => {} );
	};

	const getNeighborPageId = ( offset ) => {
		const pages = getBlockOrder();
		const rootClientId = getCurrentPage();
		const currentPageIndex = pages.findIndex( ( i ) => i === rootClientId );
		const newPageIndex = currentPageIndex + offset;
		const isInsidePageCount = newPageIndex >= 0 && newPageIndex < pages.length;
		const newPageId = pages[ newPageIndex ];

		// Do we even have a neighbor in that direction?
		if ( ! isInsidePageCount ) {
			return null;
		}

		return newPageId;
	};

	const moveBlock = ( clientId, offset ) => {
		const newPageId = getNeighborPageId( offset );
		const block = getBlock( clientId );
		const isAllowedOnPage = isBlockAllowedOnPage( block.name, newPageId );
		if ( ! newPageId || ! isAllowedOnPage || ! offset === 0 ) {
			return;
		}
		// Remove block and add cloned block to new page.
		removeBlock( clientId );
		const clonedBlock = cloneBlock( block );
		insertBlock( clonedBlock, null, newPageId );

		// Switch to new page.
		setCurrentPage( newPageId );
		selectBlock( newPageId );
	};

	return {
		removeBlock,
		duplicateBlock( clientId ) {
			const block = getBlock( clientId );
			if ( DISABLE_DUPLICATE_BLOCKS.includes( block.name ) ) {
				return;
			}

			const rootClientId = getBlockRootClientId( clientId );
			const clonedBlock = cloneBlock( block );
			insertBlock( clonedBlock, null, rootClientId );
		},
		copyBlock,
		cutBlock( clientId ) {
			// First copy block and then remove it.
			copyBlock( clientId );
			removeBlock( clientId );
		},
		pasteBlock( clientId, insidePercentageY, insidePercentageX ) {
			const { navigator } = window;

			if ( navigator.clipboard && navigator.clipboard.readText ) {
				// We have to ask permissions for being able to read from clipboard.
				navigator.clipboard.readText().
					then( ( clipBoardText ) => {
						// If got permission, paste from clipboard.
						processTextToPaste( clipBoardText, clientId, insidePercentageY, insidePercentageX );
					} ).catch( () => {
						// If forbidden, use the markup from state instead.
						const text = getCopiedMarkup();
						processTextToPaste( text, clientId, insidePercentageY, insidePercentageX );
					} );
			} else {
				const text = getCopiedMarkup();
				processTextToPaste( text, clientId, insidePercentageY, insidePercentageX );
			}
		},
		moveBackBlock( clientId ) {
			moveBlock( clientId, -1 );
		},
		moveForwardBlock( clientId ) {
			moveBlock( clientId, 1 );
		},
	};
} );

export default compose(
	applyWithSelect,
	applyWithDispatch,
)( RightClickMenu );
