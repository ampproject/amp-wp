<?php

require __DIR__ . '/../../amp-beta-tester.php';

/**
 * Class AMP_Beta_Tester_Test
 *
 * @covers \AMP_Beta_Tester
 */
class AMP_Beta_Tester_Test extends WP_UnitTestCase {

	/**
	 * Test init().
	 *
	 * @covers \AMP_Beta_Tester\init()
	 */
	public function test_init() {
		AMP_Beta_Tester\init();

		$this->assertEquals( 10, has_filter( 'pre_set_site_transient_update_plugins', 'AMP_Beta_Tester\update_amp_manifest' ) );
		$this->assertEquals( 10, has_action( 'after_plugin_row_amp/amp.php', 'AMP_Beta_Tester\replace_view_version_details_link' ) );
	}

	/**
	 * Test show_amp_not_active_notice().
	 *
	 * @covers \AMP_Beta_Tester\show_amp_not_active_notice()
	 */
	public function test_show_amp_not_active_notice() {
		$expected = "<div class='notice notice-error'><p><strong>AMP Beta Tester requires AMP to be active.</strong></p></div>";

		ob_start();
		AMP_Beta_Tester\show_amp_not_active_notice();
		$actual = ob_get_clean();

		$this->assertEquals( $actual, $expected );
	}

	/**
	 * Test replace_view_version_details_link().
	 *
	 * @covers \AMP_Beta_Tester\replace_view_version_details_link()
	 */
	public function test_replace_view_version_details_link() {
		// It should not output anything if its not a pre-release.
		ob_start();
		AMP_Beta_Tester\replace_view_version_details_link( null, [ 'Version' => '' ] );
		$actual = ob_get_clean();

		$this->assertEquals( '', $actual );

		// It should output a script if its a pre-release.
		ob_start();
		?>
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				const link = document.querySelectorAll("[data-slug='amp'] a.thickbox.open-plugin-details-modal");

				link.forEach( (link) => {
					link.className = 'overridden'; // Override class so that onclick listeners are disabled.
					link.target = '_blank';
					link.href = 'example.com';
				} );
			}, false);
		</script>
		<?php
		$expected = ob_get_clean();

		ob_start();
		AMP_Beta_Tester\replace_view_version_details_link(
			null,
			[
				'Version' => '1.0.0-beta',
				'url'     => 'example.com',
			]
		);
		$actual = ob_get_clean();

		$this->assertEquals( $expected, $actual );
	}

	/**
	 * Test is_pre_release().
	 *
	 * @covers \AMP_Beta_Tester\is_pre_release()
	 */
	public function test_is_pre_release() {
		$actual = AMP_Beta_Tester\is_pre_release( '' );
		$this->assertFalse( $actual );

		$actual = AMP_Beta_Tester\is_pre_release( '1.0.0' );
		$this->assertFalse( $actual );

		$actual = AMP_Beta_Tester\is_pre_release( '1.0.0-beta' );
		$this->assertTrue( $actual );

		$actual = AMP_Beta_Tester\is_pre_release( '1.0.0-beta1' );
		$this->assertTrue( $actual );

		$actual = AMP_Beta_Tester\is_pre_release( '1.0.0-alpha' );
		$this->assertTrue( $actual );
	}
}
