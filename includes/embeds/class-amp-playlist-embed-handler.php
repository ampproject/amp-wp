<?php
/**
 * Class AMP_Playlist_Embed_Handler
 *
 * @package AMP
 * @since 0.7
 */

/**
 * Class AMP_Playlist_Embed_Handler
 */
class AMP_Playlist_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * The ID of the individual playlist.
	 *
	 * @var int
	 */
	public static $playlist_id = 0;

	/**
	 * Registers the playlist shortcode.
	 */
	public function register_embed() {
		add_shortcode( 'playlist', array( $this, 'shortcode' ) );
	}

	/**
	 * Unregisters the playlist shortcode.
	 *
	 * @return void.
	 */
	public function unregister_embed() {
		remove_shortcode( 'playlist' );
	}

	/**
	 * Outputs an AMP-compliant playlist shortcode.
	 *
	 * Uses the JSON that wp_playlist_shortcode() produces.
	 * But outputs an <amp-video>, and an <amp-state> to change the current video.
	 *
	 * @global content_width.
	 * @param array $attr The playlist attributes.
	 * @return string Playlist shortcode markup.
	 */
	public function shortcode( $attr ) {
		global $content_width;

		$markup = wp_playlist_shortcode( $attr );
		preg_match( '/(?s)\<script [^>]* class="wp-playlist-script"\>[^<]*?(.*).*?\<\/script\>/', $markup, $matches );
		if ( empty( $matches[1] ) ) {
			return;
		}
		$data = json_decode( $matches[1], true );
		if ( ! isset( $data['tracks'], $data['tracks'][0]['src'] ) ) {
			return;
		}

		$amp_state = array(
			'currentVideo' => '0',
		);
		foreach ( $data['tracks'] as $index => $track ) {
			$amp_state[ $index ] = array(
				'videoUrl' => isset( $track['src'] ) ? $track['src'] : '',
				'thumb'    => isset( $track['thumb']['src'] ) ? $track['thumb']['src'] : '',
			);
		}
		$playlist   = 'playlist' . self::$playlist_id++;
		$dimensions = isset( $data['tracks'][0]['dimensions']['resized'] ) ? $data['tracks'][0]['dimensions']['resized'] : null;
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
			<amp-video id="amp-video" src="<?php echo esc_url( $data['tracks'][0]['src'] ); ?>" [src]="<?php echo esc_attr( $playlist ); ?>[<?php echo esc_attr( $playlist ); ?>.currentVideo].videoUrl" width="<?php echo esc_attr( $width ); ?>" height="<?php echo isset( $height ) ? esc_attr( $height ) : ''; ?>" controls></amp-video>
			<div class="wp-playlist-tracks">
				<?php
				$i = 1;
				foreach ( $data['tracks'] as $index => $track ) {
					if ( ! empty( $track['caption'] ) ) {
						$title = $track['caption'];
					} elseif ( ! empty( $track['title'] ) ) {
						$title = $track['title'];
					}
					?>
					<div class="wp-playlist-item">
						<a class="wp-playlist-caption" on="tap:AMP.setState({<?php echo esc_attr( $playlist ); ?>: {currentVideo: <?php echo esc_attr( $index ); ?>}})">
							<?php echo esc_html( $i . '.' ); ?> <span class="wp-playlist-item-title"><?php echo isset( $title ) ? esc_html( $title ) : ''; ?></span>
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
		</div>
		<?php
		return ob_get_clean(); // WPCS: XSS ok.
	}

}

