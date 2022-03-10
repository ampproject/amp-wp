<?php
/**
 * Tests for CallbackReflection class.
 *
 * @package AMP
 */

namespace AmpProject\AmpWP\Tests\DevTools;

use AMP_Validation_Callback_Wrapper;
use AmpProject\AmpWP\DevTools\CallbackReflection;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;
use AmpProject\AmpWP\Tests\Helpers\LoadsCoreThemes;
use ReflectionFunction;
use ReflectionMethod;

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
		$this->restore_theme_directories();
		parent::tear_down();
	}

	/**
	 * Test get_unwrapped_callback().
	 *
	 * @covers ::get_unwrapped_callback()
	 * @covers AMP_Validation_Callback_Wrapper::__invoke()
	 * @covers AMP_Validation_Callback_Wrapper::invoke_with_first_ref_arg()
	 */
	public function test_get_unwrapped_callback() {
		$original_by_value_callback = function ( $number ) {
			return $number + 1;
		};
		$original_by_ref_callback   = function ( &$number ) {
			$number++;
		};

		$this->assertSame(
			$original_by_value_callback,
			$this->callback_reflection->get_unwrapped_callback( $original_by_value_callback )
		);
		$this->assertSame(
			$original_by_ref_callback,
			$this->callback_reflection->get_unwrapped_callback( $original_by_ref_callback )
		);

		$wrapped_by_value_callback = new AMP_Validation_Callback_Wrapper(
			[
				'function'         => $original_by_value_callback,
				'accepted_args'    => 1,
				'source'           => [],
				'indirect_sources' => [],
			]
		);
		$wrapped_by_ref_callback   = new AMP_Validation_Callback_Wrapper(
			[
				'function'         => $original_by_ref_callback,
				'accepted_args'    => 1,
				'source'           => [],
				'indirect_sources' => [],
			]
		);

		$this->assertSame(
			$original_by_value_callback,
			$this->callback_reflection->get_unwrapped_callback( $wrapped_by_value_callback )
		);
		$this->assertSame(
			$original_by_ref_callback,
			$this->callback_reflection->get_unwrapped_callback( $wrapped_by_ref_callback )
		);
		$this->assertSame(
			$original_by_ref_callback,
			$this->callback_reflection->get_unwrapped_callback( [ $wrapped_by_ref_callback, 'invoke_with_first_ref_arg' ] )
		);

		$rewrapped_by_value_callback = new AMP_Validation_Callback_Wrapper(
			[
				'function'         => $wrapped_by_value_callback,
				'accepted_args'    => 1,
				'source'           => [],
				'indirect_sources' => [],
			]
		);
		$rewrapped_by_ref_callback   = new AMP_Validation_Callback_Wrapper(
			[
				'function'         => $wrapped_by_ref_callback,
				'accepted_args'    => 1,
				'source'           => [],
				'indirect_sources' => [],
			]
		);

		$this->assertSame(
			$original_by_value_callback,
			$this->callback_reflection->get_unwrapped_callback( $rewrapped_by_value_callback )
		);
		$this->assertSame(
			$original_by_ref_callback,
			$this->callback_reflection->get_unwrapped_callback( $rewrapped_by_ref_callback )
		);
		$this->assertSame(
			$original_by_ref_callback,
			$this->callback_reflection->get_unwrapped_callback( [ $rewrapped_by_ref_callback, 'invoke_with_first_ref_arg' ] )
		);

		$this->assertSame( 2, $original_by_value_callback( 1 ) );
		$this->assertSame( 2, $wrapped_by_value_callback( 1 ) );
		$this->assertSame( 2, $rewrapped_by_value_callback( 1 ) );

		$number     = 1;
		$number_ref = &$number;
		$original_by_ref_callback( $number_ref );
		$this->assertSame( 2, $number );
		$wrapped_by_ref_callback->invoke_with_first_ref_arg( $number_ref );
		$this->assertSame( 3, $number );
		$rewrapped_by_ref_callback->invoke_with_first_ref_arg( $number_ref );
		$this->assertSame( 4, $number );
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
