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
};

const applyWithSelect = withSelect( ( select ) => {
	const {
		getBlock,
		getBlockOrder,
		getBlockRootClientId,
		getSettings,
	} = select( 'core/block-editor' );

	const { getCopiedMarkup } = select( 'amp/story' );

	return {
		getBlock,
		getBlockOrder,
		getBlockRootClientId,
		getSettings,
		getCopiedMarkup,
	};
} );

const applyWithDispatch = withDispatch( ( dispatch, props ) => {
	const {
		getBlock,
		getBlockOrder,
		getBlockRootClientId,
		getCopiedMarkup,
		getSettings,
	} = props;
	const {
		removeBlock,
		insertBlock,
		insertBlocks,
		updateBlockAttributes,
	} = dispatch( 'core/block-editor' );

	const { setCopiedMarkup } = dispatch( 'amp/story' );

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
	};
} );

export default compose(
	applyWithSelect,
	applyWithDispatch,
)( RightClickMenu );
