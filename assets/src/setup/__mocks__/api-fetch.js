/**
 * Mock for wp.apiFetch.
 */

global.mockApiFetchReturn = [];
global.mockApiFetchError = null;

export default function apiFetch() {
	return new Promise( ( resolve, reject ) => {
		if ( global.mockApiFetchError ) {
			reject( global.mockApiFetchError );
			return;
		}

		resolve( global.mockApiFetchReturn );
	} );
}
