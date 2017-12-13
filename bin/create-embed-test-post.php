<?php
/**
 * Create embed test post.
 *
 * @package AMP
 */

if ( ! defined( 'WP_CLI' ) ) {
	echo "Must be run in WP-CLI via: wp eval-file bin/create-embed-test-post.php\n";
	exit( 1 );
}

$data_entries = array(
	array(
		'prepare' => 'amp_test_prepare_image_attachments',
		'heading' => 'Media Gallery',
		'content' => function( $data ) {
			return sprintf( '[gallery ids="%s"]', implode( ',', $data['ids'] ) );
		},
	),
	array(
		'heading' => 'Media Image',
		'content' => amp_image_markup(),
	),
	array(
		'heading' => 'Media Caption',
		'content' => '[caption width=150]' . amp_image_markup() . 'Example image caption[/caption]',
	),
	array(
		'heading' => 'Media Video',
		'content' => '[video src=https://videos.files.wordpress.com/DK5mLrbr/video-ca6dc0ab4a_hd.mp4]',
	),
	array(
		'heading' => 'Media Audio',
		'content' => '[audio src=https://wptavern.com/wp-content/uploads/2017/11/EPISODE-296-Gutenberg-Telemetry-Calypso-and-More-With-Matt-Mullenweg.mp3]',
	),
	array(
		'prepare' => 'amp_test_prepare_video_attachments',
		'heading' => 'Video Playlist',
		'content' => function( $data ) {
			return isset( $data['ids'] ) ? sprintf( '[playlist type="video" ids="%s"]', implode( ',', $data['ids'] ) ) : 'There are no videos, so no playlist is expected';
		},
	),
	array(
		'prepare' => 'amp_test_prepare_audio_attachments',
		'heading' => 'Audio Playlist',
		'content' => function( $data ) {
			return isset( $data['ids'] ) ? sprintf( '[playlist ids="%s"]', implode( ',', $data['ids'] ) ) : 'There audio files, so no playlist is expected';
		},
	),
	array(
		'heading' => 'CloudUp Image Embed',
		'content' => 'https://cloudup.com/iWn3EIpgjev',
	),
	array(
		'heading' => 'CloudUp Video Embed',
		'content' => 'https://cloudup.com/ioyW8a_Tjme',
	),
	array(
		'heading' => 'CloudUp Gallery Embed',
		'content' => 'https://cloudup.com/cQNdYtQLO5U',
	),
	array(
		'heading' => 'CollegeHumor Embed',
		'content' => 'http://www.collegehumor.com/video/40002823/how-to-actually-finish-something-for-once',
	),
	array(
		'heading' => 'DailyMotion Embed',
		'content' => 'http://www.dailymotion.com/embed/video/x6bacgf',
	),
	array(
		'heading' => 'Facebook Post Embed',
		'content' => 'https://www.facebook.com/WordPress/posts/10155651799877911',
	),
	array(
		'heading' => 'Facebook Video Embed',
		'content' => 'https://www.facebook.com/WordPress/videos/10154702401472911/',
	),
	array(
		'heading' => 'Flickr Image Embed',
		'content' => 'https://www.flickr.com/photos/sylvainmessier/38089894895/in/explore-2017-12-11/',
	),
	array(
		'heading' => 'Flickr Video Embed',
		'content' => 'https://flic.kr/p/5TPDWa',
	),
	array(
		'heading' => 'Funny Or Die Video Embed',
		'content' => 'http://FunnyOrDie.com/m/b1dn',
	),
	array(
		'heading' => 'Hulu Embed',
		'content' => 'https://www.hulu.com/watch/807443',
	),
	array(
		'heading' => 'Imgur Embed',
		'content' => '<blockquote class="imgur-embed-pub" lang="en" data-id="HNQ2WRt"><a href="//imgur.com/HNQ2WRt">Takeoff</a></blockquote>',
	),
	array(
		'heading' => 'Instagram Embed',
		'content' => 'https://www.instagram.com/p/bNd86MSFv6/',
	),
	array(
		'heading' => 'Isuu Embed',
		'content' => 'https://issuu.com/ajcwfu/docs/seatatthetablefinal',
	),
	array(
		'heading' => 'Kickstarter Embed',
		'content' => 'https://www.kickstarter.com/projects/iananderson/save-froots-magazine',
	),
	array(
		'heading' => 'Meetup Embed',
		'content' => 'https://www.meetup.com/WordPress-Mexico',
	),
	array(
		'heading' => 'Mixcloud Embed',
		'content' => 'https://www.mixcloud.com/TheWireMagazine/adventures-in-sound-and-music-hosted-by-derek-walmsley-7-december-2017/',
	),
	array(
		'heading' => 'Photobucket Embed',
		'content' => 'http://s1284.photobucket.com/user/adonchin/media/20171116_181841_zpsrjuop6u7.jpg.html',
	),
	array(
		'heading' => 'Polldaddy Embed',
		'content' => 'https://polldaddy.com/poll/7012505/',
	),
	array(
		'heading' => 'Reddit Embed',
		'content' => 'https://www.reddit.com/r/Android/comments/7jbkub/google_maps_will_soon_tell_you_when_its_time_to/?ref=share&ref_source=link',
	),
	array(
		'heading' => 'Reverb Nation Embed',
		'content' => 'https://www.reverbnation.com/fernandotorresleiva/song/28755694-breve-amor-new-version',
	),
	array(
		'heading' => 'Scribd Embed',
		'content' => '[scribd id=124550852 key=key-1hh4hsgsvzz8jdl28w3v mode=scroll]',
	),
	array(
		'heading' => 'SlideShare Embed',
		'content' => 'https://www.slideshare.net/slideshow/embed_code/key/u6WNbsR5worSzC',
	),
	array(
		'heading' => 'SmugMug Embed',
		'content' => 'https://stuckincustoms.smugmug.com/Portfolio/i-GnwtS8R/A',
	),
	array(
		'heading' => 'Soundcloud Embed',
		'content' => 'https://soundcloud.com/jack-villano-villano/mozart-requiem-in-d-minor',
	),
	array(
		'heading' => 'Speaker Deck Embed',
		'content' => 'https://speakerdeck.com/caitiem20/distributed-sagas-a-protocol-for-coordinating-microservices',
	),
	array(
		'heading' => 'Spotify Embed',
		'content' => 'https://open.spotify.com/track/2XULDEvijLgHttFgLzzpM5',
	),
	array(
		'heading' => 'Ted Embed',
		'content' => 'https://www.ted.com/talks/derek_sivers_how_to_start_a_movement',
	),
	array(
		'heading' => 'Tumblr Post Embed',
		'content' => 'https://embed.tumblr.com/embed/post/CHB7nLkCLl-ODZ7tdPU9SQ/168290317795',
	),
	array(
		'heading' => 'Twitter Embed',
		'content' => 'https://twitter.com/WordPress/status/936550699336437760',
	),
	array(
		'heading' => 'VideoPress Embed',
		'content' => 'https://videopress.com/v/kUJmAcSf',
	),
	array(
		'heading' => 'Vimeo Embed',
		'content' => 'https://vimeo.com/59172123',
	),
	array(
		'heading' => 'Vine Embed',
		'content' => '[vine url="https://vine.co/v/bEIHZpD2JWz"]',
	),
	array(
		'heading' => 'WordPress Plugin Directory Embed',
		'content' => 'https://wordpress.org/plugins/amp/',
	),
	array(
		'heading' => 'WordPress TV Embed',
		'content' => 'https://videopress.com/v/DK5mLrbr',
	),
	array(
		'heading' => 'YouTube Embed',
		'content' => 'https://www.youtube.com/watch?v=XOY3ZUO6P0k',
	),
);

