export default () => {
	// @todo: Parse the `link` header (if set) to determine number of pages to iterate over to get all PRs.
	return fetch(
		'https://api.github.com/repos/ampproject/amp-wp/pulls?per_page=100'
	).then( ( response ) => response.json() );
};
