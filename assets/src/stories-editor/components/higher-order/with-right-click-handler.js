/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';
import { createHigherOrderComponent } from '@wordpress/compose';
import { render } from '@wordpress/element';
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

	const onContextMenu = ( event ) => {
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
		const relativePositionX = event.clientX - wrapperDimensions.left;
		const relativePositionY = event.clientY - wrapperDimensions.top - toolBarHeight;
		const clientID = getCurrentPage();

		let insidePercentageY = 0;
		let insidePercentageX = 0;

		const block = getBlockDOMNode( clientID );
		if ( block ) {
			const blockPostions = block.getBoundingClientRect();
			const insideY = event.clientY - blockPostions.top;
			const insideX = event.clientX - blockPostions.left;
			insidePercentageY = getPercentageFromPixels( 'y', insideY );
			insidePercentageX = getPercentageFromPixels( 'x', insideX );
		}

		render(
			<RightClickMenu clientIds={ selectedBlockClientIds } clientX={ relativePositionX } clientY={ relativePositionY } insidePercentageX={ insidePercentageX } insidePercentageY={ insidePercentageY } />,
			document.getElementById( 'amp-story-right-click-menu' )
		);

		event.preventDefault();
	};

	return {
		onContextMenu,
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
			const { name, onContextMenu, isReordering } = props;
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
				<div onContextMenu={ onContextMenu }>
					<BlockEdit { ...props } />
				</div>
			);
		} );
	},
	'withRightClickHandler'
);
