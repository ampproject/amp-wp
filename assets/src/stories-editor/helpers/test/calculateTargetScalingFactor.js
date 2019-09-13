/**
 * Internal dependencies
 */
import { calculateTargetScalingFactor } from '../';
import { STORY_PAGE_INNER_HEIGHT, STORY_PAGE_INNER_WIDTH } from '../../constants';

describe( 'calculateTargetScalingFactor', () => {
	it.each( [
		[ STORY_PAGE_INNER_WIDTH, STORY_PAGE_INNER_HEIGHT, 1.25 ],
		[ STORY_PAGE_INNER_WIDTH, STORY_PAGE_INNER_HEIGHT / 2, 2.5 ],
		[ STORY_PAGE_INNER_WIDTH / 2, STORY_PAGE_INNER_HEIGHT, 2.5 ],
		[ 10000, 10000, 1 ],
	] )( 'should scale the target accordingly',
		( width, height, expected ) => {
			expect( calculateTargetScalingFactor( width, height ) ).toBe( expected );
		}
	);
} );
