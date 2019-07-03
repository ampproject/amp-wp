<?php
/**
 * Class AMP_Crowdsignal_Embed_Test
 *
 * @package AMP
 */

/**
 * Class AMP_Crowdsignal_Embed_Test
 *
 * @covers AMP_Crowdsignal_Embed_Handler
 */
class AMP_Crowdsignal_Embed_Test extends WP_UnitTestCase {

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		$poll_response   = array(
			'type'          => 'rich',
			'version'       => '1.0',
			'provider_name' => 'Crowdsignal',
			'provider_url'  => 'https://crowdsignal.com',
			'title'         => 'Which design do you prefer?',
			'html' => '<script type="text/javascript" charset="utf-8" src="https://secure.polldaddy.com/p/7012505.js"></script><noscript><a href="https://poll.fm/7012505">Which design do you prefer?</a></noscript>', // phpcs:ignore
		);
		$survey_response = array(
			'type'          => 'rich',
			'version'       => '1.0',
			'provider_name' => 'Crowdsignal',
			'provider_url'  => 'https://crowdsignal.com',
			'html'          => '<div class="pd-embed" data-settings="{&quot;type&quot;:&quot;iframe&quot;,&quot;auto&quot;:true,&quot;domain&quot;:&quot;rydk.survey.fm&quot;,&quot;id&quot;:&quot;test-survey&quot;}"></div><script type="text/javascript">(function(d,c,j){if(!document.getElementById(j)){var pd=d.createElement(c),s;pd.id=j;pd.src=(\'https:\'==document.location.protocol)?\'https://polldaddy.com/survey.js\':\'http://i0.poll.fm/survey.js\';s=document.getElementsByTagName(c)[0];s.parentNode.insertBefore(pd,s);}}(document,\'script\',\'pd-embed\'));</script>',
		);

		$data = array(
			'poll.fm'          => array(
				'https://poll.fm/7012505',
				'<p><a href="https://poll.fm/7012505">Which design do you prefer?</a></p>',
				$poll_response,
			),

			'polldaddy_poll'   => array(
				'https://polldaddy.com/poll/7012505/',
				'<p><a href="https://poll.fm/7012505">Which design do you prefer?</a></p>',
				$poll_response,
			),

			'polldaddy_survey' => array(
				'https://rydk.polldaddy.com/s/test-survey',
				'<p><a href="https://rydk.polldaddy.com/s/test-survey" target="_blank">View Survey</a></p>',
				$survey_response,
			),
		);

		/*
		 * There is a bug with WordPress's oEmbed handling for Crowdsignal surveys.
		 * See <https://core.trac.wordpress.org/ticket/46467>.
		 */
		if ( version_compare( get_bloginfo( 'version' ), '5.2.0', '>=' ) ) {
			$data['survey.fm'] = array(
				'https://rydk.survey.fm/test-survey',
				'<p><a href="https://rydk.survey.fm/test-survey" target="_blank">View Survey</a></p>',
				$survey_response,
			);
		}

		return $data;
	}

	/**
	 * Test conversion.
	 *
	 * @dataProvider get_conversion_data
	 *
	 * @param string $url             Source.
	 * @param string $expected        Expected.
	 * @param string $oembed_response oEmbed response.
	 */
	public function test_conversion( $url, $expected, $oembed_response ) {
		add_filter(
			'pre_http_request',
			function ( $pre, $r, $request_url ) use ( $url, $oembed_response ) {
				unset( $r );
				if ( false === strpos( $request_url, 'crowdsignal' ) ) {
					return $pre;
				}

				return array(
					'body'     => wp_json_encode( $oembed_response ),
					'response' => array(
						'code'    => 200,
						'message' => 'OK',
					),
				);
			},
			10,
			3
		);

		$embed = new AMP_Crowdsignal_Embed_Handler();
		$embed->register_embed();
		$filtered_content = apply_filters( 'the_content', $url );

		$this->assertEquals( trim( $expected ), trim( $filtered_content ) );
	}
}
