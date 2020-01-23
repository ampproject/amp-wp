/**
 * External dependencies
 */
import styled from 'styled-components';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import useStory from '../../../app/story/useStory';
import { getDefinitionForType } from '../../../elements';
import { PAGE_WIDTH } from '../../../constants';

const PAGE_WIDTH_MARGIN = 5;
const PAGE_BORDER_WIDTH = 3;

const Page = styled.button`
	padding: 0;
	margin: 0 ${ PAGE_WIDTH_MARGIN }px;
	border: ${ PAGE_BORDER_WIDTH } px solid ${ ( { isActive, theme } ) => isActive ? theme.colors.selection : theme.colors.bg.v1 };
	height: ${ ( { height } ) => height }px;
	width: ${ ( { width } ) => width }px;
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	flex: none;
	transition: width .2s ease, height .2s ease;
`;

const PreviewWrapper = styled.div`
	height: 100%;
    position: relative;
    overflow: hidden;
`;

function PagePreview( { index, forwardedRef, ...props } ) {
	const { state: { pages } } = useStory();
	const page = pages[ index ];
	const { width } = props;
	// Deduct the margin and page border for more accurate calculation based on the inner element.
	const sizeMultiplier = ( width - PAGE_WIDTH_MARGIN - PAGE_BORDER_WIDTH ) / PAGE_WIDTH;
	return (
		<Page { ...props } ref={ forwardedRef } >
			<PreviewWrapper>
				{ page.elements.map( ( { type, ...rest } ) => {
					const { id: elId } = rest;
					// eslint-disable-next-line @wordpress/no-unused-vars-before-return
					const { Output } = getDefinitionForType( type );
					return <Output previewSizeMultiplier={ sizeMultiplier } isPreview={ true } key={ 'element-' + elId } { ...rest } />;
				} ) }
			</PreviewWrapper>
		</Page>
	);
}

PagePreview.propTypes = {
	index: PropTypes.number.isRequired,
	forwardedRef: PropTypes.func,
	width: PropTypes.number.isRequired,
};

export default PagePreview;
