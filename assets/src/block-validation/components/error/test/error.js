/**
 * External dependencies
 */
import { render, act, fireEvent } from '@testing-library/react';
import { noop } from 'lodash';
import {
	VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
	VALIDATION_ERROR_ACK_REJECTED_STATUS,
	VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
	VALIDATION_ERROR_NEW_REJECTED_STATUS,
} from 'amp-block-validation';
import { beforeAll, describe, expect, it } from '@jest/globals';

/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';
import { registerBlockType, createBlock } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import { Error } from '../index';
import { store as blockValidationStore } from '../../../store';

let pluginBlock, muPluginBlock, themeBlock, coreBlock, unknownBlock;

const TEST_PLUGIN_BLOCK = 'my-plugin/test-block';
const TEST_MU_PLUGIN_BLOCK = 'my-mu-plugin/test-block';
const TEST_THEME_BLOCK = 'my-theme/test-block';
const TEST_CORE_BLOCK = 'core/test-block';
const TEST_UNKNOWN_BLOCK = 'unknown/test-block';

global.URL = class {};

function registerBlockTypes() {
	registerBlockType(TEST_PLUGIN_BLOCK, {
		attributes: {},
		save: noop,
		category: 'widgets',
		title: 'test plugin block',
	});

	registerBlockType(TEST_MU_PLUGIN_BLOCK, {
		attributes: {},
		save: noop,
		category: 'widgets',
		title: 'test mu-plugin block',
	});

	registerBlockType(TEST_THEME_BLOCK, {
		attributes: {},
		save: noop,
		category: 'widgets',
		title: 'test theme block',
	});

	registerBlockType(TEST_CORE_BLOCK, {
		attributes: {},
		save: noop,
		category: 'widgets',
		title: 'test core block',
	});

	registerBlockType(TEST_UNKNOWN_BLOCK, {
		attributes: {},
		save: noop,
		category: 'widgets',
		title: 'test unknown block',
	});
}

function createTestStoreAndBlocks() {
	pluginBlock = createBlock(TEST_PLUGIN_BLOCK, {});
	muPluginBlock = createBlock(TEST_MU_PLUGIN_BLOCK, {});
	themeBlock = createBlock(TEST_THEME_BLOCK, {});
	coreBlock = createBlock(TEST_CORE_BLOCK, {});
	unknownBlock = createBlock(TEST_UNKNOWN_BLOCK, {});

	dispatch('core/block-editor').insertBlocks([
		pluginBlock,
		muPluginBlock,
		themeBlock,
		coreBlock,
		unknownBlock,
	]);

	dispatch(blockValidationStore).setReviewLink('http://site.test/wp-admin');

	dispatch(blockValidationStore).setValidationErrors([
		{
			clientId: pluginBlock.clientId,
			code: 'DISALLOWED_TAG',
			status: 3,
			term_id: 12,
			title: 'Invalid script: <code>jquery.js</code>',
			error: {
				type: 'js_error',
				sources: [],
			},
		},
		{
			clientId: muPluginBlock.clientId,
			code: 'DISALLOWED_TAG',
			status: 3,
			term_id: 12,
			title: 'Invalid script: <code>jquery.js</code>',
			error: {
				type: 'js_error',
				sources: [],
			},
		},
		{
			clientId: themeBlock.clientId,
			code: 'DISALLOWED_TAG',
			status: 3,
			term_id: 12,
			title: 'Invalid script: <code>jquery.js</code>',
			error: {
				type: 'js_error',
				sources: [],
			},
		},
		{
			clientId: coreBlock.clientId,
			code: 'DISALLOWED_TAG',
			status: 3,
			term_id: 12,
			title: 'Invalid script: <code>jquery.js</code>',
			error: {
				type: 'js_error',
				sources: [],
			},
		},
		{
			clientId: unknownBlock.clientId,
			code: 'DISALLOWED_TAG',
			status: 3,
			term_id: 12,
			title: 'Invalid script: <code>jquery.js</code>',
			error: {
				type: 'js_error',
				sources: [],
			},
		},
	]);
}

function getTestBlock(type) {
	let testBlock;

	switch (type) {
		case 'plugin':
		case 'removed':
			testBlock = pluginBlock;
			break;

		case 'mu-plugin':
			testBlock = muPluginBlock;
			break;

		case 'theme':
			testBlock = themeBlock;
			break;

		case 'core':
			testBlock = coreBlock;
			break;

		case 'unknown':
			testBlock = unknownBlock;
			break;

		default:
			testBlock = {};
	}

	return testBlock?.clientId || null;
}

