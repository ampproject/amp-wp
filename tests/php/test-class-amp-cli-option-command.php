<?php
/**
 * Test cases for OptionCommand class.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Cli\OptionCommand;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Infrastructure\CliCommand;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\Helpers\MockAdminUser;
use AmpProject\AmpWP\Tests\DependencyInjectedTestCase;

/**
 * Test_AMP_CLI_Option_Command class.
 *
 * @since 2.4.0
 *
 * @coversDefaultClass \AmpProject\AmpWP\Cli\OptionCommand
 */
class Test_AMP_CLI_Option_Command extends DependencyInjectedTestCase {

	use PrivateAccess, MockAdminUser;

	/**
	 * OptionCommand instance.
	 *
	 * @var OptionCommand
	 */
	private $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function set_up() {
		parent::set_up();

		$this->instance = $this->injector->make( OptionCommand::class );
	}

	/** @covers ::__construct() */
	public function test_constructor() {
		$this->assertInstanceOf( Service::class, $this->instance );
		$this->assertInstanceOf( CliCommand::class, $this->instance );
		$this->assertInstanceOf( OptionCommand::class, $this->instance );
	}

	/**  @covers ::get_command_name() */
	public function test_get_command_name() {
		$this->assertEquals( 'amp option', OptionCommand::get_command_name() );
	}

	/** @covers ::check_user_cap() */
	public function test_check_user_cap() {
		$this->mock_admin_user();
		$this->assertTrue( $this->call_private_method( $this->instance, 'check_user_cap' ) );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );

		$output = $this->call_private_method( $this->instance, 'check_user_cap' );

		$this->assertTrue( $output instanceof WP_Error );
		$this->assertStringContainsString( 'Sorry, you are not allowed to manage options', $output->get_error_message( 'amp_rest_cannot_manage_options' ) );
		$this->assertStringContainsString( 'Try using --user=<id|login|email> to set the user context', $output->get_error_message( 'amp_rest_cannot_manage_options_help' ) );
	}

	/**
	 * @covers ::do_request()
	 * @covers ::get_options()
	 */
	public function test_get_options() {
		$this->mock_admin_user();

		$output = $this->call_private_method( $this->instance, 'get_options' );

		$this->assertTrue( is_array( $output ) );
		$this->assertArrayHasKey( 'theme_support', $output );
		$this->assertArrayHasKey( 'supported_templates', $output );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );

		$output = $this->call_private_method( $this->instance, 'get_options' );

		$this->assertTrue( $output instanceof WP_Error );
		$this->assertSame( 'amp_rest_cannot_manage_options', $output->get_error_code() );
		$this->assertStringContainsString( 'Sorry, you are not allowed to manage options', $output->get_error_message() );
	}

	/**
	 * @covers ::do_request()
	 * @covers ::update_option()
	 */
	public function test_update_option() {
		$this->mock_admin_user();

		$output = $this->call_private_method( $this->instance, 'update_option', [ 'theme_support', 'standard' ] );

		$this->assertTrue( is_array( $output ) );
		$this->assertTrue( 'standard' === $output['theme_support'] );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'author' ] ) );

		$output = $this->call_private_method( $this->instance, 'update_option', [ 'theme_support', 'standard' ] );

		$this->assertTrue( $output instanceof WP_Error );
		$this->assertSame( 'amp_rest_cannot_manage_options', $output->get_error_code() );
		$this->assertStringContainsString( 'Sorry, you are not allowed to manage options', $output->get_error_message() );
	}

	/** @covers ::do_request() */
	public function test_do_request() {
		$output = $this->call_private_method( $this->instance, 'do_request', [ 'GET', '/wp/v2/posts', [ 'per_page' => 1 ] ] );

		$this->assertTrue( is_array( $output->get_data() ) );
		$this->assertTrue( $output instanceof WP_REST_Response );
	}
}
