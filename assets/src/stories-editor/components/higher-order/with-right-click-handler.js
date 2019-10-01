/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';
import { createHigherOrderComponent } from '@wordpress/compose';
import { render } from '@wordpress/element';
import { F10 } from '@wordpress/keycodes';
/**
 * Internal dependencies
 */
import { ALLOWED_CHILD_BLOCKS } from '../../constants';
import { getBlockDOMNode, getPercentageFromPixels } from '../../helpers';
import { RightClickMenu } from '../';

const applyWithSelect = withSelect( ( select, props ) => {
	const {	isReordering, getCopiedMarkup, getCurrentPage } = select( 'amp/story' );
	const {
		getSelectedBlockClientIds,
		hasMultiSelection,
	} = select( 'core/block-editor' );

	const { name } = props;

	const handleEvent = ( event ) => {
		const isKeydown = event.type === 'keydown';
		const isContextmenu = event.type === 'contextmenu';

		const selectedBlockClientIds = getSelectedBlockClientIds();

		if ( selectedBlockClientIds.length === 0 ) {
			return;
		}
		// If nothing is in the saved markup, use the default behavior.
		if ( 'amp/amp-story-page' === name && ! getCopiedMarkup().length ) {
			return;
		}

		// Let's ignore if some text has been selected.
		const selectedText = window.getSelection().toString();
		// Let's ignore multi-selection for now.
		if ( hasMultiSelection() || selectedText.length ) {
			return;
		}

		// Ignore if it's a keydown event and the correct combo hasn't been pressed.
		if ( isKeydown ) {
			const isShift = event.getModifierState( 'Shift' );
			const isF10 = event.keyCode === F10;
			const isRightCombo = isShift && isF10;
			if ( ! isRightCombo ) {
				return;
			}
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

		if ( isContextmenu ) {
			eventX = event.clientX;
			eventY = event.clientY;
		} else if ( isKeydown ) {
			// Place menu in the upper left corner of the element - but a little inside it.
			const elementPosition = event.target.getBoundingClientRect();
			eventX = elementPosition.left + 20;
			eventY = elementPosition.top + 20;
		}

		const relativePositionX = eventX - wrapperDimensions.left;
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
			<RightClickMenu clientIds={ selectedBlockClientIds } clientX={ relativePositionX } clientY={ relativePositionY } insidePercentageX={ insidePercentageX } insidePercentageY={ insidePercentageY } />,
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
 * Higher-order component that adds right click handler to each inner block.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( BlockEdit ) => {
		return applyWithSelect( ( props ) => {
			const { name, handleEvent, isReordering } = props;
			const isPageBlock = 'amp/amp-story-page' === name;

			// Add for page block and inner blocks.
			if ( ! isPageBlock && ! ALLOWED_CHILD_BLOCKS.includes( name ) ) {
				return <BlockEdit { ...props } />;
			}

			// Not relevant for reordering.
			if ( isReordering ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<div tabIndex="0" role="button" onContextMenu={ handleEvent } onKeyDown={ handleEvent }>
					<BlockEdit { ...props } />
				</div>
			);
		} );
	},
	'withRightClickHandler'
);
