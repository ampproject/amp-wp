<?php

/**
 * Autoload the classes used by the AMP plugin.
 *
 * Class AMP_Autoloader
 */
class AMP_Autoloader {

	/**
	 * Map of Classname to relative filepath sans extension.
	 *
	 * @example Format:
	 *
	 *  array(
	 *      'Class_Name1' =>  'subdir-of-includes/filename1',
	 *      'Class_Name2' =>  '2nd-subdir-of-includes/filename2',
	 *      'Class_Name3' =>  '!/full/path/to/filename3.php',
	 *  );
	 *
	 * @var string[]
	 */
	private $_classmap;

	/**
	 * Provide access to the singlton instance
	 *
	 * @var AMP_Autoloader
	 */
	private static $_instance;

	/**
	 * Perform the autoload.
	 *
	 * Run as few lines of code as possible.
	 *
	 * If $class_name has first char as '!' strip it and assume a full path w/extension.
	 * Otherwise assume subdir or /includes/ w/o extension.
	 *
	 * @param string $class_name
	 */
	function _autoload( $class_name ) {
		if ( ! isset( $this->_classmap[ $class_name ] ) ) {
			return;
		}
		$filepath = $this->_classmap[ $class_name ];
		require( '!' !== $filepath[ 0 ] ? __DIR__ . "/includes/{$filepath}.php" : substr( $filepath, 1 ) );
	}

	/**
	 * Trigger creation of AMP_Autoloader singleton instance and return it.
	 *
	 * @return AMP_Autoloader
	 */
	public static function instance() {
		if ( ! isset( static::$_instance ) ) {
			static::$_instance = new static();
		}
		return static::$_instance;
	}

	/**
	 * Registers to autoloader to PHP.
	 *
	 * Called at the end of this file; calling a second time has no effect.
	 */
	public static function register() {
		static $registered = false;
		if ( ! $registered ) {
			spl_autoload_register( [ static::instance(), '_autoload' ] );
			$registered = true;
		}
	}

	/**
	 * Allows an extensions plugin to register a class and its file for autoloading
	 *
	 * @param string $class_name Full classname (include namespace if applicable)
	 * @param string $filepath Absolute filepath to class
	 */
	public static function register_autoload_class( $class_name, $filepath ) {
		self::$_instance->_classmap[ $class_name ] = '!' . $filepath;
	}

