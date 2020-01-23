/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import useStory from '../../../app/story/useStory';
import { getDefinitionForType } from '../../../elements';

const Page = styled.button`
	padding: 0;
	margin: 0 5px;
	border: 3px solid ${ ( { isActive, theme } ) => isActive ? theme.colors.selection : theme.colors.bg.v1 };
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
	return (
		<Page { ...props } ref={ forwardedRef } >
			<PreviewWrapper>
				{ page.elements.map( ( { type, ...rest } ) => {
					const { id: elId } = rest;
					// eslint-disable-next-line @wordpress/no-unused-vars-before-return
					const { Save } = getDefinitionForType( type );
					return <Save isPreview="true" key={ 'element-' + elId } { ...rest } />;
				} ) }
			</PreviewWrapper>
		</Page>
	);
}

export default PagePreview;
