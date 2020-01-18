/**
 * Internal dependencies
 */
import PageMenu from './pagemenu';
import PageNav from './pagenav';
import Carrousel from './carrousel';
import {
	Layer,
	MenuArea,
	NavPrevArea,
	NavNextArea,
	CarouselArea,
} from './layout';

function NavLayer() {
	return (
		<Layer pointerEvents={ false } onMouseDown={ ( evt ) => evt.stopPropagation() }>
			<MenuArea pointerEvents={ true }>
				<PageMenu />
			</MenuArea>
			<NavPrevArea>
				<PageNav isNext={ false } />
			</NavPrevArea>
			<NavNextArea>
				<PageNav />
			</NavNextArea>
			<CarouselArea pointerEvents={ true }>
				<Carrousel />
			</CarouselArea>
		</Layer>
	);
}

export default NavLayer;
