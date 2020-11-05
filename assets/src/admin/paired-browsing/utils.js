
/**
 * Return whether the window is for the non-AMP page.
 *
 * @param {Window} win Window.
 * @return {boolean} Whether non-AMP window.
 */
export function isNonAmpWindow( win ) {
	return win.name === 'paired-browsing-non-amp';
}

/**
 * Return whether the window is for the AMP page.
 *
 * @param {Window} win Window.
 * @return {boolean} Whether AMP window.
 */
export function isAmpWindow( win ) {
	return win.name === 'paired-browsing-amp';
}
