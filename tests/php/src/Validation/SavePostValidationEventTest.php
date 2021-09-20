<?php
/**
 * Tests for the SavePostValidationEvent class.
 */

namespace AmpProject\AmpWP\Tests\Validation;

use AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator;
use AmpProject\AmpWP\BackgroundTask\SingleScheduledBackgroundTask;
use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\Helpers\PrivateAccess;
use AmpProject\AmpWP\Tests\Helpers\ValidationRequestMocking;
use AmpProject\AmpWP\Tests\TestCase;
use AmpProject\AmpWP\Validation\SavePostValidationEvent;
use AmpProject\AmpWP\Validation\URLValidationProvider;

/**
 * @coversDefaultClass \AmpProject\AmpWP\Validation\SavePostValidationEvent
 */
final class SavePostValidationEventTest extends TestCase {
	use ValidationRequestMocking, PrivateAccess;

	/**
	 * SavePostValidationEvent instance.
	 *
	 * @var SavePostValidationEvent.
	 */
	private $test_instance;

	/**
	 * Test instance.
	 *
	 * @var UserAccess
	 */
	private $dev_tools_user_access;

	public function set_up() {
		$this->test_instance         = new SavePostValidationEvent( new BackgroundTaskDeactivator(), new UserAccess(), new URLValidationProvider() );
		$this->dev_tools_user_access = new UserAccess();
		add_filter( 'pre_http_request', [ $this, 'get_validate_response' ] );
	}

	/** @covers ::__construct() */
	public function test__construct() {
		$this->assertInstanceof( SingleScheduledBackgroundTask::class, $this->test_instance );
		$this->assertInstanceof( SavePostValidationEvent::class, $this->test_instance );
		$this->assertInstanceof( Service::class, $this->test_instance );
		$this->assertInstanceof( Registerable::class, $this->test_instance );
		$this->assertInstanceof( Conditional::class, $this->test_instance );
	}

	/** @covers ::is_needed() */
	public function test_is_needed() {
		$this->assertFalse( SavePostValidationEvent::is_needed() );

		add_filter( 'amp_temp_validation_cron_tasks_enabled', '__return_true' );
		$this->assertTrue( SavePostValidationEvent::is_needed() );

		remove_filter( 'amp_temp_validation_cron_tasks_enabled', '__return_true' );
	}

	/**
	 * @covers ::register()
	 * @covers ::get_event_name()
	 * @covers ::get_action_hook_arg_count()
	 */
	public function test_register() {
		$this->test_instance->register();

		$this->assertEquals( 10, has_action( 'save_post', [ $this->test_instance, 'schedule_event' ] ) );
		$this->assertEquals( 10, has_action( 'amp_single_post_validate', [ $this->test_instance, 'process' ] ) );
	}

	/**
	 * @covers ::process()
	 */
	public function test_process() {
		$this->test_instance->process();
		$this->assertCount( 0, $this->get_validated_urls() );

		$post = $this->factory()->post->create_and_get(
			[
				'post_content' => '<div invalid-attr="1"></div>',
			]
		);

		$this->test_instance->process( $post->ID );

		$this->assertCount( 1, $this->get_validated_urls() );

		$this->assertInstanceof(
			URLValidationProvider::class,
			$this->get_private_property( $this->test_instance, 'url_validation_provider' )
		);
	}

	/** @covers ::schedule_event() */
	public function test_schedule_event_with_no_post() {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );

		$event_was_scheduled = false;
		$filter_cb           = static function ( $event ) use ( &$event_was_scheduled ) {
			$event_was_scheduled = true;
			return $event;
		};
		add_filter( 'schedule_event', $filter_cb );

		$this->test_instance->schedule_event( [] );

		$this->assertFalse( $event_was_scheduled );

		remove_filter( 'schedule_event', $filter_cb );
	}

	/** @covers ::schedule_event() */
	public function test_schedule_event_with_post() {
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->dev_tools_user_access->set_user_enabled( wp_get_current_user(), true );

		$event_was_scheduled = false;
		$filter_cb           = static function ( $event ) use ( &$event_was_scheduled ) {
			$event_was_scheduled = true;
			return $event;
		};
		add_filter( 'schedule_event', $filter_cb );

		$post = $this->factory()->post->create();

		$this->test_instance->schedule_event( $post );

		$this->assertFalse( $event_was_scheduled );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'author' ] ) );

		$this->test_instance->schedule_event( $post );

		$this->assertTrue( $event_was_scheduled );

		remove_filter( 'schedule_event', $filter_cb );
	}

	/** @covers ::should_schedule_event() */
	public function test_should_schedule_event() {
		// No user set.
		$this->assertFalse( $this->call_private_method( $this->test_instance, 'should_schedule_event', [ [] ] ) );

		// Array not passed.
		$this->assertFalse( $this->call_private_method( $this->test_instance, 'should_schedule_event', [ null ] ) );

		// Too many args passed.
		$this->assertFalse( $this->call_private_method( $this->test_instance, 'should_schedule_event', [ [ 'arg1', 'arg2' ] ] ) );

		// User with insufficient permissions.
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'subscriber' ] ) );
		$post = $this->factory()->post->create();
		$this->assertTrue( $this->call_private_method( $this->test_instance, 'should_schedule_event', [ [ $post ] ] ) );

		// User with dev tools off.
		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->dev_tools_user_access->set_user_enabled( wp_get_current_user(), false );
		$post = $this->factory()->post->create();
		$this->assertTrue( $this->call_private_method( $this->test_instance, 'should_schedule_event', [ [ $post ] ] ) );

		wp_set_current_user( $this->factory()->user->create( [ 'role' => 'administrator' ] ) );
		$this->dev_tools_user_access->set_user_enabled( wp_get_current_user(), true );
		$post = $this->factory()->post->create();
		$this->assertFalse( $this->call_private_method( $this->test_instance, 'should_schedule_event', [ [ $post ] ] ) );
	}

	/** @covers ::get_action_hook() */
	public function test_get_action_hook() {
		$this->assertEquals( 'save_post', $this->call_private_method( $this->test_instance, 'get_action_hook' ) );
	}
}
