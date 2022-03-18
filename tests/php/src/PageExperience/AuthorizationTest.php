<?php

namespace AmpProject\AmpWP\Tests\PageExperience;

use AmpProject\AmpWP\PageExperience\Authorization;
use AmpProject\AmpWP\Tests\TestCase;

/** @coversDefaultClass \AmpProject\AmpWP\PageExperience\Authorization */
class AuthorizationTest extends TestCase {

	/** @covers ::__construct() */
	public function test_it_can_be_instantiated()
	{
		$authorization = new Authorization();
		self::assertInstanceOf( Authorization::class, $authorization );
	}

	/** @covers ::can_user_run_analysis() */
	public function test_it_can_retrieve_the_authorization_for_the_default_user()
	{
		$authorization = new Authorization();

		self::assertFalse( $authorization->can_user_run_analysis() );

		wp_set_current_user( self::factory()->user->create( [ 'role' => 'administrator' ] ) );
		self::assertTrue( $authorization->can_user_run_analysis() );
	}

	/** @covers ::can_user_run_analysis() */
	public function test_it_can_retrieve_the_authorization_for_specific_user_ids()
	{
		$subscriber    = self::factory()->user->create( [ 'role' => 'subscriber' ] );
		$administrator = self::factory()->user->create( [ 'role' => 'administrator' ] );
		$authorization = new Authorization();

		self::assertFalse( $authorization->can_user_run_analysis( $subscriber ) );

		self::assertTrue( $authorization->can_user_run_analysis( $administrator ) );
	}

	/** @covers ::can_user_run_analysis() */
	public function test_it_can_retrieve_the_authorization_for_specific_user_objects()
	{
		$subscriber    = self::factory()->user->create_and_get( [ 'role' => 'subscriber' ] );
		$administrator = self::factory()->user->create_and_get( [ 'role' => 'administrator' ] );
		$authorization = new Authorization();

		self::assertFalse( $authorization->can_user_run_analysis( $subscriber ) );

		self::assertTrue( $authorization->can_user_run_analysis( $administrator ) );
	}
}
