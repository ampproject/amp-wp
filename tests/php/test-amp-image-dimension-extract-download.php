<?php
/**
 * Tests for AMP_Image_Dimension_Extractor.
 *
 * @package AMP
 */

/**
 * Tests for AMP_Image_Dimension_Extractor.
 *
 * @covers AMP_Image_Dimension_Extractor
 */
class AMP_Image_Dimension_Extract_Download_Test extends WP_UnitTestCase {

	/**
	 * Path to non-existing PNG file.
	 *
	 * @var string
	 */
	const AMP_IMG_DIMENSION_TEST_INVALID_FILE = __DIR__ . '/assets/not-exists.png';

	/**
	 * Process ID for PHP server.
	 *
	 * @var int
	 */
	private static $pid;

	/**
	 * Set up before class, starting PHP server.
	 */
	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		self::start_server();
	}

	/**
	 * Tear down after class, stopping PHP server.
	 */
	public static function tearDownAfterClass() {
		parent::tearDownAfterClass();
		self::stop_server();
	}

	/**
	 * Start PHP built-in server to host images to test.
	 */
	private static function start_server() {
		// Not ideal to use remote URLs; mocking would be better for performance, but FasterImage doesn't provide means to do this.
		$url = wp_parse_url( self::get_server_host_port() );

		if ( ! isset( $url['host'], $url['port'] ) ) {
			throw new Exception( 'A host and port needs to be set to start the PHP server' );
		}

		$host      = $url['host'];
		$port      = $url['port'];
		$host_root = self::get_server_root();

		self::$pid = exec( sprintf( 'php -S %s -t %s >/dev/null 2>&1 & echo $!', escapeshellarg( "$host:$port" ), escapeshellarg( $host_root ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec

		// Wait up to 3 seconds for the server to start.
		$started = false;
		for ( $i = 0; $i < 3; $i++ ) {
			$response = wp_remote_get( "http://$host:$port" );

			if ( ! $response instanceof WP_Error ) {
				$started = true;
				break;
			}

			sleep( 1 );
		}

		if ( ! $started ) {
			throw new Exception( 'Failed to start the PHP server' );
		}
	}

	/**
	 * Stop PHP server.
	 */
	private static function stop_server() {
		if ( ! empty( self::$pid ) ) {
			exec( 'kill ' . (int) self::$pid ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_exec
		}
	}

	/**
	 * Get host with port for PHP server.
	 *
	 * @return string Host and port.
	 */
	private static function get_server_host_port() {
		return getenv( 'AMP_TEST_HOST_PORT' ) ?: '127.0.0.1:8891';
	}

	/**
	 * Get root path for PHP server.
	 *
	 * @return string Public filesystem root for PHP server.
	 */
	private static function get_server_root() {
		return getenv( 'AMP_TEST_HOST_ROOT' ) ?: __DIR__ . '/data/images';
	}

	/**
	 * Get image URL for specified image file.
	 *
	 * @param string $file Image name.
	 * @return string URL.
	 */
	private static function get_image_url( $file ) {
		return self::get_server_host_port() . "/{$file}";
	}

	/**
	 * Test a valid image file.
	 *
	 * @todo: tests for transients, errors, lock
	 * @todo: mocked tests
	 */
	public function test__valid_image_file() {
		$sources  = [
			self::get_image_url( '350x150.png' ) => false,
		];
		$expected = [
			self::get_image_url( '350x150.png' ) => [
				'width'  => 350,
				'height' => 150,
			],
		];

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test multiple valid image files.
	 */
	public function test__multiple_valid_image_files() {
		$sources  = [
			self::get_image_url( '350x150.png' )  => false,
			self::get_image_url( '1024x768.png' ) => false,
			self::get_image_url( 'amp.svg' )      => false,
			self::get_image_url( 'google.svg' )   => false,
		];
		$expected = [
			self::get_image_url( '350x150.png' )  => [
				'width'  => 350,
				'height' => 150,
			],
			self::get_image_url( '1024x768.png' ) => [
				'width'  => 1024,
				'height' => 768,
			],
			self::get_image_url( 'amp.svg' )      => [
				'width'  => 175,
				'height' => 60,
			],
			self::get_image_url( 'google.svg' )   => [
				'width'  => 251,
				'height' => 80,
			],
		];

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test an invalid image file.
	 */
	public function test__invalid_image_file() {
		$sources  = [
			self::AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		];
		$expected = [
			self::AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		];

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}

	/**
	 * Test a mix of valid and invalid image files.
	 */
	public function test__mix_of_valid_and_invalid_image_file() {
		$sources  = [
			self::get_image_url( '350x150.png' )      => false,
			self::get_image_url( '1024x768.png' )     => false,
			self::AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		];
		$expected = [
			self::get_image_url( '350x150.png' )      => [
				'width'  => 350,
				'height' => 150,
			],
			self::get_image_url( '1024x768.png' )     => [
				'width'  => 1024,
				'height' => 768,
			],
			self::AMP_IMG_DIMENSION_TEST_INVALID_FILE => false,
		];

		$dimensions = AMP_Image_Dimension_Extractor::extract_by_downloading_images( $sources );

		$this->assertEquals( $expected, $dimensions );
	}
}
