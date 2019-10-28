/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';
import { createHigherOrderComponent } from '@wordpress/compose';
import { render } from '@wordpress/element';
import { KeyboardShortcuts } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { ALLOWED_CHILD_BLOCKS } from '../../constants';
import { getBlockDOMNode, getPercentageFromPixels } from '../../helpers';
import { ContextMenu } from '../';

const applyWithSelect = withSelect( ( select ) => {
	const {	isReordering, getCurrentPage } = select( 'amp/story' );
	const {
		getSelectedBlockClientIds,
		hasMultiSelection,
		getSettings,
	} = select( 'core/block-editor' );

	const { isRTL } = getSettings();

	const handleEvent = ( event ) => {
		const isRightClick = event.type === 'contextmenu';

		const selectedBlockClientIds = getSelectedBlockClientIds();

		if ( selectedBlockClientIds.length === 0 ) {
			return;
		}

		// Let's ignore if some text has been selected.
		const selectedText = window.getSelection().toString();
		// Let's ignore multi-selection for now.
		if ( hasMultiSelection() || selectedText.length ) {
			return;
		}

		const editLayout = document.querySelector( '.edit-post-layout' );
		if ( ! document.getElementById( 'amp-story-right-click-menu' ) ) {
			const menuWrapper = document.createElement( 'div' );
			menuWrapper.id = 'amp-story-right-click-menu';

			editLayout.appendChild( menuWrapper );
		}

		// Calculate the position to display the right click menu.
		const wrapperDimensions = editLayout.getBoundingClientRect();
		const toolBar = document.querySelector( '.edit-post-header' );

		// If Toolbar is available then consider that as well.
		let toolBarHeight = 0;
		if ( toolBar ) {
			toolBarHeight = toolBar.clientHeight;
		}

		let eventX = 0;
		let eventY = 0;

		if ( isRightClick ) {
			// Use coordinates of actual click to place context menu.
			eventX = event.clientX;
			eventY = event.clientY;
		} else {
			// Place menu the center of the target element.
			const elementPosition = event.target.getBoundingClientRect();
			eventX = elementPosition.left + ( elementPosition.width / 2 );
			eventY = elementPosition.top + ( elementPosition.height / 2 );
		}

		const relativeSub = ( isRTL ) ? wrapperDimensions.right : wrapperDimensions.left;
		const relativePositionX = eventX - relativeSub;
		const relativePositionY = eventY - wrapperDimensions.top - toolBarHeight;
		const clientId = getCurrentPage();

		let insidePercentageY = 0;
		let insidePercentageX = 0;

		const page = getBlockDOMNode( clientId );
		if ( page ) {
			const pagePosition = page.getBoundingClientRect();
			const insideX = eventX - pagePosition.left;
			const insideY = eventY - pagePosition.top;
			insidePercentageX = getPercentageFromPixels( 'x', insideX );
			insidePercentageY = getPercentageFromPixels( 'y', insideY );
		}

		render(
			<ContextMenu clientIds={ selectedBlockClientIds } clientX={ relativePositionX } clientY={ relativePositionY } insidePercentageX={ insidePercentageX } insidePercentageY={ insidePercentageY } />,
			document.getElementById( 'amp-story-right-click-menu' )
		);

		event.preventDefault();
	};

	return {
		handleEvent,
		isReordering: isReordering(),
	};
} );

/**
 * Higher-order component that adds right context menu handler to each inner block.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( BlockEdit ) => {
		return applyWithSelect( ( props ) => {
			const { name, handleEvent, isReordering } = props;
			const isPage = 'amp/amp-story-page' === name;

			// Add for page block and inner blocks.
			if ( ! isPage && ! ALLOWED_CHILD_BLOCKS.includes( name ) ) {
				return <BlockEdit { ...props } />;
			}

			// Not relevant for reordering.
			if ( isReordering ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<KeyboardShortcuts shortcuts={ { 'shift+f10': handleEvent } }>
					<div onContextMenu={ handleEvent }>
						<BlockEdit { ...props } />
					</div>
				</KeyboardShortcuts>
			);
		} );
	},
	'withContextMenu'
);
