<?php

require_once( AMP__DIR__ . '/includes/sanitizers/class-amp-allowed-tags-generated.php' );

class AMP_Tag_And_Attribute_Sanitizer_Test extends WP_UnitTestCase {

	protected $allowed_tags;
	protected $globally_allowed_attrs;

	public function get_data() {
		return array(
			'empty_doc' => array(
				'',
				''
			),

				'a-test' => array(
				'<a on="tap:see-image-lightbox" role="button" class="button button-secondary play" tabindex="0">Show Image</a>',
				'<a on="tap:see-image-lightbox" role="button" class="button button-secondary play" tabindex="0">Show Image</a>'
			),

			'a4a' => array(
				'<amp-ad width="300" height="400" type="fake" data-use-a4a="true" data-vars-analytics-var="bar" src="fake_amp.json"><div placeholder=""></div><div fallback=""></div></amp-ad>',
				'<amp-ad width="300" height="400" type="fake" data-use-a4a="true" data-vars-analytics-var="bar" src="fake_amp.json"><div placeholder=""></div><div fallback=""></div></amp-ad>',
			),

			'ads' => array(
				'<amp-ad width="300" height="250" type="a9" data-aax_size="300x250" data-aax_pubname="test123" data-aax_src="302"><div placeholder=""></div><div fallback=""></div></amp-ad>',
				'<amp-ad width="300" height="250" type="a9" data-aax_size="300x250" data-aax_pubname="test123" data-aax_src="302"><div placeholder=""></div><div fallback=""></div></amp-ad>',
			),

			'adsense' => array(
				'<amp-ad width="300" height="250" type="adsense" data-ad-client="ca-pub-2005682797531342" data-ad-slot="7046626912"><div placeholder=""></div><div fallback=""></div></amp-ad>',
				'<amp-ad width="300" height="250" type="adsense" data-ad-client="ca-pub-2005682797531342" data-ad-slot="7046626912"><div placeholder=""></div><div fallback=""></div></amp-ad>',
			),

			'amp-user-notification' => array(
				'<amp-user-notification layout="nodisplay" id="amp-user-notification1" data-show-if-href="https://example.com/api/show?timestamp=TIMESTAMP" data-dismiss-href="https://example.com/api/echo/post">This site uses cookies to personalize content.<a class="btn" on="tap:amp-user-notification1.dismiss">I accept</a></amp-user-notification>',
				'<amp-user-notification layout="nodisplay" id="amp-user-notification1" data-show-if-href="https://example.com/api/show?timestamp=TIMESTAMP" data-dismiss-href="https://example.com/api/echo/post">This site uses cookies to personalize content.<a class="btn" on="tap:amp-user-notification1.dismiss">I accept</a></amp-user-notification>',
			),

			'amp-apester-media' => array(
				'<amp-apester-media height="444" data-apester-media-id="57a336dba187a2ca3005e826" layout="fixed-height"></amp-apester-media>',
				'<amp-apester-media height="444" data-apester-media-id="57a336dba187a2ca3005e826" layout="fixed-height"></amp-apester-media>',
			),

			'button' => array(
				'<button on="tap:AMP.setState(foo=\'foo\', isButtonDisabled=true, textClass=\'redBackground\', imgSrc=\'https://ampbyexample.com/img/Shetland_Sheepdog.jpg\', imgSize=200, imgAlt=\'Sheepdog\', videoSrc=\'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4\')">Click me</button>',
				'<button on="tap:AMP.setState(foo=\'foo\', isButtonDisabled=true, textClass=\'redBackground\', imgSrc=\'https://ampbyexample.com/img/Shetland_Sheepdog.jpg\', imgSize=200, imgAlt=\'Sheepdog\', videoSrc=\'https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4\')">Click me</button>',
			),

			'brid-player' => array(
				'<amp-brid-player data-partner="264" data-player="4144" data-video="13663" layout="responsive" width="480" height="270"></amp-brid-player>',
				'<amp-brid-player data-partner="264" data-player="4144" data-video="13663" layout="responsive" width="480" height="270"></amp-brid-player>',
			),

			'brightcove' => array(
				'<amp-brightcove data-account="906043040001" data-video-id="1401169490001" data-player="180a5658-8be8-4f33-8eba-d562ab41b40c" layout="responsive" width="480" height="270"></amp-brightcove>',
				'<amp-brightcove data-account="906043040001" data-video-id="1401169490001" data-player="180a5658-8be8-4f33-8eba-d562ab41b40c" layout="responsive" width="480" height="270"></amp-brightcove>',
			),

			'carousel' => array(
				'<amp-carousel width="400" height="300" layout="responsive" type="slides" controls=""><div>hello world</div><amp-img src="https://lh3.googleusercontent.com/pSECrJ82R7-AqeBCOEPGPM9iG9OEIQ_QXcbubWIOdkY=w400-h300-no-n" layout="fill"></amp-img><amp-img src="https://lh3.googleusercontent.com/5rcQ32ml8E5ONp9f9-Rf78IofLb9QjS5_0mqsY1zEFc=w400-h300-no-n" width="400" height="300" layout="responsive"></amp-img><amp-img src="https://lh3.googleusercontent.com/Z4gtm5Bkxyv21Z2PtbTf95Clb9AE4VTR6olbBKYrenM=w400-h300-no-n" width="400" height="300" layout="responsive"></amp-img><amp-soundcloud height="300" layout="fixed-height" data-trackid="243169232"></amp-soundcloud><amp-youtube data-videoid="mGENRKrdoGY" width="400" height="300"></amp-youtube><amp-anim src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no" width="400" height="300"><amp-img placeholder="" src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no-k" width="400" height="300"></amp-img></amp-anim><amp-audio src="https://ia801402.us.archive.org/16/items/EDIS-SRP-0197-06/EDIS-SRP-0197-06.mp3"></amp-audio><amp-brightcove data-account="906043040001" data-video-id="1401169490001" data-player="180a5658-8be8-4f33-8eba-d562ab41b40c" layout="responsive" width="480" height="270"></amp-brightcove><amp-vimeo data-videoid="27246366" width="500" height="281"></amp-vimeo><amp-dailymotion data-videoid="x3rdtfy" width="500" height="281"></amp-dailymotion><amp-vine data-vineid="MdKjXez002d" width="381" height="381" layout="responsive"></amp-vine><amp-video src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4" width="358" height="204" layout="responsive" controls=""></amp-video></amp-carousel><amp-carousel width="auto" height="300" controls=""><div>hello world</div><amp-img src="https://lh3.googleusercontent.com/pSECrJ82R7-AqeBCOEPGPM9iG9OEIQ_QXcbubWIOdkY=w400-h300-no-n" width="400" height="300"></amp-img><amp-img src="https://lh3.googleusercontent.com/5rcQ32ml8E5ONp9f9-Rf78IofLb9QjS5_0mqsY1zEFc=w400-h300-no-n" width="400" height="300"></amp-img><amp-img src="https://lh3.googleusercontent.com/Z4gtm5Bkxyv21Z2PtbTf95Clb9AE4VTR6olbBKYrenM=w400-h300-no-n" width="400" height="300"></amp-img><amp-soundcloud height="300" layout="fixed-height" data-trackid="243169232"></amp-soundcloud><amp-youtube data-videoid="mGENRKrdoGY" width="400" height="300"></amp-youtube><amp-anim src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no" width="400" height="300"><amp-img placeholder="" src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no-k" width="400" height="300"></amp-img></amp-anim><amp-audio src="https://ia801402.us.archive.org/16/items/EDIS-SRP-0197-06/EDIS-SRP-0197-06.mp3"></amp-audio><amp-brightcove data-account="906043040001" data-video-id="1401169490001" data-player="180a5658-8be8-4f33-8eba-d562ab41b40c" layout="responsive" width="300" height="300"></amp-brightcove><amp-vimeo data-videoid="27246366" width="300" height="300"></amp-vimeo><amp-dailymotion data-videoid="x3rdtfy" width="300" height="300"></amp-dailymotion><amp-vine data-vineid="MdKjXez002d" width="300" height="300"></amp-vine><amp-video src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4" width="300" height="300" controls=""></amp-video></amp-carousel>',
				'<amp-carousel width="400" height="300" layout="responsive" type="slides" controls=""><div>hello world</div><amp-img src="https://lh3.googleusercontent.com/pSECrJ82R7-AqeBCOEPGPM9iG9OEIQ_QXcbubWIOdkY=w400-h300-no-n" layout="fill"></amp-img><amp-img src="https://lh3.googleusercontent.com/5rcQ32ml8E5ONp9f9-Rf78IofLb9QjS5_0mqsY1zEFc=w400-h300-no-n" width="400" height="300" layout="responsive"></amp-img><amp-img src="https://lh3.googleusercontent.com/Z4gtm5Bkxyv21Z2PtbTf95Clb9AE4VTR6olbBKYrenM=w400-h300-no-n" width="400" height="300" layout="responsive"></amp-img><amp-soundcloud height="300" layout="fixed-height" data-trackid="243169232"></amp-soundcloud><amp-youtube data-videoid="mGENRKrdoGY" width="400" height="300"></amp-youtube><amp-anim src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no" width="400" height="300"><amp-img placeholder="" src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no-k" width="400" height="300"></amp-img></amp-anim><amp-audio src="https://ia801402.us.archive.org/16/items/EDIS-SRP-0197-06/EDIS-SRP-0197-06.mp3"></amp-audio><amp-brightcove data-account="906043040001" data-video-id="1401169490001" data-player="180a5658-8be8-4f33-8eba-d562ab41b40c" layout="responsive" width="480" height="270"></amp-brightcove><amp-vimeo data-videoid="27246366" width="500" height="281"></amp-vimeo><amp-dailymotion data-videoid="x3rdtfy" width="500" height="281"></amp-dailymotion><amp-vine data-vineid="MdKjXez002d" width="381" height="381" layout="responsive"></amp-vine><amp-video src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4" width="358" height="204" layout="responsive" controls=""></amp-video></amp-carousel><amp-carousel width="auto" height="300" controls=""><div>hello world</div><amp-img src="https://lh3.googleusercontent.com/pSECrJ82R7-AqeBCOEPGPM9iG9OEIQ_QXcbubWIOdkY=w400-h300-no-n" width="400" height="300"></amp-img><amp-img src="https://lh3.googleusercontent.com/5rcQ32ml8E5ONp9f9-Rf78IofLb9QjS5_0mqsY1zEFc=w400-h300-no-n" width="400" height="300"></amp-img><amp-img src="https://lh3.googleusercontent.com/Z4gtm5Bkxyv21Z2PtbTf95Clb9AE4VTR6olbBKYrenM=w400-h300-no-n" width="400" height="300"></amp-img><amp-soundcloud height="300" layout="fixed-height" data-trackid="243169232"></amp-soundcloud><amp-youtube data-videoid="mGENRKrdoGY" width="400" height="300"></amp-youtube><amp-anim src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no" width="400" height="300"><amp-img placeholder="" src="https://lh3.googleusercontent.com/qNn8GDz8Jfd-s9lt3Nc4lJeLjVyEaqGJTk1vuCUWazCmAeOBVjSWDD0SMTU7x0zhVe5UzOTKR0n-kN4SXx7yElvpKYvCMaRyS_g-jydhJ_cEVYmYPiZ_j1Y9de43mlKxU6s06uK1NAlpbSkL_046amEKOdgIACICkuWfOBwlw2hUDfjPOWskeyMrcTu8XOEerCLuVqXugG31QC345hz3lUyOlkdT9fMYVUynSERGNzHba7bXMOxKRe3izS5DIWUgJs3oeKYqA-V8iqgCvneD1jj0Ff68V_ajm4BDchQubBJU0ytXVkoWh27ngeEHubpnApOS6fcGsjPxeuMjnzAUtoTsiXz2FZi1mMrxrblJ-kZoAq1DJ95cnoqoa2CYq3BTgq2E8BRe2paNxLJ5GXBCTpNdXMpVJc6eD7ceijQyn-2qanilX-iK3ChbOq0uBHMvsdoC_LsFOu5KzbbNH71vM3DPkvDGmHJmF67Vj8sQ6uBrLnzpYlCyN4-Y9frR8zugDcqX5Q=w400-h300-no-k" width="400" height="300"></amp-img></amp-anim><amp-audio src="https://ia801402.us.archive.org/16/items/EDIS-SRP-0197-06/EDIS-SRP-0197-06.mp3"></amp-audio><amp-brightcove data-account="906043040001" data-video-id="1401169490001" data-player="180a5658-8be8-4f33-8eba-d562ab41b40c" layout="responsive" width="300" height="300"></amp-brightcove><amp-vimeo data-videoid="27246366" width="300" height="300"></amp-vimeo><amp-dailymotion data-videoid="x3rdtfy" width="300" height="300"></amp-dailymotion><amp-vine data-vineid="MdKjXez002d" width="300" height="300"></amp-vine><amp-video src="https://commondatastorage.googleapis.com/gtv-videos-bucket/sample/ForBiggerJoyrides.mp4" width="300" height="300" controls=""></amp-video></amp-carousel>',
			),

			'amp-dailymotion' => array(
				'<amp-dailymotion data-videoid="x3rdtfy" width="500" height="281"></amp-dailymotion><h4>Default (responsive)</h4><amp-dailymotion data-videoid="x3rdtfy" width="500" height="281" layout="responsive"></amp-dailymotion><h4>Custom</h4><amp-dailymotion data-videoid="x3rdtfy" data-endscreen-enable="false" data-sharing-enable="false" data-ui-highlight="444444" data-ui-logo="false" data-info="false" width="640" height="360"></amp-dailymotion>',
				'<amp-dailymotion data-videoid="x3rdtfy" width="500" height="281"></amp-dailymotion><h4>Default (responsive)</h4><amp-dailymotion data-videoid="x3rdtfy" width="500" height="281" layout="responsive"></amp-dailymotion><h4>Custom</h4><amp-dailymotion data-videoid="x3rdtfy" data-endscreen-enable="false" data-sharing-enable="false" data-ui-highlight="444444" data-ui-logo="false" data-info="false" width="640" height="360"></amp-dailymotion>',
			),

			'doubleclick-1' => array(
				'<amp-ad width="480" height="75" type="doubleclick" data-slot="/4119129/mobile_ad_banner" data-multi-size="320x50" class="dashedborder"></amp-ad>',
				'<amp-ad width="480" height="75" type="doubleclick" data-slot="/4119129/mobile_ad_banner" data-multi-size="320x50" class="dashedborder"></amp-ad>',
			),

			'facebook' => array(
				'<amp-facebook width="552" height="303" layout="responsive" data-href="https://www.facebook.com/zuck/posts/10102593740125791"></amp-facebook><h1>More Posts</h1>',
				'<amp-facebook width="552" height="303" layout="responsive" data-href="https://www.facebook.com/zuck/posts/10102593740125791"></amp-facebook><h1>More Posts</h1>',
			),

			'font' => array(
				'<amp-font layout="nodisplay" font-family="Comic AMP" timeout="2000"></amp-font><amp-font layout="nodisplay" font-family="Comic AMP Bold" timeout="3000" font-weight="bold"></amp-font>',
				'<amp-font layout="nodisplay" font-family="Comic AMP" timeout="2000"></amp-font><amp-font layout="nodisplay" font-family="Comic AMP Bold" timeout="3000" font-weight="bold"></amp-font>',
			),

			'form' => array(
				'<form method="get" action="/form/search-html/get" target="_blank"><fieldset><label><span>Search for</span><input type="search" name="term" required=""/></label><input type="submit" value="Search"/></fieldset></form>',
				'<form method="get" action="/form/search-html/get" target="_blank"><fieldset><label><span>Search for</span><input type="search" name="term" required=""/></label><input type="submit" value="Search"/></fieldset></form>',
			),

			'gfycat' => array(
				'<amp-gfycat data-gfyid="BareSecondaryFlamingo" width="225" height="400"></amp-gfycat>',
				'<amp-gfycat data-gfyid="BareSecondaryFlamingo" width="225" height="400"></amp-gfycat>',
			),

			'empty_element' => array(
				'<br/>',
				'<br/>'
			),

			'merge_two_attr_specs' => array(
				'<div submit-success>Whatever</div>',
				'<div>Whatever</div>'
			),

			'attribute_value_blacklisted_by_regex_removed' => array(
				'<a href="__amp_source_origin">Click me.</a>',
				'<a>Click me.</a>'
			),

			'host_relative_url_allowed' => array(
				'<a href="/path/to/content">Click me.</a>',
				'<a href="/path/to/content">Click me.</a>'
			),

			'protocol_relative_url_allowed' => array(
				'<a href="//example.com/path/to/content">Click me.</a>',
				'<a href="//example.com/path/to/content">Click me.</a>'
			),

			'node_with_whiteilsted_protocol_http_allowed' => array(
				'<a href="http://example.com/path/to/content">Click me.</a>',
				'<a href="http://example.com/path/to/content">Click me.</a>',
			),

			'node_with_whiteilsted_protocol_https_allowed' => array(
				'<a href="https://example.com/path/to/content">Click me.</a>',
				'<a href="https://example.com/path/to/content">Click me.</a>',
			),

			'node_with_whiteilsted_protocol_fb-messenger_allowed' => array(
				'<a href="fb-messenger://example.com/path/to/content">Click me.</a>',
				'<a href="fb-messenger://example.com/path/to/content">Click me.</a>',
			),

			'attribute_with_disallowed_protocol_removed' => array(
				'<a href="evil://example.com/path/to/content">Click me.</a>',
				'<a>Click me.</a>'
			),

			'attribute_value_valid' => array(
				'<template type="amp-mustache">Template Data</template>',
				'<template type="amp-mustache">Template Data</template>',
			),

			'attribute_value_valid' => array(
				'<template type="bad-type">Template Data</template>',
				'<template>Template Data</template>',
			),

			'attribute_amp_accordion_value' => array(
				'<amp-accordion disable-session-states="">test</amp-accordion>',
				'<amp-accordion disable-session-states="">test</amp-accordion>'
			),

			'attribute_value_with_blacklisted_regex_removed' => array(
				'<a rel="import">Click me.</a>',
				'<a>Click me.</a>'
			),

			'attribute_value_with_blacklisted_multi-part_regex_removed' => array(
				'<a rel="something else import">Click me.</a>',
				'<a>Click me.</a>'
			),

			'attribute_value_with_required_regex' => array(
				'<a target="_blank">Click me.</a>',
				'<a target="_blank">Click me.</a>',
			),

			'attribute_value_with_disallowed_required_regex_removed' => array(
				'<a target="_not_blank">Click me.</a>',
				'<a>Click me.</a>',
			),

			'attribute_value_with_required_value_casei_lower' => array(
				'<a type="text/html">Click.me.</a>',
				'<a type="text/html">Click.me.</a>',
			),

			'attribute_value_with_required_value_casei_upper' => array(
				'<a type="TEXT/HTML">Click.me.</a>',
				'<a type="TEXT/HTML">Click.me.</a>',
			),

			'attribute_value_with_required_value_casei_mixed' => array(
				'<a type="TeXt/HtMl">Click.me.</a>',
				'<a type="TeXt/HtMl">Click.me.</a>',
			),

			'attribute_value_with_bad_value_casei_removed' => array(
				'<a type="bad_type">Click.me.</a>',
				'<a>Click.me.</a>',
			),

			'attribute_value_with_value_regex_casei_lower' => array(
				'<amp-dailymotion data-videoid="abc"></amp-dailymotion>',
				'<amp-dailymotion data-videoid="abc"></amp-dailymotion>',
			),

			'attribute_value_with_value_regex_casei_upper' => array(
				'<amp-dailymotion data-videoid="ABC"></amp-dailymotion>',
				'<amp-dailymotion data-videoid="ABC"></amp-dailymotion>',
			),

			'attribute_value_with_bad_value_regex_casei_removed' => array(
				'<amp-dailymotion data-videoid="###"></amp-dailymotion>',
				'<amp-dailymotion></amp-dailymotion>',
			),

			'atribute_bad_attr_with_no_value_removed' => array(
				'<amp-ad bad-attr-no-value>something here</amp-alt>',
				'<amp-ad>something here</amp-ad>'
			),

			'atribute_bad_attr_with_value_removed' => array(
				'<amp-ad bad-attr="some-value">something here</amp-alt>',
				'<amp-ad>something here</amp-ad>'
			),

			'nodes_with_non_whitelisted_tags_replaced_by_children' => array(
				'<invalid_tag>this is some text inside the invalid node</invalid_tag>',
				'this is some text inside the invalid node',
			),

			'empty_parent_nodes_of_non_whitelisted_tags_removed' => array(
				'<div><span><span><invalid_tag></invalid_tag></span></span></div>',
				'',
			),

			'replace_non_whitelisted_node_with_children' => array(
				'<p>This is some text <invalid_tag>with a disallowed tag</invalid_tag> in the middle of it.</p>',
				'<p>This is some text with a disallowed tag in the middle of it.</p>',
			),

			'remove_attribute_on_node_with_missing_mandatory_parent' => array(
				'<div submit-success>This is a test.</div>',
				'<div>This is a test.</div>',
			),

			'leave_attribute_on_node_with_present_mandatory_parent' => array(
				'<form><div submit-success>This is a test.</div></form>',
				'<form><div submit-success="">This is a test.</div></form>',
			),

			'disallowed_empty_attr_removed' => array(
				'<amp-user-notification data-dismiss-href></amp-user-notification>',
				'<amp-user-notification></amp-user-notification>',
			),

			'allowed_empty_attr' => array(
				'<a border=""></a>',
				'<a border=""></a>',
			),

			'remove_node_with_disallowed_ancestor' => array(
				'<amp-sidebar>The sidebar<amp-ad>This node is not allowed here.</amp-ad></amp-sidebar>',
				'<amp-sidebar>The sidebar</amp-sidebar>',
			),

			'remove_node_without_mandatory_ancestor' => array(
				'<div>All I have is this div, when all you want is a noscript tag.<audio>Sweet tunes</audio></div>',
				'<div>All I have is this div, when all you want is a noscript tag.</div>',
			),

			'amp-img_with_good_protocols' => array(
				'<amp-img srcset="https://example.com/resource1, https://example.com/resource2"></amp-img>',
				'<amp-img srcset="https://example.com/resource1, https://example.com/resource2"></amp-img>',
			),

			'amp-img_with_bad_protocols' => array(
				'<amp-img srcset="https://somewhere.com/resource1, evil://somewhereelse.com/resource2"></amp-img>',
				'<amp-img></amp-img>',
			),


			// Test Cases from test-amp-blacklist-sanitizer.php

			// 'disallowed_tag_with_innertext' => array(
			// 	'<script>alert("")</script>',
			// 	''
			// ),

			// 'multiple_disallowed_tags_only' => array(
			// 	'<clearly_not_allowed /><script>alert("")</script><style>body{ color: red; }</style>',
			// 	''
			// ),

			// 'multiple_disallowed_tags_only_in_child' => array(
			// 	'<p><clearly_not_allowed /><script>alert("")</script><style>body{ color: red; }</style></p>',
			// 	''
			// ),

			'allowed_tag_only' => array(
				'<p>Text</p><img src="/path/to/file.jpg" />',
				'<p>Text</p>'
			),

			'disallowed_attributes' => array(
				'<a href="/path/to/file.jpg" style="border: 1px solid red;">Link</a>',
				'<a href="/path/to/file.jpg">Link</a>'
			),

			'onclick_attribute' => array(
				'<a href="/path/to/file.jpg" onclick="alert(e);">Link</a>',
				'<a href="/path/to/file.jpg">Link</a>'
			),

			'on_attribute' => array(
				'<button on="tap:my-lightbox">Tap Me</button>',
				'<button on="tap:my-lightbox">Tap Me</button>'
			),

			'multiple_disallowed_attributes' => array(
				'<a href="/path/to/file.jpg" style="border: 1px solid red;" onclick="alert(e);">Link</a>',
				'<a href="/path/to/file.jpg">Link</a>',
			),

			'javascript_protocol' => array(
				'<a href="javascript:alert(\'Hello\');">Click</a>',
				'<a>Click</a>'
			),

			'attribute_recursive' => array(
				'<div style="border: 1px solid red;"><a href="/path/to/file.jpg" onclick="alert(e);">Hello World</a></div>',
				'<div><a href="/path/to/file.jpg">Hello World</a></div>'
			),

			'mixed_tags' => array(
				'<input type="text"/><p>Text</p><style>body{ color: red; }</style>',
				'<input type="text"/><p>Text</p>'
			),

			'no_strip_amp_tags' => array(
				'<amp-img src="http://example.com/path/to/file.jpg" width="300" height="300"></amp-img>',
				'<amp-img src="http://example.com/path/to/file.jpg" width="300" height="300"></amp-img>'
			),

			'a_with_attachment_rel' => array(
				'<a href="http://example.com" rel="wp-att-1686">Link</a>',
				'<a href="http://example.com" rel="wp-att-1686">Link</a>',
			),

			'a_with_attachment_rel_plus_another_valid_value' => array(
				'<a href="http://example.com" rel="attachment wp-att-1686">Link</a>',
				'<a href="http://example.com" rel="attachment wp-att-1686">Link</a>',
			),

			'a_with_rev' => array(
				'<a href="http://example.com" rev="footnote">Link</a>',
				'<a href="http://example.com">Link</a>',
			),

			'a_with_target_blank' => array(
				'<a href="http://example.com" target="_blank">Link</a>',
				'<a href="http://example.com" target="_blank">Link</a>',
			),

			'a_with_target_uppercase_blank' => array(
				'<a href="http://example.com" target="_BLANK">Link</a>',
				'<a href="http://example.com">Link</a>',
			),

			'a_with_target_new' => array(
				'<a href="http://example.com" target="_new">Link</a>',
				'<a href="http://example.com">Link</a>',
			),

			'a_with_target_self' => array(
				'<a href="http://example.com" target="_self">Link</a>',
				'<a href="http://example.com" target="_self">Link</a>',
			),

			'a_with_target_invalid' => array(
				'<a href="http://example.com" target="boom">Link</a>',
				'<a href="http://example.com">Link</a>',
			),

			'a_with_href_invalid' => array(
				'<a href="some random text">Link</a>',
				'<a href="some random text">Link</a>',
			),

			'a_with_href_scheme_invalid' => array(
				'<a href="wp://alinktosomething">Link</a>',
				'<a>Link</a>',
			),

			'a_with_href_scheme_tel' => array(
				'<a href="tel:4166669999">Call Me, Maybe</a>',
				'<a href="tel:4166669999">Call Me, Maybe</a>',
			),

			'a_with_href_scheme_sms' => array(
				'<a href="sms:4166669999">SMS Me, Maybe</a>',
				'<a href="sms:4166669999">SMS Me, Maybe</a>',
			),

			'a_with_href_scheme_mailto' => array(
				'<a href="mailto:email@example.com">Email Me, Maybe</a>',
				'<a href="mailto:email@example.com">Email Me, Maybe</a>',
			),

			'a_with_href_relative' => array(
				'<a href="/home">Home</a>',
				'<a href="/home">Home</a>',
			),

			'a_with_anchor' => array(
				'<a href="#section2">Home</a>',
				'<a href="#section2">Home</a>',
			),

			'a_is_anchor' => array(
				'<a name="section2"></a>',
				'<a name="section2"></a>',
			),

			'a_is_achor_with_id' => array(
				'<a id="section3"></a>',
				'<a id="section3"></a>',
			),

			'a_empty' => array(
				'<a>Hello World</a>',
				'<a>Hello World</a>',
			),

			'a_empty_with_children_with_restricted_attributes' => array(
				'<a><span style="color: red;">Red</span>&amp;<span style="color: blue;">Orange</span></a>',
				'<a><span>Red</span>&amp;<span>Orange</span></a>'
			),

			'h1_with_size' => array(
				'<h1 size="1">Headline</h1>',
				'<h1>Headline</h1>',
			),

			'font' => array(
				'<font size="1">Headline</font>',
				'Headline',
			),

			// font is removed so we should check that other elements are checked as well
			'font_with_other_bad_elements' => array(
				'<font size="1">Headline</font><span style="color: blue">Span</span>',
				'Headline<span>Span</span>',
			),
		);
	}

