/**
 * Internal dependencies
 */
import { getErrorSourceTitle } from '../get-error-source-title';

describe('getErrorSorceTitle', () => {
	it('returns an empty string if nothing is passed', () => {
		expect(getErrorSourceTitle(null)).toBe('');
		expect(getErrorSourceTitle([])).toBe('');
	});

	it('returns a plugin name if one plugin matches', () => {
		expect(
			getErrorSourceTitle([
				{
					type: 'plugin',
					name: 'test-plugin',
				},
			])
		).toBe('Test plugin');

		expect(
			getErrorSourceTitle([
				{
					type: 'mu-plugin',
					name: 'test-mu-plugin',
				},
			])
		).toBe('Test MU plugin');
	});

	it('returns generic text with count if multiple plugins', () => {
		expect(
			getErrorSourceTitle([
				{
					type: 'plugin',
					name: 'test-plugin',
				},
				{
					type: 'plugin',
					name: 'test-plugin-2',
				},
				{
					type: 'mu-plugin',
					name: 'test-mu-plugin',
				},
				{
					type: 'mu-plugin',
					name: 'test-mu-plugin-2',
				},
			])
		).toBe('Plugins (2), Must-use plugins (2)');
	});

	it('does not return Gutenberg if it is not the only plugin', () => {
		expect(
			getErrorSourceTitle([
				{
					type: 'plugin',
					name: 'gutenberg',
				},
				{
					type: 'plugin',
					name: 'test-plugin',
				},
			])
		).toBe('Test plugin');

		expect(
			getErrorSourceTitle([
				{
					type: 'plugin',
					name: 'gutenberg',
				},
				{
					type: 'mu-plugin',
					name: 'test-mu-plugin',
				},
			])
		).toBe('Test MU plugin');
	});

	it('returns theme name if theme is source', () => {
		expect(
			getErrorSourceTitle([
				{
					type: 'theme',
					name: 'test-theme',
				},
			])
		).toBe('Test theme');
	});

	it('returns inactive theme if inactive theme is source', () => {
		expect(
			getErrorSourceTitle([
				{
					type: 'theme',
					name: 'test-other-theme',
				},
			])
		).toBe('Inactive theme(s)');
	});

	it('returns block name for block', () => {
		expect(
			getErrorSourceTitle([
				{
					block_name: 'Some Block',
				},
			])
		).toBe('Some Block');
	});

	it('returns Embed for embed', () => {
		expect(
			getErrorSourceTitle([
				{
					type: 'embed',
					name: 'test-theme',
				},
			])
		).toBe('Embed');
	});

	it('returns Core for core', () => {
		expect(
			getErrorSourceTitle([
				{
					type: 'unknown-type',
					name: 'core source',
				},
				{
					type: 'core',
					name: 'test-theme',
				},
			])
		).toBe('Core');
	});

	it('returns Unknown for unknown sources', () => {
		expect(
			getErrorSourceTitle([
				{
					type: 'unknown-type',
					name: 'core source',
				},
			])
		).toBe('Unknown');
	});
});
