export async function testSiteScanning( { statusElementClassName, isAmpFirst } ) {
	const statusTextRegex = /^Scanning ([\d])+\/([\d]+) URLs/;
	const statusText = await page.$eval( `.${ statusElementClassName }`, ( el ) => el.innerText );

	expect( statusText ).toMatch( statusTextRegex );

	const currentlyScannedIndex = Number( statusText.match( statusTextRegex )[ 1 ] ) - 1;
	const scannableUrlsCount = Number( statusText.match( statusTextRegex )[ 2 ] );
	const urls = [ ...Array( scannableUrlsCount - currentlyScannedIndex ) ];

	const expectedParams = [
		'amp_validate[nonce]',
		'amp_validate[omit_stylesheets]',
		'amp_validate[cache_bust]',
	].map( encodeURI );

	// Use generous timeout since site scan may take a while.
	const timeout = 20000;

	await Promise.all( [
		...urls.map( ( url, index ) => page.waitForXPath( `//p[@class='${ statusElementClassName }'][contains(text(), 'Scanning ${ index + 1 }/${ scannableUrlsCount } URLs')]`, { timeout } ) ),
		page.waitForResponse( ( response ) => isAmpFirst === response.url().includes( 'amp-first' ) && expectedParams.every( ( param ) => response.url().includes( param ) ), { timeout } ),
		page.waitForXPath( `//p[@class='${ statusElementClassName }'][contains(text(), 'Scan complete')]`, { timeout } ),
	] );
}