	public function setUp() {
		$this->allowed_tags = apply_filters( 'amp_allowed_tags', AMP_Allowed_Tags_Generated::get_allowed_tags() );
		$this->globally_allowed_attributes = apply_filters( 'amp_globally_allowed_attributes', AMP_Allowed_Tags_Generated::get_allowed_attributes() );
	}

	/**
	 * @dataProvider get_data
	 * @group allowed-tags
	 */
	public function test_sanitizer( $source, $expected ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $source );
		$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$sanitizer->sanitize();
		$content = AMP_DOM_Utils::get_content_from_dom( $dom );
		$this->assertEquals( $expected, $content );
	}

	/**
	 * @group allowed-tags
	 */
	public function test_validate_attr_spec_rules() {

		$test_data = array(
			'test_attr_spec_rule_mandatory_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-img',
				'attribute_name' => 'src',
				'attribute_value' => '/path/to/resource',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_mandatory',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_mandatory_alternate_attr_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-img',
				'attribute_name' => 'src',
				'use_alternate_name' => 'srcset',
				'attribute_value' => '/path/to/resource',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_mandatory',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_mandatory_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-img',
				'attribute_name' => 'src',
				'attribute_value' => '/path/to/resource',
				'include_attr' => false,
				'include_attr_value' => false,
				'func_name' => 'test_attr_spec_rule_mandatory',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_mandatory_na' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-img',
				'attribute_name' => 'alt',
				'attribute_value' => 'alternate',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_mandatory',
				'expected' => AMP_Rule_Spec::not_applicable,
			),



			'test_attr_spec_rule_value_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'template',
				'attribute_name' => 'type',
				'attribute_value' => 'amp-mustache',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_value',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_value_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'template',
				'attribute_name' => 'type',
				'attribute_value' => 'invalid',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_value',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_value_not_applicable' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'template',
				'attribute_name' => 'type',
				'attribute_value' => 'invalid',
				'include_attr' => false,
				'include_attr_value' => false,
				'func_name' => 'test_attr_spec_rule_value',
				'expected' => AMP_Rule_Spec::not_applicable,
			),




			'test_attr_spec_rule_value_casei_lower_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'type',
				'attribute_value' => 'text/html',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_value_casei',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_value_casei_upper_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'type',
				'attribute_value' => 'TEXT/HTML',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_value_casei',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_value_casei_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'type',
				'attribute_value' => 'invalid',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_value_casei',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_value_casei_na' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'template',
				'attribute_name' => 'type',
				'attribute_value' => 'invalid',
				'include_attr' => false,
				'include_attr_value' => false,
				'func_name' => 'test_attr_spec_rule_value_casei',
				'expected' => AMP_Rule_Spec::not_applicable,
			),



			'test_attr_spec_rule_value_regex_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'target',
				'attribute_value' => '_blank',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_value_regex',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_value_regex_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'target',
				'attribute_value' => '_blankzzz',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_value_regex',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_value_regex_na' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'target',
				'attribute_value' => 'invalid',
				'include_attr' => false,
				'include_attr_value' => false,
				'func_name' => 'test_attr_spec_rule_value_regex',
				'expected' => AMP_Rule_Spec::not_applicable,
			),



			'test_attr_spec_rule_value_regex_casei_lower_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-playbuzz',
				'attribute_name' => 'data-comments',
				'attribute_value' => 'false',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_value_regex_casei',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_value_regex_casei_upper_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-playbuzz',
				'attribute_name' => 'data-comments',
				'attribute_value' => 'FALSE',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_value_regex_casei',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_value_regex_casei_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-playbuzz',
				'attribute_name' => 'data-comments',
				'attribute_value' => 'invalid',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_value_regex_casei',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_value_regex_casei_na' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-playbuzz',
				'attribute_name' => 'data-comments',
				'attribute_value' => 'invalid',
				'include_attr' => false,
				'include_attr_value' => false,
				'func_name' => 'test_attr_spec_rule_value_regex_casei',
				'expected' => AMP_Rule_Spec::not_applicable,
			),




			'test_attr_spec_rule_allowed_protocol_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'href',
				'attribute_value' => 'http://example.com',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_allowed_protocol',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_allowed_protocol_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'href',
				'attribute_value' => 'evil://example.com',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_allowed_protocol',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_allowed_protocol_na' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'href',
				'attribute_value' => 'invalid',
				'include_attr' => false,
				'include_attr_value' => false,
				'func_name' => 'test_attr_spec_rule_allowed_protocol',
				'expected' => AMP_Rule_Spec::not_applicable,
			),


			'test_attr_spec_rule_allowed_protocol_srcset_single_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-img',
				'attribute_name' => 'srcset',
				'attribute_value' => 'http://veryunique.com/img.jpg',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_allowed_protocol',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_allowed_protocol_srcset_multiple_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-img',
				'attribute_name' => 'srcset',
				'attribute_value' => 'http://example.com/img.jpg, https://example.com/whatever.jpg, image.jpg',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_allowed_protocol',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_allowed_protocol_srcset_single_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-img',
				'attribute_name' => 'srcset',
				'attribute_value' => 'bad://example.com/img.jpg',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_allowed_protocol',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_allowed_protocol_srcset_multiple_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-img',
				'attribute_name' => 'srcset',
				'attribute_value' => 'bad://example.com/img.jpg, evil://example.com/whatever.jpg',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_allowed_protocol',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_allowed_protocol_srcset_multiple_fail_good_first' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-img',
				'attribute_name' => 'srcset',
				'attribute_value' => 'https://example.com/img.jpg, evil://example.com/whatever.jpg',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_allowed_protocol',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_allowed_protocol_srcset_multiple_fail_bad_first' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-img',
				'attribute_name' => 'srcset',
				'attribute_value' => 'evil://example.com/img.jpg, https://example.com/whatever.jpg',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_allowed_protocol',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_allowed_protocol_srcset_na' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-img',
				'attribute_name' => 'srcset',
				'attribute_value' => 'invalid',
				'include_attr' => false,
				'include_attr_value' => false,
				'func_name' => 'test_attr_spec_rule_allowed_protocol',
				'expected' => AMP_Rule_Spec::not_applicable,
			),




			'test_attr_spec_rule_disallowed_relative_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-social-share',
				'attribute_name' => 'data-share-endpoint',
				'attribute_value' => 'http://example.com',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_disallowed_relative',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_disallowed_relative_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-social-share',
				'attribute_name' => 'data-share-endpoint',
				'attribute_value' => '//example.com',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_disallowed_relative',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_disallowed_relative_na' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-social-share',
				'attribute_name' => 'data-share-endpoint',
				'attribute_value' => 'invalid',
				'include_attr' => false,
				'include_attr_value' => false,
				'func_name' => 'test_attr_spec_rule_disallowed_relative',
				'expected' => AMP_Rule_Spec::not_applicable,
			),



			'test_attr_spec_rule_disallowed_empty_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-user-notification',
				'attribute_name' => 'data-dismiss-href',
				'attribute_value' => 'https://example.com',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_disallowed_empty',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_disallowed_empty_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-user-notification',
				'attribute_name' => 'data-dismiss-href',
				'attribute_value' => '',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_disallowed_empty',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_disallowed_empty_na' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-user-notification',
				'attribute_name' => 'data-dismiss-href',
				'attribute_value' => 'invalid',
				'include_attr' => false,
				'include_attr_value' => false,
				'func_name' => 'test_attr_spec_rule_disallowed_empty',
				'expected' => AMP_Rule_Spec::not_applicable,
			),



			'test_attr_spec_rule_disallowed_domain_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'form',
				'attribute_name' => 'action',
				'attribute_value' => 'https://example.com',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_disallowed_domain',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_disallowed_domain_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'form',
				'attribute_name' => 'action',
				'attribute_value' => '//cdn.ampproject.org',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_disallowed_domain',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_disallowed_domain_fail_2' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'form',
				'attribute_name' => 'action',
				'attribute_value' => 'https://cdn.ampproject.org',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_disallowed_domain',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_disallowed_domain_na' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'form',
				'attribute_name' => 'action',
				'attribute_value' => 'invalid',
				'include_attr' => false,
				'include_attr_value' => false,
				'func_name' => 'test_attr_spec_rule_disallowed_domain',
				'expected' => AMP_Rule_Spec::not_applicable,
			),




			'test_attr_spec_rule_blacklisted_value_regex_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'rel',
				'attribute_value' => 'whatever',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_blacklisted_value_regex',
				'expected' => AMP_Rule_Spec::pass,
			),
			'test_attr_spec_rule_blacklisted_value_regex_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'rel',
				'attribute_value' => 'components',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_blacklisted_value_regex',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_blacklisted_value_regex_fail_2' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'rel',
				'attribute_value' => 'import',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'test_attr_spec_rule_blacklisted_value_regex',
				'expected' => AMP_Rule_Spec::fail,
			),
			'test_attr_spec_rule_blacklisted_value_regex_na' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'a',
				'attribute_name' => 'rel',
				'attribute_value' => 'invalid',
				'include_attr' => false,
				'include_attr_value' => false,
				'func_name' => 'test_attr_spec_rule_blacklisted_value_regex',
				'expected' => AMP_Rule_Spec::not_applicable,
			),

		);


		foreach ( $test_data as $test_name => $test ) {
			if ( isset( $test['include_attr_value'] ) && $test['include_attr_value'] ) {
				$attr_value = '="' . $test['attribute_value'] . '"';
			} else {
				$attr_value = '';
			}
			if ( isset( $test['use_alternate_name'] ) && $test['use_alternate_name'] && $test['include_attr'] ) {
				$attribute = $test['use_alternate_name'] . $attr_value;
			} elseif ( isset( $test['include_attr'] ) && $test['include_attr'] ) {
				$attribute = $test['attribute_name'] . $attr_value;
			} else {
				$attribute = '';
			}
			$source = '<' . $test['tag_name'] . ' ' . $attribute . '>Some test content</' . $test['tag_name'] . '>';

			$attr_spec_list = $this->allowed_tags[ $test['tag_name'] ][$test['rule_spec_index']]['attr_spec_list'];
			foreach( $attr_spec_list as $attr_name => $attr_val ) {
				if ( isset( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::alternative_names] ) ) {
					foreach( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::alternative_names] as $attr_alt_name ) {
						$attr_spec_list[ $attr_alt_name ] = $attr_spec_list[ $attr_name ];
					}
				}
			}

			$attr_spec_rule = $attr_spec_list[ $test['attribute_name'] ];

			$dom = AMP_DOM_Utils::get_dom_from_content( $source );
			$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
			$node = $dom->getElementsByTagName( $test['tag_name'] )->item( 0 );

			$got = $this->invokeMethod( $sanitizer, $test['func_name'], array( $node, $test['attribute_name'], $attr_spec_rule ) );

			if ( $test['expected'] != $got ) {
				printf( PHP_EOL . '%s failed:' . PHP_EOL, $test_name );
				printf( $source . PHP_EOL );
				printf( 'expected: %s' . PHP_EOL, $test['expected'] );
				printf( 'got: %s' . PHP_EOL, $got );
				var_dump( $test );
			}

			$this->assertEquals( $test['expected'], $got );
		}
	}

	public function test_is_allowed_attribute() {

		$test_data = array(
			'test_is_amp_allowed_attribute_whitelisted_regex_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-social-share',
				'attribute_name' => 'data-whatever-else-you-want',
				'attribute_value' => 'whatever',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'is_amp_allowed_attribute',
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_global_attribute_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-social-share',
				'attribute_name' => 'itemid',
				'attribute_value' => 'whatever',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'is_amp_allowed_attribute',
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_tag_spec_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-social-share',
				'attribute_name' => 'media',
				'attribute_value' => 'whatever',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'is_amp_allowed_attribute',
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_disallowed_attr_fail' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amp-social-share',
				'attribute_name' => 'bad-attr',
				'attribute_value' => 'whatever',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'is_amp_allowed_attribute',
				'expected' => false,
			),

			'test_is_amp_allowed_attribute_layout_height_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amd-ad',
				'attribute_name' => 'height',
				'attribute_value' => 'not_tested',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'is_amp_allowed_attribute',
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_layout_heights_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amd-ad',
				'attribute_name' => 'heights',
				'attribute_value' => 'not_tested',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'is_amp_allowed_attribute',
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_layout_width_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amd-ad',
				'attribute_name' => 'width',
				'attribute_value' => 'not_tested',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'is_amp_allowed_attribute',
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_layout_sizes_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amd-ad',
				'attribute_name' => 'sizes',
				'attribute_value' => 'not_tested',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'is_amp_allowed_attribute',
				'expected' => true,
			),
			'test_is_amp_allowed_attribute_layout_layout_pass' => array(
				'rule_spec_index' => 0,
				'tag_name' => 'amd-ad',
				'attribute_name' => 'layout',
				'attribute_value' => 'not_tested',
				'include_attr' => true,
				'include_attr_value' => true,
				'func_name' => 'is_amp_allowed_attribute',
				'expected' => true,
			),
		);

		foreach ( $test_data as $test_name => $test ) {
			if ( isset( $test['include_attr_value'] ) && $test['include_attr_value'] ) {
				$attr_value = '="' . $test['attribute_value'] . '"';
			} else {
				$attr_value = '';
			}
			if ( isset( $test['use_alternate_name'] ) && $test['use_alternate_name'] && $test['include_attr'] ) {
				$attribute = $test['use_alternate_name'] . $attr_value;
			} elseif ( isset( $test['include_attr'] ) && $test['include_attr'] ) {
				$attribute = $test['attribute_name'] . $attr_value;
			} else {
				$attribute = '';
			}
			$source = '<' . $test['tag_name'] . ' ' . $attribute . '>Some test content</' . $test['tag_name'] . '>';

			$attr_spec_list = $this->allowed_tags[ $test['tag_name'] ][$test['rule_spec_index']]['attr_spec_list'];
			foreach( $attr_spec_list as $attr_name => $attr_val ) {
				if ( isset( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::alternative_names] ) ) {
					foreach( $attr_spec_list[ $attr_name ][AMP_Rule_Spec::alternative_names] as $attr_alt_name ) {
						$attr_spec_list[ $attr_alt_name ] = $attr_spec_list[ $attr_name ];
					}
				}
			}

			$dom = AMP_DOM_Utils::get_dom_from_content( $source );
			$sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
			$node = $dom->getElementsByTagName( $test['tag_name'] )->item( 0 );

			$got = $this->invokeMethod( $sanitizer, $test['func_name'], array( $test['attribute_name'], $attr_spec_list ) );

			if ( $test['expected'] != $got ) {
				printf( PHP_EOL . '%s failed:' . PHP_EOL, $test_name );
				printf( $source . PHP_EOL );
				printf( 'expected: %s' . PHP_EOL, $test['expected'] );
				printf( 'got: %s' . PHP_EOL, $got );
				var_dump( $test );
			}

			$this->assertEquals( $test['expected'], $got );
		}
	}

	// Use this to call private methods
	public function invokeMethod(&$object, $methodName, array $parameters = array()) {
	    $reflection = new \ReflectionClass(get_class($object));
	    $method = $reflection->getMethod($methodName);
	    $method->setAccessible(true);

	    return $method->invokeArgs($object, $parameters);
	}

}