function getErrorTypeClassName(status) {
	return [
		VALIDATION_ERROR_NEW_REJECTED_STATUS,
		VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
	].includes(status)
		? 'new'
		: 'reviewed';
}

describe('Error', () => {
	beforeAll(() => {
		registerBlockTypes();
		createTestStoreAndBlocks();
	});

	it.each(
		[
			VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
			VALIDATION_ERROR_ACK_REJECTED_STATUS,
			VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
			VALIDATION_ERROR_NEW_REJECTED_STATUS,
		].map((status) => [
			status,
			() => (
				<Error
					status={status}
					term_id={12}
					title="My test error"
					error={{ type: 'js_error', sources: [] }}
				/>
			),
		])
	)(
		'errors with no associated blocks work correctly',
		(status, ErrorComponent) => {
			const { container } = render(<ErrorComponent />);

			expect(container.firstChild.classList).toContain('amp-error');
			expect(
				container.querySelectorAll(
					`.amp-error--${getErrorTypeClassName(status)}`
				)
			).toHaveLength(1);
			expect(
				container.querySelector('.amp-error__details-link')
			).toBeNull();
			expect(
				container.querySelector(
					`.amp-error--${getErrorTypeClassName(status)} button`
				)
			).not.toBeNull();

			fireEvent.click(
				container.querySelector(
					`.amp-error--${getErrorTypeClassName(status)} button`
				)
			);

			expect(
				container.querySelector('.amp-error__block-type-icon')
			).toBeNull();
			expect(
				container.querySelector('.amp-error__details-link')
			).not.toBeNull();
			expect(
				container.querySelector('.amp-error__select-block')
			).toBeNull();
		}
	);

	it.each(
		[
			VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
			VALIDATION_ERROR_ACK_REJECTED_STATUS,
			VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
			VALIDATION_ERROR_NEW_REJECTED_STATUS,
		].map((status) => [
			status,
			() => (
				<Error
					clientId={pluginBlock.clientId}
					status={status}
					term_id={12}
					title="My test error"
					error={{ type: 'js_error', sources: [] }}
				/>
			),
		])
	)(
		'errors with associated blocks work correctly',
		(status, ErrorComponent) => {
			const { container } = render(<ErrorComponent />);

			expect(container.firstChild.classList).toContain('amp-error');
			expect(
				container.querySelectorAll(
					`.amp-error--${getErrorTypeClassName(status)}`
				)
			).toHaveLength(1);
			expect(
				container.querySelector('.amp-error__details-link')
			).toBeNull();
			expect(
				container.querySelector(
					`.amp-error--${getErrorTypeClassName(status)} button`
				)
			).not.toBeNull();

			fireEvent.click(
				container.querySelector(
					`.amp-error--${getErrorTypeClassName(status)} button`
				)
			);

			expect(
				container.querySelector('.amp-error__block-type-icon')
			).not.toBeNull();
			expect(
				container.querySelector('.amp-error__details-link')
			).not.toBeNull();
			expect(
				container.querySelector('.amp-error__select-block')
			).not.toBeNull();
		}
	);

	it.each(
		[
			VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
			VALIDATION_ERROR_ACK_REJECTED_STATUS,
			VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
			VALIDATION_ERROR_NEW_REJECTED_STATUS,
		].map((status) => [
			status,
			() => (
				<Error
					clientId={pluginBlock.clientId}
					status={status}
					term_id={12}
					title="My test error"
					error={{ type: 'js_error', sources: [] }}
				/>
			),
		])
	)('errors with removed blocks work correctly', (status, ErrorComponent) => {
		act(() => {
			dispatch('core/block-editor').removeBlock(
				pluginBlock.clientId,
				false
			);
		});

		const { container } = render(<ErrorComponent />);

		expect(container.firstChild.classList).toContain('amp-error');
		expect(
			container.querySelectorAll(
				`.amp-error--${getErrorTypeClassName(status)}`
			)
		).toHaveLength(1);
		expect(container.querySelector('.amp-error--removed')).not.toBeNull();
		expect(container.querySelector('.amp-error__details-link')).toBeNull();
		expect(
			container.querySelector('.amp-error--removed button')
		).not.toBeNull();

		fireEvent.click(container.querySelector('.amp-error--removed button'));

		expect(
			container.querySelector('.amp-error__block-type-icon')
		).toBeNull();
		expect(
			container.querySelector('.amp-error__details-link')
		).not.toBeNull();
		expect(container.querySelector('.amp-error__select-block')).toBeNull();
	});
});

