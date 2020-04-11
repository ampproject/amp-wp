<?php
/**
 * Interface Icon.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * Icons used to visually represent validity of validation errors.
 *
 * @package AmpProject\AmpWP
 */
interface Icon {
	/**
	 * Indicate an AMP version of the page is available.
	 */
	const LINK = 'amp-link';

	/**
	 * Indicates the page is valid AMP.
	 */
	const VALID = 'amp-valid';

	/**
	 * Indicates there are validation errors which have not been explicitly accepted.
	 */
	const WARNING = 'amp-warning';

	/**
	 * Indicates there are validation errors for the AMP page.
	 */
	const INVALID = 'amp-invalid';

}
