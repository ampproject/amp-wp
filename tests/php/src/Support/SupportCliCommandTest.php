<?php
/**
 * Tests for SupportServiceTest.
 *
 * @package AmpProject\AmpWP\Support\Tests
 */

namespace AmpProject\AmpWP\Support\Tests;

use AmpProject\AmpWP\Support\SupportData;
use AmpProject\AmpWP\Support\SupportCliCommand;
use WP_UnitTestCase;

/**
 * Tests for SupportCliCommandTest.
 *
 * @group support-admin
 * @coversDefaultClass \AmpProject\AmpWP\Support\SupportCliCommand
 */
class SupportCliCommandTest extends WP_UnitTestCase {

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
	public function setUp() {

		parent::setUp();

		$this->instance = new SupportCliCommand( new SupportData() );
	}

	/**
	 * @covers ::get_command_name
	 */
	public function test_get_command_name() {

		$this->assertEquals( 'amp support', SupportCliCommand::get_command_name() );
	}
}
