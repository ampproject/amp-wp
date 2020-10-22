<?php
/**
 * Tests for the SavePostValidationEvent class.
 */

namespace AmpProject\AmpWP\Tests\Validation;

use AmpProject\AmpWP\BackgroundTask\BackgroundTaskDeactivator;
use AmpProject\AmpWP\BackgroundTask\SingleScheduledBackgroundTask;
use AmpProject\AmpWP\DevTools\UserAccess;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use AmpProject\AmpWP\Tests\Helpers\AssertContainsCompatibility;
use AmpProject\AmpWP\Validation\SavePostValidationEvent;
use ReflectionClass;
use WP_UnitTestCase;

/**
 * @coversDefaultClass \AmpProject\AmpWP\Validation\SavePotValidationEvent
 */
final class SavePostValidationEventTest extends WP_UnitTestCase {
	use AssertContainsCompatibility;

	/**
	 * SavePostValidationEvent instance.
	 *
	 * @var SavePostValidationEvent.
	 */
	private $test_instance;

	public function setUp() {
		$this->test_instance = new SavePostValidationEvent( new BackgroundTaskDeactivator(), new UserAccess() );
	}

	/**
	 * @covers ::register()
	 */
	public function test_register() {
		$this->assertInstanceof( SingleScheduledBackgroundTask::class, $this->test_instance );
		$this->assertInstanceof( SavePostValidationEvent::class, $this->test_instance );
		$this->assertInstanceof( Service::class, $this->test_instance );
		$this->assertInstanceof( Registerable::class, $this->test_instance );

		$this->test_instance->register();

		$this->assertEquals( 10, has_action( 'save_post', [ $this->test_instance, 'schedule_event' ] ) );
		$this->assertEquals( 10, has_action( 'amp_single_post_validate', [ $this->test_instance, 'process' ] ) );
	}

	/**
	 * @covers ::process()
	 */
	public function test_process() {
		$post = self::factory()->post->create_and_get(
			[
				'post_content' => '<div invalid-attr="1"></div>',
			]
		);

		$filter = static function() {
			return [
				'body'    => '{"results": []}',
				'headers' => [
					'content-type' => 'application/json',
				],
			];
		};
		add_filter( 'pre_http_request', $filter );

		$this->test_instance->process( $post->ID );

		remove_filter( 'pre_http_request', $filter );

		$plugin_property = ( new ReflectionClass( $this->test_instance ) )->getProperty( 'url_validation_provider' );
		$plugin_property->setAccessible( true );
		$url_validation_provider = $plugin_property->getValue( $this->test_instance );

		$this->assertEquals( 1, $url_validation_provider->get_number_validated() );
	}
}
