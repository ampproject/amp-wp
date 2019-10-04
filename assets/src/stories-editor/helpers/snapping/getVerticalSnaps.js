/**
 * Internal dependencies
 */
import { STORY_PAGE_INNER_HEIGHT, STORY_PAGE_INNER_WIDTH } from '../../constants';
import getSnapCalculatorByDimension from './getSnapCalculatorByDimension';

const getHorizontalLine = (
	offsetY,
	start = 0,
	end = STORY_PAGE_INNER_WIDTH,
) => [ [ start, offsetY ], [ end, offsetY ] ];

const getVerticalSnaps = getSnapCalculatorByDimension(
	getHorizontalLine,
	STORY_PAGE_INNER_HEIGHT,
	[ 'top', 'bottom' ],
	[ 'left', 'right' ],
);

export default getVerticalSnaps;
