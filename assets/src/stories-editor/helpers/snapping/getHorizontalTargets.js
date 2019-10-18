/**
 * Internal dependencies
 */
import { STORY_PAGE_INNER_HEIGHT, STORY_PAGE_INNER_WIDTH } from '../../constants';
import getSnapCalculatorByDimension from './getSnapCalculatorByDimension';

const getVerticalLine = (
	offsetX,
	start = 0,
	end = STORY_PAGE_INNER_HEIGHT,
) => [ [ offsetX, start ], [ offsetX, end ] ];

const getHorizontalTargets = getSnapCalculatorByDimension(
	getVerticalLine,
	STORY_PAGE_INNER_WIDTH,
	[ 'left', 'right' ],
	[ 'top', 'bottom' ],
);

export default getHorizontalTargets;
