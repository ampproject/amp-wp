<?php

class AMP_Cache_Utilities_Test extends WP_UnitTestCase {

	private $http_response_code = 0;

	public function get_amp_cache_path_for_url_data() {
		return array(
			'http_content_success' => array(
				array(
					'url' => 'http://example.com/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => null,
				),
				'/c/example.com/path/to/resource.ext',
			),
			'http_content_with_port_success' => array(
				array(
					'url' => 'http://example.com:8080/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => null,
				),
				'/c/example.com:8080/path/to/resource.ext',
			),
			'http_content_with_query_success' => array(
				array(
					'url' => 'http://example.com/path/to/resource.ext?query=value&query2=value2',
					'content_type' => 'c',
					'scheme' => null,
				),
				'/c/example.com/path/to/resource.ext?query=value&query2=value2',
			),
			'http_content_with_fragment_success' => array(
				array(
					'url' => 'http://example.com/path/to/resource.ext#frag',
					'content_type' => 'c',
					'scheme' => null,
				),
				'/c/example.com/path/to/resource.ext#frag',
			),
			'http_content_with_everything_success' => array(
				array(
					'url' => 'http://example.com:8888/path/to/resource.ext?query1=val1&query2=val2#frag',
					'content_type' => 'c',
					'scheme' => null,
				),
				'/c/example.com:8888/path/to/resource.ext?query1=val1&query2=val2#frag',
			),
			'https_content_success' => array(
				array(
					'url' => 'https://example.com/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => null,
				),
				'/c/s/example.com/path/to/resource.ext',
			),
			'http_content_specify_scheme_success' => array(
				array(
					'url' => 'http://example.com/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => 'http',
				),
				'/c/example.com/path/to/resource.ext',
			),
			'http_content_specify_different_scheme_https_success' => array(
				array(
					'url' => 'http://example.com/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => 'https',
				),
				'/c/s/example.com/path/to/resource.ext',
			),
			'https_content_specify_scheme_success' => array(
				array(
					'url' => 'https://example.com/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => 'https',
				),
				'/c/s/example.com/path/to/resource.ext',
			),
			'https_content_specify_different_scheme_success' => array(
				array(
					'url' => 'https://example.com/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => 'http',
				),
				'/c/example.com/path/to/resource.ext',
			),
			'no_scheme_content_fail' => array(
				array(
					'url' => '//example.com/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => null,
				),
				false,
			),
			'no_host_scheme_http_fail' => array(
				array(
					'url' => '/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => 'http',
				),
				false,
			),
			'no_host_scheme_https_fail' => array(
				array(
					'url' => '/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => 'https',
				),
				false,
			),
			'content_scheme_http_success' => array(
				array(
					'url' => '//example.com/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => 'http',
				),
				'/c/example.com/path/to/resource.ext',
			),
			'content_scheme_https_success' => array(
				array(
					'url' => '//example.com/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => 'https',
				),
				'/c/s/example.com/path/to/resource.ext',
			),
			'content_scheme_bad_fail' => array(
				array(
					'url' => '//example.com/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => 'bad',
				),
				false,
			),
			'content_bad_fail' => array(
				array(
					'url' => '//example.com/path/to/resource.ext',
					'content_type' => 'zzz',
					'scheme' => 'https',
				),
				false,
			),
			'no_scheme_no_host_fail' => array(
				array(
					'url' => '/path/to/resource.ext',
					'content_type' => 'c',
					'scheme' => null,
				),
				false,
			),
			'bad_url_fail' => array(
				array(
					'url' => '!@#$%^&*()!@#$%^&*()',
					'content_type' => 'c',
					'scheme' => 'http',
				),
				false,
			),
		);
	}

	/**
	 * @dataProvider get_amp_cache_path_for_url_data
	 * @group amp-cache-path-test	
	 */
	public function test_get_amp_cache_path_for_url( $data, $expected_ache_path ) {
		$cache_path = AMP_Cache_Utilities::get_amp_cache_path_for_url( $data['url'], $data['content_type'] , $data['scheme'] );
		$this->assertEquals( $cache_path, $expected_ache_path );
	}


	public function get_do_amp_update_ping_data() {
		return array(
			'post_success' => array(
				array(
					'factory_type' => 'post',
					'http_response_code' => 204,
				),
				true,
			),
			'tag_fail' => array(
				array(
					'factory_type' => 'tag',
					'http_response_code' => 204,
				),
				false,
			),
			'user_fail' => array(
				array(
					'factory_type' => 'user',
					'http_response_code' => 204,
				),
				false,
			),
			'post_fail' => array(
				array(
					'factory_type' => 'post',
					'http_response_code' => 404,
				),
				false,
			),
		);
	}

