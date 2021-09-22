/**
 * WordPress dependencies
 */
import { useContext, useEffect, useState } from '@wordpress/element';

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
	const [ canShowScanSummary, setCanShowScanSummary ] = useState( false );

	/**
	 * Start site scan.
	 */
	useEffect( () => {
		if ( canScanSite ) {
			startSiteScan();
		}
	}, [ canScanSite, startSiteScan ] );

	/**
	 * Show scan summary with a delay so that the progress bar has a chance to
	 * complete.
	 */
	useEffect( () => {
		let delay;

		if ( siteScanComplete ) {
			delay = setTimeout( () => setCanShowScanSummary( true ), 500 );
		}

		return () => {
			if ( delay ) {
				clearTimeout( delay );
			}
		};
	}, [ siteScanComplete ] );

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
