/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { createContext } from '@wordpress/element';

export const SiteScan = createContext();

/**
 * MOCK.
 *
 * @param {Object}  props
 * @param {any}     props.children
 * @param {boolean} props.hasSiteScanResults
 * @param {boolean} props.isBusy
 * @param {boolean} props.isCancelled
 * @param {boolean} props.isCompleted
 * @param {boolean} props.isFailed
 * @param {boolean} props.isFetchingScannableUrls
 * @param {boolean} props.isReady
 * @param {boolean} props.isSiteScannable
 * @param {boolean} props.stale
 */
export function SiteScanContextProvider( {
	children,
	hasSiteScanResults = false,
	isBusy = false,
	isCancelled = false,
	isCompleted = false,
	isFailed = false,
	isFetchingScannableUrls = false,
	isReady = true,
	isSiteScannable = false,
	stale = false,
} ) {
	return (
		<SiteScan.Provider value={
			{
				cancelSiteScan: () => {},
				fetchScannableUrls: () => {},
				hasSiteScanResults,
				isBusy,
				isCancelled,
				isCompleted,
				isFailed,
				isFetchingScannableUrls,
				isReady,
				isSiteScannable,
				pluginsWithAmpIncompatibility: [],
				previewPermalink: '',
				scannableUrls: [],
				scannedUrlsMaxIndex: -1,
				stale,
				startSiteScan: () => {},
				themesWithAmpIncompatibility: [],
			}
		}>
			{ children }
		</SiteScan.Provider>
	);
}
SiteScanContextProvider.propTypes = {
	children: PropTypes.any,
	hasSiteScanResults: PropTypes.bool,
	isBusy: PropTypes.bool,
	isCancelled: PropTypes.bool,
	isCompleted: PropTypes.bool,
	isFailed: PropTypes.bool,
	isFetchingScannableUrls: PropTypes.bool,
	isReady: PropTypes.bool,
	isSiteScannable: PropTypes.bool,
	stale: PropTypes.bool,
};
