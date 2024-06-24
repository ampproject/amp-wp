/**
 * External dependencies
 */
import { create } from 'react-test-renderer';
import { describe, expect, it } from '@jest/globals';

/**
 * Internal dependencies
 */
import { AMPInfo } from '..';
import { IconMobile } from '../../svg/icon-mobile';

describe('AMPInfo', () => {
	it('matches snapshots', () => {
		let wrapper = create(<AMPInfo />);
		expect(wrapper.toJSON()).toMatchSnapshot();

		wrapper = create(
			<AMPInfo
				className="my-class"
				icon={(props) => <IconMobile {...props} />}
			>
				{'Component children'}
			</AMPInfo>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();
	});
});
