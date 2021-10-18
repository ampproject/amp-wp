export async function testSiteScanning( { statusElementClassName, isAmpFirst } ) {
	const statusTextRegex = /^Scanning \d+\/([\d]+) URLs/;
	const statusText = await page.$eval( `.${ statusElementClassName }`, ( el ) => el.innerText );

	expect( statusText ).toMatch( statusTextRegex );

	const scannableUrlsCount = Number( statusText.match( statusTextRegex )[ 1 ] );
	const expectedParams = [
		'amp_validate[nonce]',
		'amp_validate[omit_stylesheets]',
		'amp_validate[cache_bust]',
	].map( encodeURI );

	await Promise.all( [ ...Array( scannableUrlsCount ) ].map( ( url, index ) => ( [
		page.waitForResponse( ( response ) => isAmpFirst === response.url().includes( 'amp-first' ) && expectedParams.every( ( param ) => response.url().includes( param ) ) ),
		page.waitForXPath( `//p[@class='${ statusElementClassName }'][contains(text(), 'Scanning ${ index + 1 }/${ scannableUrlsCount } URLs')]` ),
	] ) ).flat() );
}
