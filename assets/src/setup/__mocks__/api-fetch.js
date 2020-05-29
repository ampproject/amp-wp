/**
 * Mock for wp.apiFetch.
 */

global.mockApiFetchReturn = [];

export default function apiFetch() {
	return new Promise( ( resolve ) => {
		resolve( global.mockApiFetchReturn );
	} );
}