	/**
	 * @dataProvider get_do_amp_update_ping_data
	 * @group amp-update-ping-test	
	 */
	public function test_do_amp_update_ping( $data, $expected ) {
		add_filter( 'pre_http_request', array( $this, 'mock_update_ping_request' ), 10, 3 );
		$post_id = $this->factory->{$data['factory_type']}->create();
		$this->http_response_code = $data['http_response_code'];
		$result = AMP_Cache_Utilities::do_amp_update_ping( $post_id );
		$this->assertEquals( $expected, $result );
	}

	public function mock_update_ping_request( $preempt, $request_args, $url ) {
		$response = array(
			'response' => array(
				'code' => $this->http_response_code,
			),
		);
		return $response;
	}

	public function get_get_amp_cache_update_url_for_resource_data() {
		return array(
			'post_success' => array(
				array(
					'url' => 'http://example.com/path/to/resource',
					'content_type' => 'c',
					'scheme' => null,
				),
				'https://cdn.ampproject.org/update-ping/c/example.com/path/to/resource',
			),
			'post_https_success' => array(
				array(
					'url' => 'https://example.com/path/to/resource',
					'content_type' => 'c',
					'scheme' => null,
				),
				'https://cdn.ampproject.org/update-ping/c/s/example.com/path/to/resource',
			),
			'image_success' => array(
				array(
					'url' => 'http://example.com/path/to/resource',
					'content_type' => 'i',
					'scheme' => null,
				),
				'https://cdn.ampproject.org/update-ping/i/example.com/path/to/resource',
			),
			'image_https_success' => array(
				array(
					'url' => 'https://example.com/path/to/resource',
					'content_type' => 'i',
					'scheme' => null,
				),
				'https://cdn.ampproject.org/update-ping/i/s/example.com/path/to/resource',
			),
			'bad_content_type_fail' => array(
				array(
					'url' => 'http://example.com/path/to/resource',
					'content_type' => 'x',
					'scheme' => null,
				),
				false,
			),
			'post_bad_scheme_fail' => array(
				array(
					'url' => 'file://example.com/path/to/resource',
					'content_type' => 'c',
					'scheme' => null,
				),
				false,
			),
			'protocol_relative_scheme_fail' => array(
				array(
					'url' => '//example.com/path/to/resource',
					'content_type' => 'c',
					'scheme' => null,
				),
				false,
			),
			'no_protocol_scheme_fail' => array(
				array(
					'url' => 'example.com/path/to/resource',
					'content_type' => 'c',
					'scheme' => null,
				),
				false,
			),
			'host_relative_fail' => array(
				array(
					'url' => '/path/to/resource',
					'content_type' => 'c',
					'scheme' => null,
				),
				false,
			),
		);
	}

	/**
	 * @dataProvider get_get_amp_cache_update_url_for_resource_data
	 * @group amp-cache-url-test	
	 */
	public function test_get_amp_cache_update_url_for_resource( $data, $expected ) {
		add_filter( 'pre_http_request', array( 'AMP_Cache_Utilities_Test', 'handle_update_ping_request' ), 10, 3 );
		$result = AMP_Cache_Utilities::get_amp_cache_update_url_for_resource( $data['url'], $data['content_type'], $data['scheme'] );
		$this->assertEquals( $expected, $result );
	}