describe('ErrorTypeIcon', () => {
	it.each([
		'js_error',
		'html_attribute_error',
		'html_element_error',
		'css_error',
	])('shows the correct error icon', (errorType) => {
		const { container } = render(
			<Error
				status={3}
				term_id={12}
				title="My test error"
				error={{ type: errorType, sources: [] }}
			/>
		);

		expect(
			container.querySelector(
				`.amp-error__error-type-icon--${errorType.replace(/_/g, '-')}`
			)
		).not.toBeNull();
	});

	it('shows no error icon for unknown error type', () => {
		const { container } = render(
			<Error
				status={3}
				term_id={12}
				title="My test error"
				error={{ type: 'unknown_error', sources: [] }}
			/>
		);

		expect(
			container.querySelector('svg[class^=amp-error__error-type-icon]')
		).toBeNull();
	});
});

describe('ErrorContent', () => {
	beforeAll(() => {
		createTestStoreAndBlocks();
	});

	/* eslint-disable jest/no-conditional-in-test */
	it.each(
		[null, 'plugin', 'mu-plugin', 'theme', 'core', 'removed'].reduce(
			(collection, testBlockSource) => [
				...collection,
				...[
					VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
					VALIDATION_ERROR_ACK_REJECTED_STATUS,
					VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
					VALIDATION_ERROR_NEW_REJECTED_STATUS,
				].map((status) => [testBlockSource, status]),
			],
			[]
		)
	)(
		'shows expected content based on whether or not the error has an associated block',
		(testBlockSource, status) => {
			const clientId = getTestBlock(testBlockSource);

			const { container } = render(
				<Error
					clientId={clientId}
					status={status}
					term_id={12}
					title="My test error"
					error={{ type: 'js_error', sources: [] }}
				/>
			);

			fireEvent.click(container.querySelector(`.components-button`));

			expect(container.innerHTML).toContain('Markup status');

			if ('removed' === testBlockSource) {
				act(() => {
					dispatch('core/block-editor').removeBlock(clientId, false);
				});
				// eslint-disable-next-line jest/no-conditional-expect
				expect(container.innerHTML).toContain(
					'error is no longer detected'
				);
				return;
			}

			if (null === clientId) {
				// eslint-disable-next-line jest/no-conditional-expect
				expect(container.innerHTML).toContain('outside the content');
				return;
			}

			expect(container.innerHTML).toContain('<dt>Source');
			expect(container.innerHTML).not.toContain('outside the content');

			/* eslint-disable jest/no-conditional-expect */
			switch (testBlockSource) {
				case 'plugin':
					expect(container.innerHTML).toContain('test plugin block');
					expect(container.innerHTML).toContain('My plugin (plugin)');
					break;

				case 'mu-plugin':
					expect(container.innerHTML).toContain(
						'test mu-plugin block'
					);
					expect(container.innerHTML).toContain(
						'My MU plugin (must-use plugin)'
					);
					break;

				case 'theme':
					expect(container.innerHTML).toContain('test theme block');
					expect(container.innerHTML).toContain('My theme (theme)');
					break;

				case 'core':
					expect(container.innerHTML).toContain('test core block');
					expect(container.innerHTML).toContain('<dd>WordPress core');
					break;

				default:
					break;
			}
			/* eslint-enable jest/no-conditional-expect */

			expect(container.innerHTML).toContain(
				[
					VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
					VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
				].includes(status)
					? 'Removed'
					: 'Kept'
			);

			expect(container.innerHTML).not.toContain(
				[
					VALIDATION_ERROR_ACK_REJECTED_STATUS,
					VALIDATION_ERROR_NEW_REJECTED_STATUS,
				].includes(status)
					? 'Removed'
					: 'Kept'
			);

			fireEvent.click(
				container.querySelector('.amp-error__select-block')
			);

			expect(
				select('core/block-editor').getSelectedBlock().clientId
			).toBe(clientId);
		}
	);
	/* eslint-enable jest/no-conditional-in-test */
});
