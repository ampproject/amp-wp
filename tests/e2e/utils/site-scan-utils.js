export async function testSiteScanning({ statusElementClassName, isAmpFirst }) {
	await page.waitForSelector(`.${statusElementClassName}`);

	const statusTextRegex = /^Scanning ([\d])+\/([\d]+) URLs/;
	const statusText = await page.$eval(
		`.${statusElementClassName}`,
		(el) => el.innerText
	);

	expect(statusText).toMatch(statusTextRegex);

	const scannableUrlsCount = Number(statusText.match(statusTextRegex)[2]);

	const expectedParams = [
		'amp_validate[cache]',
		'amp_validate[cache_bust]',
		'amp_validate[nonce]',
		'amp_validate[omit_stylesheets]',
	].map(encodeURI);

	// Use generous timeout since site scan may take a while.
	const timeout = 20000;

	await Promise.all([
		page.waitForXPath(
			`//p[@class='${statusElementClassName}'][contains(text(), 'Scanning ${scannableUrlsCount}/${scannableUrlsCount} URLs')]`,
			{ timeout }
		),
		page.waitForResponse(
			(response) =>
				isAmpFirst ===
					response
						.url()
						.includes(
							encodeURI('amp_validate[force_standard_mode]')
						) &&
				expectedParams.every((param) => response.url().includes(param)),
			{ timeout }
		),
		page.waitForXPath(
			`//p[@class='${statusElementClassName}'][contains(text(), 'Scan complete')]`,
			{ timeout }
		),
	]);
}