/**
 * Prepare test by ensuring attachments exist.
 *
 * @param array $data Entry data.
 * @return array Data.
 */
function amp_test_prepare_image_attachments( $data ) {
	$attachments = get_children( array(
		'post_parent' => 0,
		'post_status' => 'inherit',
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
	) );
	$data['ids'] = wp_list_pluck( $attachments, 'ID' );

	$image_count_needed = 5;
	if ( count( $data['ids'] ) < $image_count_needed ) {
		$data['ids'] = wp_list_pluck( amp_get_media_items( 'image', $image_count_needed ), 'ID' );
	}
	return $data;
}

/**
 * Get the markup for an image.
 *
 * @return string $image Markup for the image.
 */
function amp_image_markup() {
	$image_posts = amp_get_media_items( 'image', 1 );
	$image       = reset( $image_posts );
	return isset( $image->ID ) ? wp_get_attachment_image( $image->ID ) : null;
}

/**
 * Get video IDs to test a video playlist.
 *
 * @param array $data Entry data.
 * @return array $data Entry data, with video IDs.
 */
function amp_test_prepare_video_attachments( $data ) {
	$data['ids'] = wp_list_pluck( amp_get_media_items( 'video' ), 'ID' );
	return $data;
}

/**
 * Get audio IDs to test an audio playlist.
 *
 * @param array $data Entry data.
 * @return array $data Data, with video IDs.
 */
function amp_test_prepare_audio_attachments( $data ) {
	$data['ids'] = wp_list_pluck( amp_get_media_items( 'audio' ), 'ID' );
	return $data;
}

/**
 * Get media items, using a \WP_Query.
 *
 * @param integer $type The post_mime_type of the media item.
 * @param integer $image_count The number of images for which to query.
 * @return array $media_items The media items from the query.
 */
function amp_get_media_items( $type, $image_count = 10 ) {
	$query = new \WP_Query( array(
		'post_type'      => 'attachment',
		'post_mime_type' => $type,
		'post_status'    => 'inherit',
		'posts_per_page' => $image_count,
	) );
	return $query->get_posts();
}

// Run the script.
$page = get_page_by_path( '/amp-test-embeds/' );
if ( $page ) {
	$page_id = $page->ID;
} else {
	$page_id = wp_insert_post( array(
		'post_name' => 'amp-test-embeds',
		'post_title' => 'AMP Test Embeds',
		'post_type' => 'page',
	) );
}

$content = '';
foreach ( $data_entries as $data_entry ) {
	if ( isset( $data_entry['prepare'] ) ) {
		$data_entry = array_merge(
			$data_entry,
			call_user_func( $data_entry['prepare'], $data_entry )
		);
	}

	$content .= sprintf( "<h1>%s</h1>\n", $data_entry['heading'] );
	if ( is_callable( $data_entry['content'] ) ) {
		$content .= call_user_func( $data_entry['content'], $data_entry );
	} else {
		$content .= $data_entry['content'];
	}
	$content .= "\n\n";
}

wp_update_post( wp_slash( array(
	'ID' => $page_id,
	'post_content' => $content,
) ) );

WP_CLI::success( sprintf( 'Please take a look at: %s', get_permalink( $page_id ) ) );
