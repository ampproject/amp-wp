/**
 * WordPress dependencies
 */
import { useContext, useEffect, useLayoutEffect, useState } from '@wordpress/element';

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
		cancelSiteScan,
		canScanSite,
		startSiteScan,
		siteScanComplete,
	} = useContext( SiteScanContext );
	const [ canShowScanSummary, setCanShowScanSummary ] = useState( false );

	/**
	 * Cancel scan on component unmount.
	 */
	useEffect( () => () => cancelSiteScan(), [ cancelSiteScan ] );

	/**
	 * Start site scan.
	 */
	useLayoutEffect( () => {
		if ( canScanSite ) {
			startSiteScan();
		} else if ( siteScanComplete ) {
			setCanShowScanSummary( true );
		}
	}, [ canScanSite, siteScanComplete, startSiteScan ] );

	/**
	 * Show scan summary with a delay so that the progress bar has a chance to
	 * complete.
	 */
	useEffect( () => {
		let delay;

		if ( siteScanComplete && ! canShowScanSummary ) {
			delay = setTimeout( () => setCanShowScanSummary( true ), 500 );
		}

		return () => {
			if ( delay ) {
				clearTimeout( delay );
			}
		};
	}, [ canShowScanSummary, siteScanComplete ] );

	/**
	 * Allow moving forward.
	 */
	useEffect( () => {
		if ( siteScanComplete ) {
			setCanGoForward( true );
		}
	}, [ setCanGoForward, siteScanComplete ] );

	if ( canShowScanSummary ) {
		return <SiteScanComplete />;
	}

	return <SiteScanInProgress />;
}
