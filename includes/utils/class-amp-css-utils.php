<?php

class AMP_CSS_Utils
{
	public static function extract_and_minify_css_file($cssFile)
	{
		// Load CSS file contents
		$minified_css = file_get_contents($cssFile);
		// Remove comments
		$minified_css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $minified_css);
		// Remove space after colons
		$minified_css = str_replace(': ', ':', $minified_css);
		// Remove whitespace
		$minified_css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $minified_css);

		return $minified_css;
	}
}