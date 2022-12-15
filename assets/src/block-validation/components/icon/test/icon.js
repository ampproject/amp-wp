/**
 * External dependencies
 */
import { render } from '@testing-library/react';

/**
 * Internal dependencies
 */
import { MoreMenuIcon, ToolbarIcon, StatusIcon } from '../index';

describe('Icons', () => {
	it('renders a toolbar icon without AMP broken and no badge', () => {
		const { container } = render(<ToolbarIcon broken={false} count={0} />);

		expect(container.querySelector('.amp-toolbar-icon')).not.toBeNull();
		expect(
			container.querySelector('.amp-toolbar-icon--has-badge')
		).toBeNull();
		expect(container.querySelector('.amp-toolbar-broken-icon')).toBeNull();
		expect(
			container.querySelector('.amp-toolbar-broken-icon--has-badge')
		).toBeNull();
	});

	it('renders a toolbar icon without AMP broken and with a badge', () => {
		const { container } = render(<ToolbarIcon broken={false} count={1} />);

		expect(container.querySelector('.amp-toolbar-icon')).not.toBeNull();
		expect(
			container.querySelector('.amp-toolbar-icon--has-badge')
		).not.toBeNull();
		expect(container.querySelector('.amp-toolbar-broken-icon')).toBeNull();
		expect(
			container.querySelector('.amp-toolbar-broken-icon--has-badge')
		).toBeNull();
	});

	it('renders a toolbar icon with AMP broken and with no badge', () => {
		const { container } = render(<ToolbarIcon broken={true} count={0} />);

		expect(container.querySelector('.amp-toolbar-icon')).toBeNull();
		expect(
			container.querySelector('.amp-toolbar-icon--has-badge')
		).toBeNull();
		expect(
			container.querySelector('.amp-toolbar-broken-icon')
		).not.toBeNull();
		expect(
			container.querySelector('.amp-toolbar-broken-icon--has-badge')
		).toBeNull();
	});

	it('renders a toolbar icon with AMP broken and with a badge', () => {
		const { container } = render(<ToolbarIcon broken={true} count={1} />);

		expect(container.querySelector('.amp-toolbar-icon')).toBeNull();
		expect(
			container.querySelector('.amp-toolbar-icon--has-badge')
		).toBeNull();
		expect(
			container.querySelector('.amp-toolbar-broken-icon')
		).not.toBeNull();
		expect(
			container.querySelector('.amp-toolbar-broken-icon--has-badge')
		).not.toBeNull();
	});

	it('renders the MoreMenuIcon', () => {
		const { container } = render(<MoreMenuIcon broken={true} count={1} />);

		expect(container.querySelector('.amp-toolbar-icon')).not.toBeNull();
		expect(
			container.querySelector('.amp-toolbar-icon--has-badge')
		).toBeNull();
	});

	it('renders the StatusIcon', () => {
		const { container } = render(<StatusIcon broken={false} />);

		expect(container.querySelector('.amp-status-icon')).not.toBeNull();
		expect(container.querySelector('.amp-status-icon--broken')).toBeNull();
	});

	it('renders the broken StatusIcon', () => {
		const { container } = render(<StatusIcon broken={true} />);

		expect(
			container.querySelector('.amp-status-icon--broken')
		).not.toBeNull();
	});
});
