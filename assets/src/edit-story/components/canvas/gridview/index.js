/**
 * External dependencies
 */
import styled, { css } from 'styled-components';
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
import Rectangle from './rectangle.svg';

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
	width: 440px;
	margin: 0 auto 75px;
`;

const rangeThumb = css`
	width: 28px;
	height: 28px;
	border-radius: 100%;
	background: ${ ( { theme } ) => theme.colors.fg.v1 };
`;

const rangeTrack = css`
	background: rgba(255, 255, 255, 0.2);
	border-color: transparent;
	color: transparent;
	width: 100%;
	height: 4px;
`;

// Lots of repetition to avoid browsers dropping unknown selectors.
const RangeInput = styled.input.attrs( () => ( {
	type: 'range',
} ) )`
	-webkit-appearance: none;
	background: transparent;
	display: block;
	width: 360px;
	margin: 0 auto;

	&::-webkit-slider-thumb {
		${ rangeThumb }
		-webkit-appearance: none;
		margin-top: -12px;
	}

	&::-moz-range-thumb {
		${ rangeThumb }
	}

	&::-ms-thumb {
		${ rangeThumb }
	}

	&::-webkit-slider-runnable-track {
		${ rangeTrack }
	}

	&::-moz-range-track {
		${ rangeTrack }
	}

	&::-ms-track {
		${ rangeTrack }
	}
`;

const RectangleIcon = styled( Rectangle )`
	width: ${ ( { isLarge } ) => isLarge ? '20px' : '12px' };
	height: ${ ( { isLarge } ) => isLarge ? '32px' : '20px' };
	margin-top: ${ ( { isLarge } ) => isLarge ? '0' : '6px' };
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
			<RectangleIcon isLarge />
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
