/**
 * Internal dependencies
 */
import PageMenu from './pagemenu';
import PageNav from './pagenav';
import Carousel from './carousel';
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
				<Carousel />
			</CarouselArea>
		</Layer>
	);
}

export default NavLayer;
