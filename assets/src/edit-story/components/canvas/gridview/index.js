/**
 * External dependencies
 */
import styled from 'styled-components';
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
import RectangleIcon from './rectangle.svg';

const PAGE_WIDTH = 90;
const PAGE_HEIGHT = 160;
const NUMBER_OF_COLUMNS = {
	1: 16,
	2: 8,
	3: 4,
};

const Wrapper = styled.div`
	position: relative;
	display: grid;
	grid-template-columns: ${ ( { scale } ) => `repeat(${ NUMBER_OF_COLUMNS[ scale ] }, ${ scale * PAGE_WIDTH }px)` };
	grid-gap: 20px;
	justify-content: center;
	justify-items: center;
    align-items: center;
`;

const Page = styled.button`
	padding: 0;
	margin: 0 5px;
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

	svg {
	}
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
						<Page
							key={ index }
							scale={ zoomLevel }
							isActive={ isCurrentPage }
							aria-label={ isCurrentPage ? sprintf( __( 'Page %s (current page)', 'amp' ), index + 1 ) : sprintf( __( 'Page %s', 'amp' ), index + 1 ) }
						/>
					);
				} ) }
			</Wrapper>
		</>
	);
}

export default GridView;