	/**
	 * Creates instance and initializes classmap.
	 *
	 * AMP_Autoloader constructor.
	 */
	private function __construct() {
		$this->_classmap = array(
			'AMP_Actions'                                                    => 'actions/class-amp-actions',
			'AMP_Frontend_Actions'                                           => 'actions/class-amp-frontend-actions',
			'AMP_Paired_Post_Actions'                                        => 'actions/class-amp-paired-post-actions',
			'AMP_Template_Customizer'                                        => 'admin/class-amp-customizer',
			'AMP_Post_Meta_Box'                                              => 'admin/class-amp-post-meta-box',
			'AMP_Post_Type_Support'                                          => 'class-amp-post-type-support',
			'AMP_Base_Embed_Handler'                                         => 'embeds/class-amp-base-embed-handler',
			'AMP_DailyMotion_Embed_Handler'                                  => 'embeds/class-amp-dailymotion-embed',
			'AMP_Facebook_Embed_Handler'                                     => 'embeds/class-amp-facebook-embed',
			'AMP_Gallery_Embed_Handler'                                      => 'embeds/class-amp-gallery-embed',
			'AMP_Instagram_Embed_Handler'                                    => 'embeds/class-amp-instagram-embed',
			'AMP_Pinterest_Embed_Handler'                                    => 'embeds/class-amp-pinterest-embed',
			'AMP_SoundCloud_Embed_Handler'                                   => 'embeds/class-amp-soundcloud-embed',
			'AMP_Twitter_Embed_Handler'                                      => 'embeds/class-amp-twitter-embed',
			'AMP_Vimeo_Embed_Handler'                                        => 'embeds/class-amp-vimeo-embed',
			'AMP_Vine_Embed_Handler'                                         => 'embeds/class-amp-vine-embed',
			'AMP_YouTube_Embed_Handler'                                      => 'embeds/class-amp-youtube-embed',
			'FastImage'                                                      => '/lib/fastimage/class-fastimage.php',
			'WillWashburn\\Stream\\Exception\\StreamBufferTooSmallException' => 'lib/fasterimage/Stream/Exception/StreamBufferTooSmallException',
			'WillWashburn\\Stream\\StreamableInterface'                      => 'lib/fasterimage/Stream/StreamableInterface',
			'WillWashburn\\Stream\\Stream'                                   => 'lib/fasterimage/Stream/Stream',
			'FasterImage\\Exception\\InvalidImageException'                  => 'lib/fasterimage/Exception/InvalidImageException',
			'FasterImage\\ExifParser'                                        => 'lib/fasterimage/ExifParser',
			'FasterImage\\ImageParser'                                       => 'lib/fasterimage/ImageParser',
			'FasterImage\\FasterImage'                                       => 'lib/fasterimage/FasterImage',
			'AMP_Analytics_Options_Submenu'                                  => 'options/class-amp-analytics-options-submenu',
			'AMP_Options_Menu'                                               => 'options/class-amp-options-menu',
			'AMP_Options_Manager'                                            => 'options/class-amp-options-manager',
			'AMP_Analytics_Options_Submenu_Page'                             => 'options/views/class-amp-analytics-options-submenu-page',
			'AMP_Options_Menu_Page'                                          => 'options/views/class-amp-options-menu-page',
			'AMP_Allowed_Tags_Generated'                                     => 'sanitizers/class-amp-allowed-tags-generated',
			'AMP_Audio_Sanitizer'                                            => 'sanitizers/class-amp-audio-sanitizer',
			'AMP_Base_Sanitizer'                                             => 'sanitizers/class-amp-base-sanitizer',
			'AMP_Blacklist_Sanitizer'                                        => 'sanitizers/class-amp-blacklist-sanitizer',
			'AMP_Iframe_Sanitizer'                                           => 'sanitizers/class-amp-iframe-sanitizer',
			'AMP_Img_Sanitizer'                                              => 'sanitizers/class-amp-img-sanitizer',
			'AMP_Playbuzz_Sanitizer'                                         => 'sanitizers/class-amp-playbuzz-sanitizer',
			'AMP_Style_Sanitizer'                                            => 'sanitizers/class-amp-style-sanitizer',
			'AMP_Tag_And_Attribute_Sanitizer'                                => 'sanitizers/class-amp-tag-and-attribute-sanitizer',
			'AMP_Video_Sanitizer'                                            => 'sanitizers/class-amp-video-sanitizer',
			'AMP_Customizer_Design_Settings'                                 => 'settings/class-amp-customizer-design-settings',
			'AMP_Customizer_Settings'                                        => 'settings/class-amp-customizer-settings',
			'AMP_Content'                                                    => 'templates/class-amp-content',
			'AMP_Content_Sanitizer'                                          => 'templates/class-amp-content-sanitizer',
			'AMP_Post_Template'                                              => 'templates/class-amp-post-template',
			'AMP_DOM_Utils'                                                  => 'utils/class-amp-dom-utils',
			'AMP_HTML_Utils'                                                 => 'utils/class-amp-html-utils',
			'AMP_Image_Dimension_Extractor'                                  => 'utils/class-amp-image-dimension-extractor',
			'AMP_String_Utils'                                               => 'utils/class-amp-string-utils',
			'AMP_WP_Utils'                                                   => 'utils/class-amp-wp-utils',
			'WPCOM_AMP_Polldaddy_Embed'                                      => 'wpcom/class-amp-polldaddy-embed.php',
		);
	}
}

/**
 * Call method to register this autoloader with PHP.
 */
AMP_Autoloader::register();



