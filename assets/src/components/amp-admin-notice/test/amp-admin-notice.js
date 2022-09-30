/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';
import { create } from 'react-test-renderer';

/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import {
	AmpAdminNotice,
	AMP_ADMIN_NOTICE_TYPE_INFO,
	AMP_ADMIN_NOTICE_TYPE_SUCCESS,
	AMP_ADMIN_NOTICE_TYPE_WARNING,
	AMP_ADMIN_NOTICE_TYPE_ERROR,
} from '..';

let container;

describe('AmpAdminNotice', () => {
	beforeEach(() => {
		container = document.createElement('div');
		document.body.appendChild(container);
	});

	afterEach(() => {
		document.body.removeChild(container);
		container = null;
	});

	it('matches the snapshot', () => {
		const wrapper = create(<AmpAdminNotice />);

		expect(wrapper.toJSON()).toMatchSnapshot();
	});

	it('renders a plain AMP admin notice', () => {
		act(() => {
			render(<AmpAdminNotice>
{'Content'}
</AmpAdminNotice>, container);
		});

		expect(container.querySelector('.amp-admin-notice')).not.toBeNull();
		expect(container.querySelector('.amp-admin-notice').textContent).toBe(
			'Content'
		);
	});

	it('renders a dismissible AMP admin notice', () => {
		const onDismiss = jest.fn();

		act(() => {
			render(
				<AmpAdminNotice isDismissible={true} onDismiss={onDismiss} />,
				container
			);
		});

		expect(
			container.querySelector('.amp-admin-notice--dismissible')
		).not.toBeNull();
		expect(
			container.querySelector('.amp-admin-notice__dismiss')
		).not.toBeNull();

		act(() => {
			container.querySelector('.amp-admin-notice__dismiss').click();
		});

		expect(onDismiss).toHaveBeenCalledTimes(1);
	});

	it.each([
		[AMP_ADMIN_NOTICE_TYPE_INFO],
		[AMP_ADMIN_NOTICE_TYPE_SUCCESS],
		[AMP_ADMIN_NOTICE_TYPE_WARNING],
		[AMP_ADMIN_NOTICE_TYPE_ERROR],
	])('renders a "%s" AMP admin notice type', (type) => {
		act(() => {
			render(<AmpAdminNotice type={type} />, container);
		});

		expect(
			container.querySelector(`.amp-admin-notice--${type}`)
		).not.toBeNull();
	});
});
