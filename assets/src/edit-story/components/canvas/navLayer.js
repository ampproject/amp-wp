/**
 * Internal dependencies
 */
import Header from '../header';
import PageMenu from './pagemenu';
import PageNav from './pagenav';
import Carousel from './carousel';
import {
	Layer,
	HeadArea,
	MenuArea,
	NavPrevArea,
	NavNextArea,
	CarouselArea,
} from './layout';

function NavLayer() {
	return (
		<Layer pointerEvents={ false } onMouseDown={ ( evt ) => evt.stopPropagation() }>
			<HeadArea pointerEvents={ true }>
				<Header />
			</HeadArea>
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
