<?php
/**
 * Tests for CallbackReflection class.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\DevTools;

use AmpProject\AmpWP\DevTools\CallbackReflection;
use AmpProject\AmpWP\DevTools\FileReflection;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use ReflectionFunction;
use ReflectionMethod;
use AmpProject\AmpWP\Tests\Helpers\LoadsCoreThemes;

/**
 * Tests for CallbackReflection class.
 *
 * @since 2.0.2
 *
 * @coversDefaultClass \AmpProject\AmpWP\DevTools\CallbackReflection
 */
class CallbackReflectionTest extends DependencyInjectedTestCase {

	use LoadsCoreThemes;

	/**
	 * Test instance.
	 *
	 * @var CallbackReflection
	 */
	private $callback_reflection;

	public function set_up() {
		parent::set_up();

		$this->register_core_themes();

		$this->callback_reflection = $this->injector->make( CallbackReflection::class );

		$theme_root = dirname( dirname( __DIR__ ) ) . '/data/themes';
		add_filter(
			'theme_root',
			static function () use ( $theme_root ) {
				return $theme_root;
			}
		);
		register_theme_directory( $theme_root );
	}

	public function tear_down() {
		parent::tear_down();
		$this->restore_theme_directories();
	}

	/** @return array */
	public function data_get_source() {
		require_once ABSPATH . '/wp-admin/includes/widgets.php';
		require_once dirname( dirname( __DIR__ ) ) . '/data/themes/custom/functions.php';
		require_once dirname( dirname( __DIR__ ) ) . '/data/themes/child-of-core/functions.php';
		return [
			'parent_theme_function'               => [
				'my_custom_after_setup_theme',
				'my_custom_after_setup_theme',
				'custom',
				'theme',
				'functions.php',
				ReflectionFunction::class,
			],
			'child_theme_function'                => [
				'child_of_core_after_setup_theme',
				'child_of_core_after_setup_theme',
				'child-of-core',
				'theme',
				'actions.php',
				ReflectionFunction::class,
			],
			'plugin_function'                     => [
				'amp_after_setup_theme',
				'amp_after_setup_theme',
				'amp',
				'plugin',
				'includes/amp-helper-functions.php',
				ReflectionFunction::class,
			],
			'core_includes_wp_user_static_method' => [
				'WP_User::get_data_by',
				'WP_User::get_data_by',
				'wp-includes',
				'core',
				'class-wp-user.php',
				ReflectionMethod::class,
			],
			'core_widget_display_callback_array'  => [
				[ new \WP_Widget_Text(), 'display_callback' ],
				'WP_Widget_Text::widget',
				'wp-includes',
				'core',
				'widgets/class-wp-widget-text.php',
				ReflectionMethod::class,
			],
			'core_admin'                          => [
				'wp_list_widgets',
				'wp_list_widgets',
				'wp-admin',
				'core',
				'includes/widgets.php',
				ReflectionFunction::class,
			],
			'plugin_closure'                      => [
				function () {},
				__NAMESPACE__ . '\{closure}',
				'amp',
				'plugin',
				'tests/php/src/DevTools/CallbackReflectionTest.php',
				ReflectionFunction::class,
			],
		];
	}

	/**
	 * Test get_source().
	 *
	 * @dataProvider data_get_source
	 * @covers ::get_source()
	 * @covers ::get_reflection()
	 * @covers \AmpProject\AmpWP\DevTools\FileReflection::get_file_source()
	 *
	 * @param string $function         Function.
	 * @param string $source_function  Source function identified.
	 * @param string $name             Name.
	 * @param string $type             Type.
	 * @param string $file             File.
	 * @param string $reflection_class Reflection class.
	 */
	public function test_get_source( $function, $source_function, $name, $type, $file, $reflection_class ) {
		if ( 'theme' === $type ) {
			switch_theme( $name );
		}

		$source = $this->callback_reflection->get_source( $function );
		$this->assertEquals( $type, $source['type'] );
		$this->assertEquals( $name, $source['name'] );
		$this->assertEquals( $file, $source['file'] );
		$this->assertTrue( is_int( $source['line'] ) );
		$this->assertEquals( $source_function, $source['function'] );
		/** @var ReflectionFunction $reflection */
		$reflection = $source['reflection'];
		$this->assertInstanceOf( $reflection_class, $reflection );
		$this->assertEquals( preg_replace( '/.*::/', '', $source['function'] ), $reflection->getName() );
	}

	/**
	 * Test get_source() when function doesn't exist.
	 *
	 * @covers ::get_source()
	 * @covers ::get_reflection()
	 */
	public function test_get_source_non_existent_callback() {
		$function = 'this_function_does_not_exist';
		$this->assertFalse( function_exists( $function ) );
		$source = $this->callback_reflection->get_source( $function );
		$this->assertNull( $source );
	}
}
