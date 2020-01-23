/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { PAGE_NAV_BUTTON_WIDTH } from '../../../constants';
import { useStory } from '../../../app';
import { LeftArrow, RightArrow } from '../../button';

const Wrapper = styled.div`
	display: flex;
	align-items: center;
	justify-content: ${ ( { isNext } ) => isNext ? 'flex-end' : 'flex-start' };
	color:  ${ ( { theme } ) => theme.colors.fg.v1 };
	width: ${ PAGE_NAV_BUTTON_WIDTH }px;
	height: ${ PAGE_NAV_BUTTON_WIDTH }px;
	& > * {
		pointer-events: initial;
	}
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
	const buttonProps = {
		isDisabled: ! displayNav,
		isHidden: ! displayNav,
		'aria-label': isNext ? __( 'Next Page', 'amp' ) : __( 'Previous Page', 'amp' ),
		onClick: handleClick,
		width: PAGE_NAV_BUTTON_WIDTH,
		height: PAGE_NAV_BUTTON_WIDTH,
	};
	return (
		<Wrapper isNext={ isNext }>
			{ isNext ? <RightArrow { ...buttonProps } /> : <LeftArrow { ...buttonProps } /> }
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
