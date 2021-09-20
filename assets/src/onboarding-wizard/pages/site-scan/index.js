/**
 * WordPress dependencies
 */
import { useContext, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './style.scss';
import { Navigation } from '../../components/navigation-context-provider';
import { SiteScan as SiteScanContext } from '../../../components/site-scan-context-provider';
import { SiteScanComplete } from './complete';
import { SiteScanInProgress } from './in-progress';

/**
 * Screen for visualizing a site scan.
 */
export function SiteScan() {
	const { setCanGoForward } = useContext( Navigation );
	const {
		canScanSite,
		startSiteScan,
		siteScanComplete,
	} = useContext( SiteScanContext );

	/**
	 * Start site scan.
	 */
	useEffect( () => {
		if ( canScanSite ) {
			startSiteScan();
		}
	}, [ canScanSite, startSiteScan ] );

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( siteScanComplete ) {
			setCanGoForward( true );
		}
	}, [ setCanGoForward, siteScanComplete ] );

	if ( siteScanComplete ) {
		return <SiteScanComplete />;
	}

	return <SiteScanInProgress />;
}
