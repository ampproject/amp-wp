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
import { PAGE_NAV_BUTTON_WIDTH, PAGE_NAV_PADDING } from '../../../constants';
import { useStory } from '../../../app';

const Wrapper = styled.div`
	display: flex;
	align-items: center;
	justify-content: flex-start;
	height: 100%;
	color:  ${ ( { theme } ) => theme.colors.fg.v1 };
`;

const Space = styled.div`
	flex: 0 1 ${ ( { isNext } ) => isNext ? PAGE_NAV_PADDING : 0 }px;
`;

const NavButton = styled.button`
	display: block;
	background: transparent;
	border: none;
	width: ${ PAGE_NAV_BUTTON_WIDTH }px;
	height: 40px;
	visibility: ${ ( { displayNav } ) => displayNav ? 'visible' : 'hidden' };
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

function PageNav( { isNext } ) {
	const { state: { pages, currentPageIndex }, actions: { setCurrentPage } } = useStory();
	const handleClick = useCallback( () => {
		const newPage = isNext ? pages[ currentPageIndex + 1 ] : pages[ currentPageIndex - 1 ];
		if ( newPage ) {
			setCurrentPage( { pageId: newPage.id } );
		}
	}, [ setCurrentPage, currentPageIndex, isNext, pages ] );
	const displayNav = ( isNext && currentPageIndex < pages.length - 1 ) || ( ! isNext && currentPageIndex > 0 );
	return (
		<Wrapper>
			<Space isNext={ isNext } />
			<NavButton disabled={ ! displayNav } displayNav={ displayNav } aria-label={ isNext ? 'Next Page' : 'Previous Page' } onClick={ handleClick }>
				{ isNext ? <IconNext /> : <IconPrev /> }
			</NavButton>
		</Wrapper>
	);
}

PageNav.propTypes = {
	isNext: PropTypes.bool,
};

PageNav.defaultProps = {
	isNext: true,
};

export default PageNav;
