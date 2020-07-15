<?php
/**
 * Interface with data for mocking plugin environment.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\Helpers;

interface MockPluginEnvironment {

	const AMP_PLUGIN_FILE       = 'amp/amp.php';
	const GUTENBERG_PLUGIN_FILE = 'gutenberg/gutenberg.php';
	const FOO_PLUGIN_FILE       = 'foo/foo.php';
	const BAR_PLUGIN_FILE       = 'bar.php';
	const BAZ_PLUGIN_FILE       = 'baz.php';

	const PLUGINS_DATA = [
		self::AMP_PLUGIN_FILE       => [
			'Name'    => 'AMP',
			'Version' => AMP__VERSION,
		],
		self::GUTENBERG_PLUGIN_FILE => [
			'Name'    => 'Gutenberg',
			'Version' => '8.2',
		],
		self::FOO_PLUGIN_FILE       => [
			'Name'    => 'Foo',
			'Version' => '0.1',
		],
		self::BAR_PLUGIN_FILE       => [
			'Name'    => 'Bar',
			'Version' => '0.2',
		],
		self::BAZ_PLUGIN_FILE       => [
			'Name'    => 'Baz',
			'Version' => '0.3',
		],
	];

	const BAD_WIDGET_PLUGIN_FILE    = 'bad-widget/bad-widget.php';
	const BAD_BLOCK_PLUGIN_FILE     = 'bad-block.php';
	const BAD_HOOKS_PLUGIN_FILE     = 'bad-hooks.php';
	const BAD_SHORTCODE_PLUGIN_FILE = 'bad-shortcode.php';

	const BAD_PLUGIN_FILES = [
		self::BAD_BLOCK_PLUGIN_FILE,
		self::BAD_HOOKS_PLUGIN_FILE,
		self::BAD_SHORTCODE_PLUGIN_FILE,
		self::BAD_WIDGET_PLUGIN_FILE,
	];

	const BAD_PLUGINS_DIR = 'tests/php/data/plugins';
}
