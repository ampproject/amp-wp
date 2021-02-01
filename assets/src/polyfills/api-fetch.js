/**
 * WordPress dependencies
 */
// eslint-disable-next-line import/no-unresolved
import apiFetch from '@wordpress/api-fetch__non-shim';

global.wp = global.wp || {};
global.wp.apiFetch = apiFetch;

export default apiFetch;
