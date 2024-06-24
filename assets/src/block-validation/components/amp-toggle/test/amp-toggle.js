/**
 * External dependencies
 */
import { render, fireEvent } from '@testing-library/react';
import { beforeEach, describe, expect, it, jest } from '@jest/globals';

/**
 * Internal dependencies
 */
import AMPToggle from '../index';
import { useAMPDocumentToggle } from '../../../hooks/use-amp-document-toggle';

jest.mock('../../../hooks/use-amp-document-toggle', () => ({
	useAMPDocumentToggle: jest.fn(),
}));

describe('AMPToggle', () => {
	const toggleAMP = jest.fn();

	function setupHooks(overrides) {
		useAMPDocumentToggle.mockImplementation(() => ({
			isAMPEnabled: false,
			toggleAMP,
			...overrides,
		}));
	}

	beforeEach(() => {
		jest.clearAllMocks();
	});

	it('renders a toggle that reacts to changes', () => {
		setupHooks({
			isAMPEnabled: true,
		});

		const { container } = render(<AMPToggle />);

		expect(
			container.querySelector('input[type="checkbox"]')
		).not.toBeNull();
		expect(container.querySelector('input[type="checkbox"]').checked).toBe(
			true
		);

		fireEvent.click(container.querySelector('input[type="checkbox"]'));

		expect(toggleAMP).toHaveBeenCalledTimes(1);
	});
});
