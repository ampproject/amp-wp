/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import withOverlay from '../overlay/withOverlay';
import Selection from './selection';
import useCanvas from './useCanvas';
import Element from './element';

const Background = withOverlay( styled.div.attrs( { className: 'container' } )`
	background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
	position: relative;
	width: 100%;
	height: 100%;
` );

function Page() {
	const {
		state: { currentPage },
	} = useStory();

	const {
		actions: { setPageContainer },
	} = useCanvas();

	return (
		<Background ref={ setPageContainer }>
			{ currentPage && currentPage.elements.map( ( { id, ...rest } ) => {
				return (
					<Element
						key={ id }
						element={ { id, ...rest } }
					/>
				);
			} ) }

			<Selection />
		</Background>
	);
}

export default Page;
