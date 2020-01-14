/**
 * External dependencies
 */
import styled, { css } from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useStory } from '../../../app';

const Wrapper = styled.div`
	display: flex;
	align-items: center;
	justify-content: flex-start;
	height: 100%;
	color:  ${ ( { theme } ) => theme.colors.fg.v1 };
`;

const Space = styled.div`
	flex: 0 1 ${ ( { direction } ) => 'next' === direction ? 60 : 115 }px;
`;

const IconStyle = css`
	border: solid ${ ( { theme } ) => theme.colors.fg.v1 };
	border-width: 0 1px 1px 0;
	display: inline-block;
	padding: 3px;
	cursor: pointer;
	opacity: .25;
	display: flex;
	align-items: center;
	justify-content: center;
	color: inherit;
	height: 25px;
	width: 25px;
	&:hover {
		color: inherit;
		opacity: 1;
	}
`;

const IconNext = styled.i`
	${ IconStyle }
	transform: rotate(-45deg);
`;

const IconPrev = styled.i`
	${ IconStyle }
	transform: rotate(135deg);
`;

function PageNav( { next, prev } ) {
	const { actions: { setCurrentPageByIndex }, state: { pages, currentPageIndex } } = useStory();
	const handleClick = useCallback( () => {
		if ( next ) {
			setCurrentPageByIndex( currentPageIndex + 1 );
		} else if ( prev ) {
			setCurrentPageByIndex( currentPageIndex - 1 );
		}
	}, [ setCurrentPageByIndex, currentPageIndex, next, prev ] );
	return (
		<Wrapper>
			<Space direction={ next ? 'next' : 'prev' } />
			{ next && currentPageIndex < pages.length - 1 && ( <IconNext onClick={ handleClick } /> ) }
			{ prev && currentPageIndex > 0 && ( <IconPrev onClick={ handleClick } /> ) }
		</Wrapper>
	);
}

PageNav.propTypes = {
	next: PropTypes.bool,
	prev: PropTypes.bool,
};

PageNav.defaultProps = {
	next: false,
	prev: false,
};

export default PageNav;