	public function get_get_amp_cache_url_for_resource_data() {
		return array(
			'post_success' => array(
				array(
					'url' => 'http://example.com/path/to/resource',
					'content_type' => 'c',
					'scheme' => null,
				),
				'https://cdn.ampproject.org/c/example.com/path/to/resource',
			),
			'post_https_success' => array(
				array(
					'url' => 'https://example.com/path/to/resource',
					'content_type' => 'c',
					'scheme' => null,
				),
				'https://cdn.ampproject.org/c/s/example.com/path/to/resource',
			),
			'image_success' => array(
				array(
					'url' => 'http://example.com/path/to/resource',
					'content_type' => 'i',
					'scheme' => null,
				),
				'https://cdn.ampproject.org/i/example.com/path/to/resource',
			),
			'image_https_success' => array(
				array(
					'url' => 'https://example.com/path/to/resource',
					'content_type' => 'i',
					'scheme' => null,
				),
				'https://cdn.ampproject.org/i/s/example.com/path/to/resource',
			),
			'bad_content_type_fail' => array(
				array(
					'url' => 'http://example.com/path/to/resource',
					'content_type' => 'x',
					'scheme' => null,
				),
				false,
			),
			'post_bad_scheme_fail' => array(
				array(
					'url' => 'file://example.com/path/to/resource',
					'content_type' => 'c',
					'scheme' => null,
				),
				false,
			),
			'protocol_relative_scheme_fail' => array(
				array(
					'url' => '//example.com/path/to/resource',
					'content_type' => 'c',
					'scheme' => null,
				),
				false,
			),
			'no_protocol_scheme_fail' => array(
				array(
					'url' => 'example.com/path/to/resource',
					'content_type' => 'c',
					'scheme' => null,
				),
				false,
			),
			'host_relative_fail' => array(
				array(
					'url' => '/path/to/resource',
					'content_type' => 'c',
					'scheme' => null,
				),
				false,
			),
		);
	}

	/**
	 * @dataProvider get_get_amp_cache_url_for_resource_data
	 * @group amp-cache-url-test	
	 */
	public function test_get_amp_cache_url_for_resource( $data, $expected ) {
		add_filter( 'pre_http_request', array( 'AMP_Cache_Utilities_Test', 'handle_update_ping_request' ), 10, 3 );
		$result = AMP_Cache_Utilities::get_amp_cache_url_for_resource( $data['url'], $data['content_type'], $data['scheme'] );
		$this->assertEquals( $expected, $result );
	}


	public function get_post_updated_data() {
		return array(
			'post_updated' => array(
				array(
					'factory_type' => 'post',
					'post_status_before' => 'publish',
					'post_status_after' => 'publish',
				),
				true,
			),
			'post_unpublished' => array(
				array(
					'factory_type' => 'post',
					'post_status_before' => 'publish',
					'post_status_after' => 'draft',
				),
				true,
			),
			'post_published' => array(
				array(
					'factory_type' => 'post',
					'post_status_before' => 'draft',
					'post_status_after' => 'publish',
				),
				true,
			),
			'post_updated_not_published' => array(
				array(
					'factory_type' => 'post',
					'post_status_before' => 'draft',
					'post_status_after' => 'draft',
				),
				false,
			),
		);
	}

	/**
	 * @dataProvider get_post_updated_data
	 * @group post-updated-test	
	 */
	public function test_post_updated( $data, $expected ) {
		AMP_Cache_Utilities::amp_add_cache_update_actions();
		$post = $this->factory->{$data['factory_type']}->create_and_get();
		$post->post_status = $data['post_status_before'];
		wp_update_post( $post );
		$post_before = clone $post;
		$post->post_status = $data['post_status_after'];
		wp_update_post( $post );
		$post_after = clone $post;
		$got = AMP_Cache_Utilities::post_updated( $post->ID, $post_after, $post_before );

		$this->assertEquals( $expected, $got );
	}


	public function get_get_amp_cache_url_for_post_data() {
		return array(
			'post_success' => array(
				array(
					'factory_type' => 'post',
					'content_type' => 'c',
				),
				true,
			),
			'bad_post_type_fail' => array(
				array(
					'factory_type' => 'user',
					'content_type' => 'c',
				),
				false,
			),
		);
	}

	/**
	 * @dataProvider get_get_amp_cache_url_for_post_data
	 * @group get-amp-cache-url-for-post	
	 */
	public function test_get_amp_cache_url_for_post( $data, $expected ) {
		$post_id = $this->factory->{$data['factory_type']}->create();
		$got = AMP_Cache_Utilities::get_amp_cache_url_for_post( $post_id );

		if ( $expected ) {

			// replace the scheme with the appropriate cdn url
			$permalink = get_permalink( $post_id );

			if ( 'c' == $data['content_type'] ) {
				$expected = preg_replace('@(http[s]{0,1}\:\/\/)@ui', 'https://cdn.ampproject.org/c/', $permalink);
			} elseif ( 'i' == $data['content_type'] ) {
				$expected = preg_replace('@(http[s]{0,1}\:\/\/)@ui', 'https://cdn.ampproject.org/i/', $permalink);
			} else {
				$expected = false;
			}

			// if there was not a valid scheme, then we should get false
			if ( $got == $permalink ) {
				$expected = false;
			}
		}

		$this->assertEquals( $expected, $got );
	}
}
?>