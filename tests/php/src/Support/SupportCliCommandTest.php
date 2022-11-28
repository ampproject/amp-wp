<?php
/**
 * Tests for SupportServiceTest.
 *
 * @package AmpProject\AmpWP\Support\Tests
 */

namespace AmpProject\AmpWP\Tests\Support;

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
	 * @covers ::get_command_name()
	 */
	public function test_get_command_name() {

		$this->assertEquals( 'amp support', SupportCliCommand::get_command_name() );
	}
}
