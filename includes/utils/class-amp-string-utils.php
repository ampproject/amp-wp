<?php
/**
 * Class AMP_String_Utils
 *
 * @package AMP
 */

/**
 * Class with static string utility methods.
 */
class AMP_String_Utils {

	/**
	 * Checks whether a given string ends in the given substring.
	 *
	 * @param string $haystack Input string.
	 * @param string $needle   Substring to look for at the end of $haystack.
	 * @return bool True if $haystack ends in $needle, false otherwise.
	 */
	public static function endswith( $haystack, $needle ) {
		return '' !== $haystack
			&& '' !== $needle
			&& substr( $haystack, -strlen( $needle ) ) === $needle;
	}
}
