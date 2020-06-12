/**
 * Base URL for the GitHub API.
 *
 * @type {string}
 */
const baseUrl = 'https://api.github.com';

/**
 * Fetches all open PRs that have build zips available for download.
 *
 * @return {Promise<Object[]>} Promise containing a list of PR items.
 */
export const getPullRequestsWithBuilds = () => {
	const url = new URL( `${ baseUrl }/search/issues` );
	const params = {
		/* eslint-disable-next-line prettier/prettier */
		q: 'repo:ampproject/amp-wp is:pr commenter:app/github-actions in:comments "Download development build"',
		sort: 'created',
		order: 'desc',
	};

	url.search = new URLSearchParams( params ).toString();

	return fetch( url )
		.then( ( response ) => response.json() )
		.then( ( json ) => json.items || [] );
};

/**
 * Get the list of protected branches.
 *
 * @return {Promise<Object[]>} Promise containing list of protected branches.
 */
export const getProtectedBranches = () => {
	// Add the `protected` query to filter PR branches.
	/* eslint-disable-next-line prettier/prettier */
	const url = `${ baseUrl }/repos/ampproject/amp-wp/branches?protected=true`;

	return fetch( url ).then( ( response ) => response.json() );
};
