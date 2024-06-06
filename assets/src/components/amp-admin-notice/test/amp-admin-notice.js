/**
 * External dependencies
 */
import { render, fireEvent } from '@testing-library/react';
import { create } from 'react-test-renderer';
import { describe, expect, it, jest } from '@jest/globals';

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

describe('AmpAdminNotice', () => {
	it('matches the snapshot', () => {
		const wrapper = create(<AmpAdminNotice />);

		expect(wrapper.toJSON()).toMatchSnapshot();
	});

	it('renders a plain AMP admin notice', () => {
		const { container } = render(
			<AmpAdminNotice>{'Content'}</AmpAdminNotice>
		);

		expect(container.querySelector('.amp-admin-notice')).not.toBeNull();
		expect(container.querySelector('.amp-admin-notice').textContent).toBe(
			'Content'
		);
	});

	it('renders a dismissible AMP admin notice', () => {
		const onDismiss = jest.fn();

		const { container } = render(
			<AmpAdminNotice isDismissible={true} onDismiss={onDismiss} />
		);

		expect(
			container.querySelector('.amp-admin-notice--dismissible')
		).not.toBeNull();
		expect(
			container.querySelector('.amp-admin-notice__dismiss')
		).not.toBeNull();

		fireEvent.click(container.querySelector('.amp-admin-notice__dismiss'));

		expect(onDismiss).toHaveBeenCalledTimes(1);
	});

	it.each([
		[AMP_ADMIN_NOTICE_TYPE_INFO],
		[AMP_ADMIN_NOTICE_TYPE_SUCCESS],
		[AMP_ADMIN_NOTICE_TYPE_WARNING],
		[AMP_ADMIN_NOTICE_TYPE_ERROR],
	])('renders a "%s" AMP admin notice type', (type) => {
		const { container } = render(<AmpAdminNotice type={type} />);

		expect(
			container.querySelector(`.amp-admin-notice--${type}`)
		).not.toBeNull();
	});
});
