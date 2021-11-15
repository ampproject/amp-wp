/**
 * Check if the provided URL is external.
 *
 * @param {string} url URL to be checked.
 * @return {boolean} True if the URL is external, false otherwise.
 */
export const isExternalUrl = ( url ) => global?.location?.host !== new URL( url ).host;
