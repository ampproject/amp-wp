<?php
/**
 * Class AMP_Iframe_Converter_Test.
 *
 * @package AMP
 */

use AmpProject\AmpWP\Tests\Helpers\MarkupComparison;

// phpcs:disable WordPress.Arrays.MultipleStatementAlignment.DoubleArrowNotAligned

/**
 * Class AMP_Iframe_Converter_Test
 *
 * @covers AMP_Iframe_Sanitizer
 */
class AMP_Iframe_Converter_Test extends WP_UnitTestCase {

	use MarkupComparison;

	/**
	 * Data provider.
	 *
	 * @return array Data.
	 */
	public function get_data() {
		return [
			'no_iframes'                                => [
				'<p>Lorem Ipsum Demet Delorit.</p>',
				'<p>Lorem Ipsum Demet Delorit.</p>',
			],

			'simple_iframe'                             => [
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class" allowtransparency="false" allowfullscreen></iframe>',
				'
					<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class amp-wp-enforced-sizes" allowfullscreen="" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic">
						<span placeholder="" class="amp-wp-iframe-placeholder"></span>
						<noscript>
							<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class"></iframe>
						</noscript>
					</amp-iframe>
				',
				[
					'add_noscript_fallback' => true,
					'add_placeholder'       => true,
				],
			],

			'simple_iframe_without_noscript_or_placeholder' => [
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class" allowtransparency="FALSE" allowfullscreen></iframe>',
				'<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class amp-wp-enforced-sizes" allowfullscreen="" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic"></amp-iframe>',
				[
					'add_noscript_fallback' => false,
					'add_placeholder'       => false,
				],
			],

			'force_https'                               => [
				'<iframe src="http://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class" allowtransparency="false" allowfullscreen></iframe>',
				'
					<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class amp-wp-enforced-sizes" allowfullscreen="" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic">
						<noscript>
							<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'iframe_without_dimensions'                 => [
				'<iframe src="https://example.com/video/132886713"></iframe>',
				'
					<amp-iframe src="https://example.com/video/132886713" height="400" layout="fixed-height" width="auto" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation">
						<noscript>
							<iframe src="https://example.com/video/132886713"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'iframe_with_height_only'                   => [
				'<iframe src="https://example.com/video/132886713" height="400"></iframe>',
				'
					<amp-iframe src="https://example.com/video/132886713" height="400" layout="fixed-height" width="auto" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation">
						<noscript>
							<iframe src="https://example.com/video/132886713" height="400"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'iframe_with_100_percent_width'             => [
				'<iframe src="https://example.com/video/132886713" height="123" width="100%"></iframe>',
				'
					<amp-iframe src="https://example.com/video/132886713" height="123" width="auto" layout="fixed-height" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation">
						<noscript>
							<iframe src="https://example.com/video/132886713" height="123" width="100%"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'iframe_with_100_percent_width_and_height'  => [
				'<iframe src="https://example.com/video/132886713" width="100%" height="100%"></iframe>',
				'
					<amp-iframe src="https://example.com/video/132886713" layout="fill" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation">
						<noscript>
							<iframe src="https://example.com/video/132886713" width="100%" height="100%"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'iframe_with_width_only'                    => [
				'<iframe src="https://example.com/video/132886713" width="600"></iframe>',
				'
					<amp-iframe src="https://example.com/video/132886713" height="400" layout="fixed-height" width="auto" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation">
						<noscript>
							<iframe src="https://example.com/video/132886713" width="600"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'iframe_with_invalid_frameborder'           => [
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="no"></iframe>',
				'
					<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'iframe_with_1_frameborder'                 => [
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder=1></iframe>',
				'
					<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="1" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="1"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'simple_iframe_with_sandbox'                => [
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" sandbox="allow-same-origin"></iframe>',
				'
					<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" sandbox="allow-same-origin" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com/embed/132886713" width="500" height="281" sandbox="allow-same-origin"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'iframe_with_disallowed_attribute'         => [
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" scrolling="auto"></iframe>',
				'
					<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" scrolling="auto" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com/embed/132886713" width="500" height="281" scrolling="auto"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'iframe_with_sizes_attribute_is_overridden' => [
				'<iframe src="https://example.com/iframe" width="500" height="281"></iframe>',
				'
					<amp-iframe src="https://example.com/iframe" width="500" height="281" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com/iframe" width="500" height="281"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'iframe_with_id_attribute'                  => [
				'<iframe src="https://example.com/iframe" id="myIframe"></iframe>',
				'
					<amp-iframe src="https://example.com/iframe" id="myIframe" height="400" layout="fixed-height" width="auto" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation">
						<noscript>
							<iframe src="https://example.com/iframe" id="myIframe"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'iframe_with_protocol_relative_url'         => [
				'<iframe src="//example.com/video/132886713"></iframe>',
				'
					<amp-iframe src="https://example.com/video/132886713" height="400" layout="fixed-height" width="auto" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation">
						<noscript>
							<iframe src="https://example.com/video/132886713"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'multiple_same_iframe'                      => [
				'
					<iframe src="https://example.com/embed/132886713" width="500" height="281"></iframe>
					<iframe src="https://example.com/embed/132886713" width="500" height="281"></iframe>
					<iframe src="https://example.com/embed/132886713" width="500" height="281"></iframe>
				',
				str_repeat(
					'
						<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
							<noscript>
								<iframe src="https://example.com/embed/132886713" width="500" height="281"></iframe>
							</noscript>
						</amp-iframe>
					',
					3
				),
			],

			'multiple_different_iframes'                => [
				'
					<iframe src="https://example.com/embed/12345" width="500" height="281"></iframe>
					<iframe src="https://example.com/embed/67890" width="280" height="501"></iframe>
					<iframe src="https://example.com/embed/11111" width="700" height="601"></iframe>
				',
				'
					<amp-iframe src="https://example.com/embed/12345" width="500" height="281" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com/embed/12345" width="500" height="281"></iframe>
						</noscript>
					</amp-iframe>
					<amp-iframe src="https://example.com/embed/67890" width="280" height="501" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com/embed/67890" width="280" height="501"></iframe>
						</noscript>
					</amp-iframe>
					<amp-iframe src="https://example.com/embed/11111" width="700" height="601" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com/embed/11111" width="700" height="601"></iframe>
						</noscript>
					</amp-iframe>
				',
			],
			'iframe_in_p_tag'                           => [
				'<p><iframe src="https://example.com/video/132886713" width="500" height="281"></iframe></p>',
				'
					<p>
						<amp-iframe src="https://example.com/video/132886713" width="500" height="281" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
							<noscript>
								<iframe src="https://example.com/video/132886713" width="500" height="281"></iframe>
							</noscript>
						</amp-iframe>
					</p>
				',
			],
			'multiple_iframes_in_p_tag'                 => [
				'<p><iframe src="https://example.com/video/132886713" width="500" height="281"></iframe><iframe src="https://example.com/video/132886714" width="500" height="281"></iframe></p>',
				'
					<p><amp-iframe src="https://example.com/video/132886713" width="500" height="281" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
							<noscript>
								<iframe src="https://example.com/video/132886713" width="500" height="281"></iframe>
							</noscript>
						</amp-iframe>
						<amp-iframe src="https://example.com/video/132886714" width="500" height="281" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
							<noscript>
								<iframe src="https://example.com/video/132886714" width="500" height="281"></iframe>
							</noscript>
						</amp-iframe>
					</p>
				',
			],
			'multiple_iframes_and_contents_in_p_tag'    => [
				'<p>contents<iframe src="https://example.com/video/132886713" width="500" height="281"></iframe><iframe src="https://example.com/video/132886714" width="500" height="281"></iframe></p>',
				'
					<p>contents<amp-iframe src="https://example.com/video/132886713" width="500" height="281" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
							<noscript>
								<iframe src="https://example.com/video/132886713" width="500" height="281"></iframe>
							</noscript>
						</amp-iframe>
						<amp-iframe src="https://example.com/video/132886714" width="500" height="281" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
							<noscript>
								<iframe src="https://example.com/video/132886714" width="500" height="281"></iframe>
							</noscript>
						</amp-iframe>
					</p>
				',
			],
			'iframe_src_with_commas_and_colons'         => [
				'<iframe src="https://www.geoportail.gouv.fr/embed/visu.html?c=3.9735668054865076,43.90558192721261&z=15&l0=GEOLOGY.GEOLOGY::EXTERNAL:OGC:EXTERNALWMS(1)&permalink=yes" width="100" height="200" sandbox="allow-forms allow-scripts allow-same-origin" allowfullscreen=""></iframe>',
				'
					<amp-iframe src="https://www.geoportail.gouv.fr/embed/visu.html?c=3.9735668054865076,43.90558192721261&amp;z=15&amp;l0=GEOLOGY.GEOLOGY::EXTERNAL:OGC:EXTERNALWMS(1)&amp;permalink=yes" width="100" height="200" sandbox="allow-forms allow-scripts allow-same-origin" allowfullscreen="" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://www.geoportail.gouv.fr/embed/visu.html?c=3.9735668054865076,43.90558192721261&amp;z=15&amp;l0=GEOLOGY.GEOLOGY::EXTERNAL:OGC:EXTERNALWMS(1)&amp;permalink=yes" width="100" height="200" sandbox="allow-forms allow-scripts allow-same-origin"></iframe>
						</noscript>
					</amp-iframe>
				',
			],

			'amp_iframe_with_fallback'                  => [
				'<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class amp-wp-enforced-sizes" allowfullscreen="" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic"><noscript><iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class"></iframe></noscript></amp-iframe>',
				null,
			],

			'attributes_removed_from_noscript_iframe'   => [
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" onclick="foo()" data-foo="bar"></iframe>',
				'
					<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" onclick="foo()" data-foo="bar" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<span placeholder="" class="amp-wp-iframe-placeholder"></span>
						<noscript>
							<iframe src="https://example.com/embed/132886713" width="500" height="281"></iframe>
						</noscript>
					</amp-iframe>
				',
				[
					'add_noscript_fallback' => true,
					'add_placeholder'       => true,
				],
			],

			'iframe_relative_url'                       => [
				'<iframe src="/same-origin/" width="50" height="100"></iframe>',
				'<amp-iframe src="https://example.com/same-origin/" width="50" height="100" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
				[
					'add_noscript_fallback' => false,
					'add_placeholder'       => false,
					'current_origin'        => 'https://example.com',
				],
			],

			'iframe_scheme_relative_url'                => [
				'<iframe src="//example.com/same-origin/" width="50" height="100"></iframe>',
				'<amp-iframe src="https://example.com/same-origin/" width="50" height="100" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
				[
					'add_noscript_fallback' => false,
					'add_placeholder'       => false,
					'current_origin'        => 'https://example.com',
				],
			],

			'iframe_relative_url_with_alias_origin'     => [
				'<iframe src="/same-origin/" width="50" height="100"></iframe>',
				'<amp-iframe src="https://alt.example.org/same-origin/" width="50" height="100" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
				[
					'add_noscript_fallback' => false,
					'add_placeholder'       => false,
					'current_origin'        => 'https://example.com',
					'alias_origin'          => 'https://alt.example.org',
				],
			],

			'iframe_absolute_url_with_alias_origin'     => [
				'<iframe src="https://example.com/same-origin/" width="50" height="100"></iframe>',
				'<amp-iframe src="https://alt.example.org/same-origin/" width="50" height="100" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes"></amp-iframe>',
				[
					'add_noscript_fallback' => false,
					'add_placeholder'       => false,
					'current_origin'        => 'https://example.com',
					'alias_origin'          => 'https://alt.example.org',
				],
			],

			'iframe_with_frameborder_no'                => [
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="no" class="iframe-class" allowtransparency="false" allowfullscreen></iframe>',
				'
					<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class amp-wp-enforced-sizes" allowfullscreen="" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic">
						<span placeholder="" class="amp-wp-iframe-placeholder"></span>
						<noscript>
							<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="0" class="iframe-class"></iframe>
						</noscript>
					</amp-iframe>
				',
				[
					'add_noscript_fallback' => true,
					'add_placeholder'       => true,
				],
			],

			'iframe_with_frameborder_yes'               => [
				'<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="yes" class="iframe-class" allowtransparency="false" allowfullscreen></iframe>',
				'
					<amp-iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="1" class="iframe-class amp-wp-enforced-sizes" allowfullscreen="" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic">
						<span placeholder="" class="amp-wp-iframe-placeholder"></span>
						<noscript>
							<iframe src="https://example.com/embed/132886713" width="500" height="281" frameborder="1" class="iframe-class"></iframe>
						</noscript>
					</amp-iframe>
				',
				[
					'add_noscript_fallback' => true,
					'add_placeholder'       => true,
				],
			],

			'iframe_with_full_width_alignment'               => [
				'<figure class="alignfull"><iframe width="580" height="326" src="https://videopress.com/embed/yFCmLMGL?hd=0" frameborder="0" allowfullscreen="" data-origwidth="580" data-origheight="326"></iframe></figure>',
				'
					<figure class="alignfull">
						<amp-iframe width="580" height="326" src="https://videopress.com/embed/yFCmLMGL?hd=0" frameborder="0" allowfullscreen="" data-origwidth="580" data-origheight="326" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="responsive" class="amp-wp-enforced-sizes">
							<span placeholder="" class="amp-wp-iframe-placeholder"></span>
							<noscript>
								<iframe width="580" height="326" src="https://videopress.com/embed/yFCmLMGL?hd=0" frameborder="0"></iframe>
							</noscript>
						</amp-iframe>
					</figure>
				',
				[
					'add_noscript_fallback' => true,
					'add_placeholder'       => true,
					'align_wide_support'    => true,
				],
			],

			'iframe_with_wide_width_alignment'               => [
				'<figure class="alignwide"><iframe width="750" height="422" src="https://videopress.com/embed/yFCmLMGL?hd=0" frameborder="0" allowfullscreen="" data-origwidth="750" data-origheight="422"></iframe></figure>',
				'
					<figure class="alignwide">
						<amp-iframe width="750" height="422" src="https://videopress.com/embed/yFCmLMGL?hd=0" frameborder="0" allowfullscreen="" data-origwidth="750" data-origheight="422" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="responsive" class="amp-wp-enforced-sizes">
							<span placeholder="" class="amp-wp-iframe-placeholder"></span>
							<noscript>
								<iframe width="750" height="422" src="https://videopress.com/embed/yFCmLMGL?hd=0" frameborder="0"></iframe>
							</noscript>
						</amp-iframe>
					</figure>
				',
				[
					'add_noscript_fallback' => true,
					'add_placeholder'       => true,
					'align_wide_support'    => true,
				],
			],

			'test_with_dev_mode' => [
				'<iframe data-ampdevmode="" src="about:blank" onload="alert(\'Hey.\')"></iframe>',
				null, // No change.
				[
					'add_dev_mode' => true,
				],
			],

			'iframe_with_mozallowfullscreen_and_webkitallowfullscreen_attrs' => [
				'<iframe title="Why Backbone" id="talk_frame_48643" src="//speakerdeck.com/player/4648d440a3230130452522b217532879" width="640" height="480" style="border:0; padding:0; margin:0; background:transparent;" frameborder="0" allowtransparency="true" allowfullscreen="allowfullscreen" mozallowfullscreen="true" webkitallowfullscreen="true"></iframe>',
				'
					<amp-iframe title="Why Backbone" id="talk_frame_48643" src="https://speakerdeck.com/player/4648d440a3230130452522b217532879" width="640" height="480" style="border:0; padding:0; margin:0; background:transparent;" frameborder="0" allowtransparency="" allowfullscreen="" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe title="Why Backbone" id="talk_frame_48643" src="https://speakerdeck.com/player/4648d440a3230130452522b217532879" width="640" height="480" style="border:0; padding:0; margin:0; background:transparent;" frameborder="0"></iframe>
						</noscript>
					</amp-iframe>',
			],

			'iframe_with_loading_lazy_attr' => [
				'<iframe loading="lazy" src="https://example.com" width="320" height="640"></iframe>',
				'
					<amp-iframe src="https://example.com" width="320" height="640" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com" width="320" height="640"></iframe>
						</noscript>
					</amp-iframe>',
			],

			'iframe_with_loading_eager_attr' => [
				'<iframe loading="eager" src="https://example.com" width="320" height="640"></iframe>',
				'
					<amp-iframe loading="eager" src="https://example.com" width="320" height="640" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com" width="320" height="640"></iframe>
						</noscript>
					</amp-iframe>',
			],

			'iframe_with_security_attr' => [
				'<iframe security="restricted" src="https://example.com" width="320" height="640"></iframe>',
				'
					<amp-iframe src="https://example.com" width="320" height="640" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com" width="320" height="640"></iframe>
						</noscript>
					</amp-iframe>',
			],

			'iframe_with_type_html_attr' => [
				'<iframe type="text/html" src="https://example.com" width="320" height="640"></iframe>',
				'
					<amp-iframe src="https://example.com" width="320" height="640" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com" width="320" height="640"></iframe>
						</noscript>
					</amp-iframe>',
			],

			'iframe_with_marginheight_and_marginwidth_attrs' => [
				'<iframe marginwidth="0" marginheight="0" src="https://example.com" width="320" height="640"></iframe>',
				'
					<amp-iframe src="https://example.com" width="320" height="640" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<noscript>
							<iframe src="https://example.com" width="320" height="640"></iframe>
						</noscript>
					</amp-iframe>',
			],

			'iframe_with_resizable' => [
				'<iframe resizable src="https://example.com" width="320" height="640"></iframe>',
				'
					<amp-iframe resizable="" src="https://example.com" width="320" height="640" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<span placeholder="" class="amp-wp-iframe-placeholder"></span>
						<button overflow="">Show all</button>
						<noscript>
							<iframe resizable src="https://example.com" width="320" height="640"></iframe>
						</noscript>
					</amp-iframe>
				',
				[
					'add_placeholder' => true,
				],
			],

			'iframe_with_resizable_and_custom_overflow' => [
				'<iframe data-amp-resizable data-amp-overflow-text="Expand me" src="https://example.com" width="320" height="640"></iframe>',
				'
					<amp-iframe resizable="" src="https://example.com" width="320" height="640" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<span placeholder="" class="amp-wp-iframe-placeholder"></span>
						<button overflow="">Expand me</button>
						<noscript>
							<iframe src="https://example.com" width="320" height="640"></iframe>
						</noscript>
					</amp-iframe>
				',
				[
					'add_placeholder' => true,
				],
			],

			'iframe_with_custom_placeholder' => [
				'
					<iframe src="https://example.com" width="320" height="640">
						<a placeholder href="https://example.com">Loading example site...</a>
					</iframe>
				',
				'
					<amp-iframe src="https://example.com" width="320" height="640" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<a placeholder href="https://example.com">Loading example site...</a>
						<noscript>
							<iframe src="https://example.com" width="320" height="640"></iframe>
						</noscript>
					</amp-iframe>
				',
				[
					'add_placeholder' => true,
				],
			],

			'iframe_with_custom_overflow' => [
				'
					<iframe resizable src="https://example.com" width="320" height="640">
						<button overflow>See more</button>
					</iframe>
				',
				'
					<amp-iframe resizable="" src="https://example.com" width="320" height="640" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes">
						<span placeholder="" class="amp-wp-iframe-placeholder"></span>
						<button overflow>See more</button>
						<noscript>
							<iframe resizable src="https://example.com" width="320" height="640"></iframe>
						</noscript>
					</amp-iframe>
				',
				[
					'add_placeholder' => true,
				],
			],

			'iframe_with_100_percent_with_100_percent_height_position_absolute_top_zero_left_zero' => [
				'
					<iframe
						style="width: 100%; height: 100%; position: absolute; left: 0px; top: 0px;"
						src="https://example.com/video/132886713"
						width="100%"
						height="100%"
					>
					</iframe>
				',
				'
					<amp-iframe src="https://example.com/video/132886713" layout="fill" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation">
						<noscript>
							<iframe style="width: 100%; height: 100%; position: absolute; left: 0px; top: 0px;" src="https://example.com/video/132886713" width="100%" height="100%"></iframe>
						</noscript>
					</amp-iframe>
				',
			],
		];
	}

	/**
	 * Test converter.
	 *
	 * @dataProvider get_data
	 *
	 * @param string $source   Source.
	 * @param string $expected Expected.
	 * @param array  $args     Sanitizer args.
	 */
	public function test_converter( $source, $expected = null, $args = [] ) {
		if ( ! $expected ) {
			$expected = $source;
		}
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		if ( ! empty( $args['add_dev_mode'] ) ) {
			$dom->documentElement->setAttribute( AMP_Rule_Spec::DEV_MODE_ATTRIBUTE, '' );
		}

		$sanitizer = new AMP_Iframe_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

		$sanitizer = new AMP_Layout_Sanitizer( $dom, $args );
		$sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEqualMarkup( $expected, $content );
	}

	/**
	 * Test HTTPS required.
	 */
	public function test__https_required() {
		$source   = '<iframe src="http://example.com/embed/132886713"></iframe>';
		$expected = '';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Iframe_Sanitizer(
			$dom,
			[
				'add_placeholder'   => true,
				'require_https_src' => true,
			]
		);
		$sanitizer->sanitize();

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * Test get_scripts() did not convert.
	 */
	public function test_get_scripts__didnt_convert() {
		$source   = '<p>Hello World</p>';
		$expected = [];

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Iframe_Sanitizer( $dom );
		$sanitizer->sanitize();

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();

		$scripts = array_merge(
			$sanitizer->get_scripts(),
			$validating_sanitizer->get_scripts()
		);
		$this->assertEquals( $expected, $scripts );
	}

	/**
	 * Test get_scripts() did convert.
	 */
	public function test_get_scripts__did_convert() {
		$source   = '<iframe src="https://example.com/embed/132886713" width="500" height="281"></iframe>';
		$expected = [ 'amp-iframe' => true ];

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Iframe_Sanitizer( $dom );
		$sanitizer->sanitize();

		$validating_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$validating_sanitizer->sanitize();

		$scripts = array_merge(
			$sanitizer->get_scripts(),
			$validating_sanitizer->get_scripts()
		);
		$this->assertEquals( $expected, $scripts );
	}

	/**
	 * Test args placeholder.
	 */
	public function test__args__placeholder() {
		$source   = '<p><iframe src="https://example.com/video/132886713" width="500" height="281"></iframe></p>';
		$expected = '<p><amp-iframe src="https://example.com/video/132886713" width="500" height="281" sandbox="allow-downloads allow-forms allow-modals allow-orientation-lock allow-pointer-lock allow-popups allow-popups-to-escape-sandbox allow-presentation allow-same-origin allow-scripts allow-top-navigation-by-user-activation" layout="intrinsic" class="amp-wp-enforced-sizes"><span placeholder="" class="amp-wp-iframe-placeholder"></span><noscript><iframe src="https://example.com/video/132886713" width="500" height="281"></iframe></noscript></amp-iframe></p>';

		$dom       = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Iframe_Sanitizer(
			$dom,
			[
				'add_placeholder' => true,
			]
		);
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $content );
	}
}
