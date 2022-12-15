/**
 * External dependencies
 */
import { render } from '@testing-library/react';
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import {
	AMPNotice,
	NOTICE_TYPE_SUCCESS,
	NOTICE_SIZE_LARGE,
	NOTICE_TYPE_ERROR,
	NOTICE_SIZE_SMALL,
	NOTICE_TYPE_INFO,
	NOTICE_TYPE_PLAIN,
} from '..';

describe('AMPNotice', () => {
	it('matches snapshots', () => {
		let wrapper = create(
			<AMPNotice type={NOTICE_TYPE_SUCCESS} size={NOTICE_SIZE_LARGE}>
				{'Component children'}
			</AMPNotice>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();

		wrapper = create(
			<AMPNotice type={NOTICE_TYPE_ERROR} size={NOTICE_SIZE_SMALL}>
				{'Component children'}
			</AMPNotice>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();
	});

	it('has correct classes', () => {
		let { container } = render(<AMPNotice>{'children'}</AMPNotice>);

		expect(container.querySelector('div').getAttribute('class')).toBe(
			'amp-notice amp-notice--info amp-notice--large'
		);

		container = render(
			<AMPNotice
				type={NOTICE_TYPE_SUCCESS}
				size={NOTICE_SIZE_LARGE}
				className="my-cool-class"
			>
				{'children'}
			</AMPNotice>
		).container;

		expect(container.querySelector('div').getAttribute('class')).toBe(
			'my-cool-class amp-notice amp-notice--success amp-notice--large'
		);

		container = render(
			<AMPNotice type={NOTICE_TYPE_INFO} size={NOTICE_SIZE_SMALL}>
				{'children'}
			</AMPNotice>
		).container;

		expect(container.querySelector('div').getAttribute('class')).toBe(
			'amp-notice amp-notice--info amp-notice--small'
		);

		container = render(
			<AMPNotice type={NOTICE_TYPE_PLAIN} size={NOTICE_SIZE_SMALL}>
				{'children'}
			</AMPNotice>
		).container;

		expect(container.querySelector('div').getAttribute('class')).toBe(
			'amp-notice amp-notice--plain amp-notice--small'
		);
	});
});
