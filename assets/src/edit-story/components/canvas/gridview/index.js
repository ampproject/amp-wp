/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useCallback, useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useStory } from '../../../app/story';
import DropZone from '../../dropzone';
import RectangleIcon from './rectangle.svg';

const PAGE_WIDTH = 90;
const PAGE_HEIGHT = 160;

const Wrapper = styled.div`
	position: relative;
	display: grid;
	grid-template-columns: ${ ( { scale } ) => `repeat(auto-fit, minmax(${ scale * PAGE_WIDTH }px, max-content))` };
	grid-gap: 20px;
	justify-content: center;
	justify-items: center;
    align-items: center;
`;

const Page = styled.button`
	padding: 0;
	margin: 0;
	border: 3px solid ${ ( { isActive, theme } ) => isActive ? theme.colors.selection : theme.colors.bg.v1 };
	height: ${ ( { scale } ) => `${ scale * PAGE_HEIGHT }px` };
	width: ${ ( { scale } ) => `${ scale * PAGE_WIDTH }px` };
	background-color: ${ ( { isActive, theme } ) => isActive ? theme.colors.fg.v1 : theme.colors.mg.v1 };
	transition: width .2s ease, height .2s ease;
`;

const RangeInputWrapper = styled.div`
	display: flex;
	width: 400px;
	margin: 0 auto;
`;

const RangeInput = styled.input.attrs( () => ( {
	type: 'range',
} ) )`
	display: block;
	width: 360px;
	margin: 0 auto;
`;

function RangeControl( { value, onChange } ) {
	return (
		<RangeInputWrapper>
			<RectangleIcon />
			<RangeInput
				min="1"
				max="3"
				steps="1"
				value={ value }
				onChange={ ( evt ) => onChange( Number( evt.target.value ) ) }
			/>
			<RectangleIcon />
		</RangeInputWrapper>
	);
}

RangeControl.propTypes = {
	value: PropTypes.number.isRequired,
	onChange: PropTypes.func.isRequired,
};

// TODO: Make drag & drop part DRY.
function GridView() {
	const { state: { pages, currentPageIndex }, actions: { setCurrentPage, arrangePage } } = useStory();
	const [ zoomLevel, setZoomLevel ] = useState( 2 );

	const getArrangeIndex = ( sourceIndex, dstIndex, position ) => {
		// If the dropped element is before the dropzone index then we have to deduct
		// that from the index to make up for the "lost" element in the row.
		const indexAdjustment = sourceIndex < dstIndex ? -1 : 0;
		if ( 'left' === position.x ) {
			return dstIndex + indexAdjustment;
		}
		return dstIndex + 1 + indexAdjustment;
	};

	const onDragStart = useCallback( ( index ) => ( evt ) => {
		const pageData = {
			type: 'page',
			index,
		};
		evt.dataTransfer.setData( 'text', JSON.stringify( pageData ) );
	}, [] );

	const onDrop = ( evt, { position, pageIndex } ) => {
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
		<>
			<RangeControl
				value={ zoomLevel }
				onChange={ setZoomLevel }
			/>
			<Wrapper scale={ zoomLevel }>
				{ pages.map( ( page, index ) => {
					const isCurrentPage = index === currentPageIndex;
					return (
						<DropZone key={ index } onDrop={ onDrop } pageIndex={ index } >
							<Page
								key={ index }
								draggable="true"
								onDragStart={ onDragStart( index ) }
								isActive={ isCurrentPage }
								scale={ zoomLevel }
								aria-label={ isCurrentPage ? sprintf( __( 'Page %s (current page)', 'amp' ), index + 1 ) : sprintf( __( 'Go to page %s', 'amp' ), index + 1 ) }
							/>
						</DropZone>
					);
				} ) }
			</Wrapper>
		</>
	);
}

export default GridView;
