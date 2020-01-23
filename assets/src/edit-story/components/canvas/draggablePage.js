/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { forwardRef, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import DropZone from '../dropzone';
import { useStory } from '../../app/story';

const Page = styled.button`
	padding: 0;
	margin: 0;
	border: 3px solid ${ ( { isActive, theme } ) => isActive ? theme.colors.selection : theme.colors.bg.v1 };
	height: ${ ( { height } ) => height }px;
	width: ${ ( { width } ) => width }px;
	background-color: ${ ( { theme } ) => theme.colors.mg.v1 };
	flex: none;
	transition: width .2s ease, height .2s ease;
`;

// Disable reason: forwardRef render functions do not support propTypes
//eslint-disable-next-line react/prop-types
function DraggablePageWithRef( { pageIndex, onClick, isActive, ariaLabel, width, height }, ref ) {
	const { state: { pages }, actions: { setCurrentPage, arrangePage } } = useStory();

	const getArrangeIndex = ( sourceIndex, dstIndex, position ) => {
		// If the dropped element is before the dropzone index then we have to deduct
		// that from the index to make up for the "lost" element in the row.
		const indexAdjustment = sourceIndex < dstIndex ? -1 : 0;
		if ( 'left' === position.x ) {
			return dstIndex + indexAdjustment;
		}
		return dstIndex + 1 + indexAdjustment;
	};

	const onDragStart = useCallback( ( evt ) => {
		const pageData = {
			type: 'page',
			index: pageIndex,
		};
		evt.dataTransfer.setData( 'text', JSON.stringify( pageData ) );
	}, [ pageIndex ] );

	const onDrop = ( evt, { position } ) => {
		const droppedEl = JSON.parse( evt.dataTransfer.getData( 'text' ) );
		if ( ! droppedEl || 'page' !== droppedEl.type ) {
			return;
		}
		const arrangedIndex = getArrangeIndex( droppedEl.index, pageIndex, position );
		// Do nothing if the index didn't change.
		if ( droppedEl.index !== arrangedIndex ) {
			const pageId = pages[ droppedEl.index ].id;
			arrangePage( { pageId, position: arrangedIndex } );
			setCurrentPage( { pageId } );
		}
	};

	return (
		<DropZone onDrop={ onDrop } pageIndex={ pageIndex } >
			<Page
				draggable="true"
				onClick={ onClick }
				onDragStart={ onDragStart }
				isActive={ isActive }
				aria-label={ ariaLabel }
				width={ width }
				height={ height }
				ref={ ref }
			/>
		</DropZone>
	);
}

const DraggablePage = forwardRef( DraggablePageWithRef );

export default DraggablePage;
