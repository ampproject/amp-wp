/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import withOverlay from '../overlay/withOverlay';
import { Layer, PageArea } from './layout';
import FrameElement from './frameElement';
import Selection from './selection';

const FramesPageArea = withOverlay( styled( PageArea ).attrs( { className: 'container', pointerEvents: true } )`
  background-color: ${ ( { theme } ) => theme.colors.fg.v1 };
` );

function FramesLayer() {
	const { state: { currentPage } } = useStory();

	return (
		<Layer pointerEvents={ false }>
			<FramesPageArea>
				{ currentPage && currentPage.elements.map( ( { id, ...rest } ) => {
					return (
						<FrameElement
							key={ id }
							element={ { id, ...rest } }
						/>
					);
				} ) }
				<Selection />
			</FramesPageArea>
		</Layer>
	);
}

export default FramesLayer;
