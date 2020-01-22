/**
 * External dependencies
 */
import styled, { css } from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { useStory } from '../../../app/story';
import DraggablePage from '../draggablePage';
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

const RectangleIcon = styled.div`
	margin-top: ${ ( { isLarge } ) => isLarge ? '0' : '6px' };

	svg {
		width: ${ ( { isLarge } ) => isLarge ? '20px' : '12px' };
		height: ${ ( { isLarge } ) => isLarge ? '32px' : '20px' };
	}
`;

function RangeControl( { value, onChange } ) {
	return (
		<RangeInputWrapper>
			<RectangleIcon>
				<Rectangle />
			</RectangleIcon>
			<RangeInput
				min="1"
				max="3"
				steps="1"
				value={ value }
				onChange={ ( evt ) => onChange( Number( evt.target.value ) ) }
			/>
			<RectangleIcon isLarge>
				<Rectangle />
			</RectangleIcon>
		</RangeInputWrapper>
	);
}

RangeControl.propTypes = {
	value: PropTypes.number.isRequired,
	onChange: PropTypes.func.isRequired,
};

function GridView() {
	const { state: { pages, currentPageIndex } } = useStory();
	const [ zoomLevel, setZoomLevel ] = useState( 2 );

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
						<DraggablePage
							key={ index }
							ariaLabel={ isCurrentPage ?
								sprintf( __( 'Page %s (current page)', 'amp' ), index + 1 ) :
								sprintf( __( 'Go to page %s', 'amp' ), index + 1 )
							}
							isActive={ isCurrentPage }
							pageIndex={ index }
							width={ zoomLevel * PAGE_WIDTH }
							height={ zoomLevel * PAGE_HEIGHT }
						/>
					);
				} ) }
			</Wrapper>
		</>
	);
}

export default GridView;
