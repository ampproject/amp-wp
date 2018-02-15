<?php
/**
 * Class AMP_Playlist_Embed_Handler
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Class AMP_Playlist_Embed_Handler
 *
 * Creates AMP-compatible markup for the WordPress 'playlist' shortcode.
 *
 * @package AMP
 */
class AMP_Playlist_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * The tag of the shortcode.
	 *
	 * @var string.
	 */
	const SHORTCODE = 'playlist';

	/**
	 * The max width of the audio thumbnail image.
	 *
	 * This corresponds to the max-width in wp-mediaelement.css:
	 * .wp-playlist .wp-playlist-current-item img
	 *
	 * @var string.
	 */
	const THUMB_MAX_WIDTH = 60;

	/**
	 * The height of the carousel.
	 *
	 * @var string.
	 */
	const CAROUSEL_HEIGHT = 160;

	/**
	 * The pattern to get the playlist data.
	 *
	 * @var string.
	 */
	const PLAYLIST_REGEX = '/(?s)\<script [^>]* class="wp-playlist-script"\>[^<]*?(.*).*?\<\/script\>/';

	/**
	 * The ID of individual playlist.
	 *
	 * @var int
	 */
	public static $playlist_id = 0;

	/**
	 * The parsed data for the playlist.
	 *
	 * @var array
	 */
	public $data;

	/**
	 * Registers the playlist shortcode.
	 */
	public function register_embed() {
		add_shortcode( self::SHORTCODE, array( $this, 'shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'styling' ) );
	}

	/**
	 * Unregisters the playlist shortcode.
	 *
	 * @return void.
	 */
	public function unregister_embed() {
		remove_shortcode( self::SHORTCODE );
	}

	/**
	 * Enqueues the playlist styling.
	 *
	 * @return void.
	 */
	public function styling() {
		global $post;
		if ( ! isset( $post->post_content ) || ! has_shortcode( $post->post_content, self::SHORTCODE ) ) {
			return;
		}

		wp_enqueue_style( 'wp-mediaelement' );
		wp_enqueue_style(
			'amp-playlist-shortcode',
			amp_get_asset_url( 'css/amp-playlist-shortcode.css' ),
			array(),
			AMP__VERSION
		);
	}

	/**
	 * Gets AMP-compliant markup for the playlist shortcode.
	 *
	 * Uses the JSON that wp_playlist_shortcode() produces.
	 * Gets the markup, based on the type of playlist.
	 *
	 * @param array $attr The playlist attributes.
	 * @return string Playlist shortcode markup.
	 */
	public function shortcode( $attr ) {
		$this->data = $this->get_data( $attr );
		if ( isset( $this->data['type'] ) && ( 'audio' === $this->data['type'] ) ) {
			return $this->audio_playlist();
		} elseif ( isset( $this->data['type'] ) && ( 'video' === $this->data['type'] ) ) {
			return $this->video_playlist();
		}
	}

	/**
	 * Gets an AMP-compliant audio playlist.
	 *
	 * @return string Playlist shortcode markup, or an empty string.
	 */
	public function audio_playlist() {
		if ( ! isset( $this->data['tracks'] ) ) {
			return '';
		}
		$container_id   = 'ampPlaylistCarousel' . self::$playlist_id;
		$selected_slide = $container_id . '.selectedSlide';
		self::$playlist_id++;

		ob_start();
		?>
		<div class="wp-playlist wp-audio-playlist wp-playlist-light">
			<amp-carousel id="<?php echo esc_attr( $container_id ); ?>" [slide]="<?php echo esc_attr( $selected_slide ); ?>" height="<?php echo esc_attr( self::CAROUSEL_HEIGHT ); ?>" width="auto" type="slides">
				<?php
				foreach ( $this->data['tracks'] as $track ) :
					$title            = $this->get_title( $track );
					$image_url        = isset( $track['thumb']['src'] ) ? esc_url( $track['thumb']['src'] ) : '';
					$thumb_dimensions = $this->get_thumb_dimensions( $track );
					$image_height     = isset( $thumb_dimensions['height'] ) ? $thumb_dimensions['height'] : '';
					$image_width      = isset( $thumb_dimensions['width'] ) ? $thumb_dimensions['width'] : '';

					?>
					<div>
						<div class="wp-playlist-current-item">
							<amp-img src="<?php echo esc_url( $image_url ); ?>" height="<?php echo esc_attr( $image_height ); ?>" width="<?php echo esc_attr( $image_width ); ?>"></amp-img>
							<div class="wp-playlist-caption">
								<span class="wp-playlist-item-meta wp-playlist-item-title"><?php echo esc_html( $title ); ?></span>
							</div>
						</div>
						<amp-audio width="auto" height="50" src="<?php echo esc_url( isset( $track['src'] ) ? $track['src'] : '' ); ?>"></amp-audio>
					</div>
				<?php endforeach; ?>
			</amp-carousel>
			<?php $this->tracks( 'audio', $container_id ); ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Gets an AMP-compliant video playlist.
	 *
	 * This uses similar markup to the native playlist shortcode output.
	 * So the styles from wp-mediaelement.min.css will apply to it.
	 *
	 * @global int content_width.
	 * @return string $video_playlist Markup for the video playlist.
	 */
	public function video_playlist() {
		global $content_width;
		if ( ! isset( $this->data['tracks'], $this->data['tracks'][0]['src'] ) ) {
			return;
		}
		$playlist = 'playlist' . self::$playlist_id;
		self::$playlist_id++;

		$amp_state = array(
			'currentVideo' => '0',
		);
		foreach ( $this->data['tracks'] as $index => $track ) {
			$amp_state[ $index ] = array(
				'videoUrl' => isset( $track['src'] ) ? $track['src'] : '',
				'thumb'    => isset( $track['thumb']['src'] ) ? $track['thumb']['src'] : '',
			);
		}

		$dimensions = isset( $this->data['tracks'][0]['dimensions']['resized'] ) ? $this->data['tracks'][0]['dimensions']['resized'] : null;
		$width      = isset( $dimensions['width'] ) ? $dimensions['width'] : $content_width;
		$height     = isset( $dimensions['height'] ) ? $dimensions['height'] : null;

		ob_start();
		?>
		<div class="wp-playlist wp-video-playlist wp-playlist-light">
			<amp-state id="<?php echo esc_attr( $playlist ); ?>">
				<script type="application/json">
					<?php echo wp_unslash( wp_json_encode( $amp_state ) ); // WPCS: XSS ok. ?>
				</script>
			</amp-state>
			<amp-video id="amp-video" src="<?php echo esc_url( $this->data['tracks'][0]['src'] ); ?>" [src]="<?php echo esc_attr( $playlist ); ?>[<?php echo esc_attr( $playlist ); ?>.currentVideo].videoUrl" width="<?php echo esc_attr( $width ); ?>" height="<?php echo esc_attr( isset( $height ) ? $height : '' ); ?>" controls></amp-video>
			<?php $this->tracks( 'video', $playlist ); ?>
		</div>
		<?php
		return ob_get_clean(); // WPCS: XSS ok.
	}

	/**
	 * Gets the thumbnail image dimensions, including height and width.
	 *
	 * If the width is higher than the maximum width,
	 * reduces it to the maximum width.
	 * And it proportionally reduces the height.
	 *
	 * @param array $track The data for the track.
	 * @return array $dimensions The height and width of the thumbnail image.
	 */
	public function get_thumb_dimensions( $track ) {
		if ( ! isset( $track['thumb']['width'], $track['thumb']['height'] ) ) {
			return;
		}
		$original_width  = intval( $track['thumb']['width'] );
		$original_height = intval( $track['thumb']['height'] );
		$image_width     = min( self::THUMB_MAX_WIDTH, $original_width );
		if ( $original_width > self::THUMB_MAX_WIDTH ) {
			$ratio        = $original_width / self::THUMB_MAX_WIDTH;
			$image_height = floor( $original_height / $ratio );
		} else {
			$image_height = $original_height;
		}
		return array(
			'height' => $image_height,
			'width'  => $image_width,
		);
	}

	/**
	 * Outputs the playlist tracks, based on the type of playlist.
	 *
	 * These typically appear below the player.
	 * Clicking a track triggers the player to appear with its src.
	 *
	 * @param string $type         The type of tracks: 'audio' or 'video'.
	 * @param string $container_id The ID of the container.
	 * @return void.
	 */
	public function tracks( $type, $container_id ) {
		?>
		<div class="wp-playlist-tracks">
			<?php
			$i = 0;
			foreach ( $this->data['tracks'] as $index => $track ) {
				$title = $this->get_title( $track );
				if ( 'audio' === $type ) {
					$on         = 'tap:AMP.setState(' . wp_json_encode( array( $container_id => array( 'selectedSlide' => $i ) ) ) . ')';
					$item_class = esc_attr( $i ) . ' == ' . esc_attr( $container_id ) . '.selectedSlide ? "wp-playlist-item wp-playlist-playing" : "wp-playlist-item"';
				} elseif ( 'video' === $type ) {
					$on         = 'tap:AMP.setState(' . wp_json_encode( array( $container_id => array( 'currentVideo' => $index ) ) ) . ')';
					$item_class = esc_attr( $index ) . ' == ' . esc_attr( $container_id ) . '.currentVideo ? "wp-playlist-item wp-playlist-playing" : "wp-playlist-item"';
				}

				?>
				<div class="wp-playlist-item" [class]="<?php echo esc_attr( isset( $item_class ) ? $item_class : '' ); ?>">
					<a class="wp-playlist-caption" on="<?php echo esc_attr( isset( $on ) ? $on : '' ); ?>">
						<?php echo esc_html( strval( $i + 1 ) . '.' ); ?> <span class="wp-playlist-item-title"><?php echo esc_html( $title ); ?></span>
					</a>
					<?php if ( isset( $track['meta']['length_formatted'] ) ) : ?>
						<div class="wp-playlist-item-length"><?php echo esc_html( $track['meta']['length_formatted'] ); ?></div>
					<?php endif; ?>
				</div>
				<?php
				$i++;
			}
		?>
		</div>
		<?php
	}

	/**
	 * Gets the data for the playlist.
	 *
	 * @param array $attr The shortcode attributes.
	 * @return array $data The data for the playlist.
	 */
	public function get_data( $attr ) {
		$markup = wp_playlist_shortcode( $attr );
		preg_match( self::PLAYLIST_REGEX, $markup, $matches );
		if ( empty( $matches[1] ) ) {
			return;
		}
		return json_decode( $matches[1], true );
	}

	/**
	 * Gets the title for the track.
	 *
	 * @param array $track The track data.
	 * @return string $title The title of the track.
	 */
	public function get_title( $track ) {
		if ( ! empty( $track['caption'] ) ) {
			return $track['caption'];
		} elseif ( ! empty( $track['title'] ) ) {
			return $track['title'];
		}
	}

}
