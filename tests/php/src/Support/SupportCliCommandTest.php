<?php
/**
 * Tests for SupportServiceTest.
 *
 * @package AmpProject\AmpWP\Support\Tests
 */

namespace AmpProject\AmpWP\Tests\Support;

use AmpProject\AmpWP\Support\SupportData;
use AmpProject\AmpWP\Support\SupportCliCommand;
use AmpProject\AmpWP\Tests\TestCase;

/**
 * Tests for SupportCliCommandTest.
 *
 * @group support-admin
 * @coversDefaultClass \AmpProject\AmpWP\Support\SupportCliCommand
 */
class SupportCliCommandTest extends TestCase {

	/**
	 * Instance of OptionsMenu
	 *
	 * @var SupportCliCommand
	 */
	public $instance;

	/**
	 * Setup.
	 *
	 * @inheritdoc
	 */
	public function set_up() {

		parent::set_up();

		$this->instance = new SupportCliCommand( new SupportData() );
	}

	/**
	 * Create validated URL.
	 *
	 * @return \WP_Post Validated URL post.
	 */
	public function create_validated_url() {

		$plugin_info = SupportData::normalize_plugin_info( 'amp/amp.php' );

		$post = $this->factory()->post->create_and_get(
			[
				'post_content' => 'Some post content',
			]
		);

		return $this->factory()->post->create_and_get(
			[
				'post_title'   => get_permalink( $post ),
				'post_type'    => \AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
				'post_content' => wp_json_encode(
					[
						[
							'term_slug' => '1',
							'data'      => [
								'node_name'       => 'script',
								'parent_name'     => 'head',
								'code'            => 'DISALLOWED_TAG',
								'type'            => 'js_error',
								'node_attributes' => [
									'src' => home_url( '/wp-includes/js/jquery/jquery.js?ver=__normalized__' ),
									'id'  => 'jquery-core-js',
								],
								'node_type'       => 1,
								'sources'         => [
									[
										'type'            => 'plugin',
										'name'            => $plugin_info['slug'],
										'file'            => $plugin_info['slug'],
										'line'            => 350,
										'function'        => 'dummy_function',
										'hook'            => 'wp_enqueue_scripts',
										'priority'        => 10,
										'dependency_type' => 'script',
										'handle'          => 'hello-script',
										'dependency_handle' => 'jquery-core',
										'text'            => 'Start of the content. ' . home_url( '/adiitional.css' ) . ' End of the content',
									],
								],
							],
						],
					]
				),
				'meta_input'   => [
					'_amp_queried_object' => [
						'id'   => $post->ID,
						'type' => 'post',
					],
				],
			]
		);
	}

	/**
	 * @covers ::get_command_name
	 */
	public function test_get_command_name() {

		$this->assertEquals( 'amp support', SupportCliCommand::get_command_name() );
	}

	/**
	 * @covers ::send_diagnostic
	 */
	public function test_send_diagnostic() {

		$amp_validated_url = $this->create_validated_url();

		ob_start();
		$this->instance->send_diagnostic(
			[],
			[
				'print' => 'json-pretty',
			]
		);
		$output = ob_get_clean();

		$this->assertContains( wp_json_encode( $amp_validated_url->post_title ), $output );
		$this->assertContains( wp_json_encode( '/wp-includes/js/jquery/jquery.js?ver=__normalized__' ), $output );
		$this->assertContains( wp_json_encode( 'DISALLOWED_TAG' ), $output );
	}
}
