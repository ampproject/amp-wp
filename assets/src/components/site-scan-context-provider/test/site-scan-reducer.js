/**
 * Internal dependencies
 */
import {
	siteScanReducer,
	ACTION_SET_STATUS,
	ACTION_SCANNABLE_URLS_REQUEST,
	ACTION_SCANNABLE_URLS_RECEIVE,
	ACTION_SCAN_INITIALIZE,
	ACTION_SCAN_URL,
	ACTION_SCAN_RECEIVE_RESULTS,
	ACTION_SCAN_COMPLETE,
	ACTION_SCAN_CANCEL,
	STATUS_REQUEST_SCANNABLE_URLS,
	STATUS_FETCHING_SCANNABLE_URLS,
	STATUS_REFETCHING_PLUGIN_SUPPRESSION,
	STATUS_READY,
	STATUS_IDLE,
	STATUS_IN_PROGRESS,
	STATUS_COMPLETED,
	STATUS_FAILED,
	STATUS_CANCELLED,
	STATUS_SKIPPED,
} from '../index';

describe('siteScanReducer', () => {
	it('throws an error if an unhandled action type is dispatched', () => {
		expect(() => {
			siteScanReducer({}, { type: 'foobar' });
		}).toThrow('Unhandled action type: foobar');
	});

	/**
	 * STATUS_SKIPPED
	 */
	it.each([
		ACTION_SET_STATUS,
		ACTION_SCANNABLE_URLS_REQUEST,
		ACTION_SCANNABLE_URLS_RECEIVE,
		ACTION_SCAN_INITIALIZE,
		ACTION_SCAN_URL,
		ACTION_SCAN_RECEIVE_RESULTS,
		ACTION_SCAN_COMPLETE,
		ACTION_SCAN_CANCEL,
	])(
		'returns previous state for %s if the current status is STATUS_SKIPPED',
		(actionType) => {
			expect(
				siteScanReducer(
					{ status: STATUS_SKIPPED },
					{
						type: actionType,
					}
				)
			).toStrictEqual({ status: STATUS_SKIPPED });
		}
	);

	/**
	 * ACTION_SET_STATUS
	 */
	it('returns correct state for ACTION_SET_STATUS', () => {
		expect(
			siteScanReducer(
				{},
				{
					type: ACTION_SET_STATUS,
					status: 'foobar',
				}
			)
		).toStrictEqual({
			status: 'foobar',
		});
	});

	/**
	 * ACTION_SCANNABLE_URLS_REQUEST
	 */
	it('returns correct state for ACTION_SCANNABLE_URLS_REQUEST', () => {
		expect(
			siteScanReducer(
				{},
				{
					type: ACTION_SCANNABLE_URLS_REQUEST,
				}
			)
		).toStrictEqual({
			status: STATUS_REQUEST_SCANNABLE_URLS,
			forceStandardMode: false,
			currentlyScannedUrlIndexes: [],
			urlIndexesPendingScan: [],
		});

		expect(
			siteScanReducer(
				{
					forceStandardMode: false,
				},
				{
					type: ACTION_SCANNABLE_URLS_REQUEST,
					forceStandardMode: true,
				}
			)
		).toStrictEqual({
			status: STATUS_REQUEST_SCANNABLE_URLS,
			forceStandardMode: true,
			currentlyScannedUrlIndexes: [],
			urlIndexesPendingScan: [],
		});
	});

	/**
	 * ACTION_SCANNABLE_URLS_RECEIVE
	 */
	it('returns correct state for ACTION_SCANNABLE_URLS_RECEIVE', () => {
		expect(
			siteScanReducer(
				{ scanOnce: false, scansCount: 0 },
				{
					type: ACTION_SCANNABLE_URLS_RECEIVE,
					scannableUrls: [],
				}
			)
		).toStrictEqual({
			status: STATUS_COMPLETED,
			scannableUrls: [],
			scanOnce: false,
			scansCount: 0,
		});

		expect(
			siteScanReducer(
				{ scanOnce: false, scansCount: 2 },
				{
					type: ACTION_SCANNABLE_URLS_RECEIVE,
					scannableUrls: ['foo', 'bar'],
				}
			)
		).toStrictEqual({
			status: STATUS_READY,
			scannableUrls: ['foo', 'bar'],
			scanOnce: false,
			scansCount: 2,
		});

		expect(
			siteScanReducer(
				{ scanOnce: true, scansCount: 1 },
				{
					type: ACTION_SCANNABLE_URLS_RECEIVE,
					scannableUrls: ['foo', 'bar'],
				}
			)
		).toStrictEqual({
			status: STATUS_COMPLETED,
			scannableUrls: ['foo', 'bar'],
			scanOnce: true,
			scansCount: 1,
		});
	});

	/**
	 * ACTION_SCAN_INITIALIZE
	 */
	it.each([
		STATUS_FETCHING_SCANNABLE_URLS,
		STATUS_IDLE,
		STATUS_IN_PROGRESS,
		STATUS_REQUEST_SCANNABLE_URLS,
	])(
		'returns previous state for ACTION_SCAN_INITIALIZE when initial status is %s',
		(status) => {
			expect(
				siteScanReducer(
					{ status },
					{
						type: ACTION_SCAN_INITIALIZE,
					}
				)
			).toStrictEqual({ status });
		}
	);

	it.each([STATUS_CANCELLED, STATUS_COMPLETED, STATUS_FAILED, STATUS_READY])(
		'returns correct state for ACTION_SCAN_INITIALIZE when initial status is %s',
		(status) => {
			expect(
				siteScanReducer(
					{
						status,
						scanOnce: false,
						scansCount: 0,
						scannableUrls: ['foo', 'bar'],
						urlIndexesPendingScan: [],
					},
					{
						type: ACTION_SCAN_INITIALIZE,
					}
				)
			).toStrictEqual({
				status: STATUS_IDLE,
				currentlyScannedUrlIndexes: [],
				scanOnce: false,
				scansCount: 1,
				scannableUrls: ['foo', 'bar'],
				urlIndexesPendingScan: [0, 1],
			});
		}
	);

	it.each([STATUS_CANCELLED, STATUS_COMPLETED, STATUS_FAILED, STATUS_READY])(
		'returns correct state for ACTION_SCAN_INITIALIZE when initial status is %s and scan should be done just once',
		(status) => {
			expect(
				siteScanReducer(
					{
						status,
						scanOnce: true,
						scansCount: 1,
						scannableUrls: ['foo', 'bar'],
						urlIndexesPendingScan: [],
					},
					{
						type: ACTION_SCAN_INITIALIZE,
					}
				)
			).toStrictEqual({
				status: STATUS_COMPLETED,
				scanOnce: true,
				scansCount: 1,
				scannableUrls: ['foo', 'bar'],
				urlIndexesPendingScan: [],
			});
		}
	);

	/**
	 * ACTION_SCAN_URL
	 */
	it.each([
		STATUS_CANCELLED,
		STATUS_COMPLETED,
		STATUS_FAILED,
		STATUS_FETCHING_SCANNABLE_URLS,
		STATUS_READY,
		STATUS_REQUEST_SCANNABLE_URLS,
	])(
		'returns previous state for ACTION_SCAN_URL when initial status is %s',
		(status) => {
			expect(
				siteScanReducer(
					{ status },
					{
						type: ACTION_SCAN_URL,
					}
				)
			).toStrictEqual({ status });
		}
	);

	it.each([STATUS_IDLE, STATUS_IN_PROGRESS])(
		'returns correct state for ACTION_SCAN_URL when initial status is %s',
		(status) => {
			expect(
				siteScanReducer(
					{
						status,
						currentlyScannedUrlIndexes: [],
						scannableUrls: ['foo', 'bar', 'baz'],
						urlIndexesPendingScan: [0, 1, 2],
					},
					{
						type: ACTION_SCAN_URL,
						currentlyScannedUrlIndex: 0,
					}
				)
			).toStrictEqual({
				status: STATUS_IN_PROGRESS,
				currentlyScannedUrlIndexes: [0],
				scannableUrls: ['foo', 'bar', 'baz'],
				urlIndexesPendingScan: [1, 2],
			});

			expect(
				siteScanReducer(
					{
						status,
						currentlyScannedUrlIndexes: [0],
						scannableUrls: ['foo', 'bar', 'baz'],
						urlIndexesPendingScan: [1, 2],
					},
					{
						type: ACTION_SCAN_URL,
						currentlyScannedUrlIndex: 1,
					}
				)
			).toStrictEqual({
				status: STATUS_IN_PROGRESS,
				currentlyScannedUrlIndexes: [0, 1],
				scannableUrls: ['foo', 'bar', 'baz'],
				urlIndexesPendingScan: [2],
			});
		}
	);

	/**
	 * ACTION_SCAN_RECEIVE_RESULTS
	 */
	it.each([
		STATUS_CANCELLED,
		STATUS_COMPLETED,
		STATUS_FAILED,
		STATUS_FETCHING_SCANNABLE_URLS,
		STATUS_READY,
		STATUS_REQUEST_SCANNABLE_URLS,
	])(
		'returns previous state for ACTION_SCAN_RECEIVE_RESULTS when initial status is %s',
		(status) => {
			expect(
				siteScanReducer(
					{ status },
					{
						type: ACTION_SCAN_RECEIVE_RESULTS,
					}
				)
			).toStrictEqual({ status });
		}
	);

	it.each([STATUS_IDLE, STATUS_IN_PROGRESS])(
		'returns correct state for ACTION_SCAN_RECEIVE_RESULTS when initial status is %s',
		(status) => {
			expect(
				siteScanReducer(
					{
						status,
						currentlyScannedUrlIndexes: [0, 1],
						scannableUrls: [
							{
								stale: true,
							},
							{
								stale: true,
								validated_url_post: {},
								validation_errors: [],
							},
						],
					},
					{
						type: ACTION_SCAN_RECEIVE_RESULTS,
						currentlyScannedUrlIndex: 1,
						validatedUrlPost: {
							url: 'http://example.com/',
						},
						validationErrors: [
							'validation-issue-1',
							'validation-issue-2',
						],
					}
				)
			).toStrictEqual({
				status: STATUS_IDLE,
				currentlyScannedUrlIndexes: [0],
				scannableUrls: [
					{
						stale: true,
					},
					{
						stale: false,
						error: false,
						validated_url_post: {
							url: 'http://example.com/',
						},
						validation_errors: [
							'validation-issue-1',
							'validation-issue-2',
						],
					},
				],
			});

			expect(
				siteScanReducer(
					{
						status,
						currentlyScannedUrlIndexes: [0],
						scannableUrls: [
							{
								stale: true,
							},
							{
								stale: false,
							},
						],
					},
					{
						type: ACTION_SCAN_RECEIVE_RESULTS,
						currentlyScannedUrlIndex: 0,
						error: 'scanner-error',
					}
				)
			).toStrictEqual({
				status: STATUS_IDLE,
				currentlyScannedUrlIndexes: [],
				scannableUrls: [
					{
						stale: false,
						error: 'scanner-error',
						validated_url_post: {},
						validation_errors: [],
					},
					{
						stale: false,
					},
				],
			});
		}
	);

	/**
	 * ACTION_SCAN_COMPLETE
	 */
	it('returns correct state for ACTION_SCAN_COMPLETE', () => {
		expect(
			siteScanReducer(
				{
					scannableUrls: [{ error: false }, { error: true }],
				},
				{
					type: ACTION_SCAN_COMPLETE,
				}
			)
		).toStrictEqual({
			status: STATUS_REFETCHING_PLUGIN_SUPPRESSION,
			scannableUrls: [{ error: false }, { error: true }],
		});

		expect(
			siteScanReducer(
				{
					scannableUrls: [{ error: true }, { error: true }],
				},
				{
					type: ACTION_SCAN_COMPLETE,
				}
			)
		).toStrictEqual({
			status: STATUS_FAILED,
			scannableUrls: [{ error: true }, { error: true }],
		});
	});

	/**
	 * ACTION_SCAN_CANCEL
	 */
	it.each([
		STATUS_CANCELLED,
		STATUS_COMPLETED,
		STATUS_FAILED,
		STATUS_FETCHING_SCANNABLE_URLS,
		STATUS_READY,
		STATUS_REQUEST_SCANNABLE_URLS,
	])(
		'returns previous state for ACTION_SCAN_CANCEL when initial status is %s',
		(status) => {
			expect(
				siteScanReducer(
					{ status },
					{
						type: ACTION_SCAN_CANCEL,
					}
				)
			).toStrictEqual({ status });
		}
	);

	it.each([STATUS_IDLE, STATUS_IN_PROGRESS])(
		'returns correct state for ACTION_SCAN_CANCEL when initial status is %s',
		(status) => {
			expect(
				siteScanReducer(
					{
						status,
						currentlyScannedUrlIndexes: [0],
						urlIndexesPendingScan: [1, 2],
						scannableUrls: ['foo', 'bar', 'baz'],
					},
					{
						type: ACTION_SCAN_CANCEL,
					}
				)
			).toStrictEqual({
				status: STATUS_CANCELLED,
				currentlyScannedUrlIndexes: [],
				urlIndexesPendingScan: [],
				scannableUrls: ['foo', 'bar', 'baz'],
			});
		}
	);
});
