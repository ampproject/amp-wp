<?php
/**
 * Create embed test post.
 *
 * @codeCoverageIgnore
 * @package AMP
 */

/**
 * Get test data entries.
 *
 * @throws Exception When there is not enough data in DB.
 * @return array Data entries.
 */
function amp_get_test_data_entries() {
	list( $img_src ) = wp_get_attachment_image_src( amp_get_media_items_ids( 'image', 1 ) );
	return array(
		array(
			'heading' => 'Media Gallery',
			'content' => sprintf( '[gallery ids="%s"]', amp_get_media_items_ids( 'image' ) ),
		),
		array(
			'heading' => 'Media Image',
			'content' => wp_get_attachment_image( amp_get_media_items_ids( 'image', 1 ) ),
		),
		array(
			'heading' => 'Media Caption',
			'content' => sprintf( '[caption width=150]%sExample image caption[/caption]', wp_get_attachment_image( amp_get_media_items_ids( 'image', 1 ) ) ),
		),
		array(
			'heading' => 'Media Video',
			'content' => sprintf( '[video poster="%s" src=https://videos.files.wordpress.com/DK5mLrbr/video-ca6dc0ab4a_hd.mp4]', $img_src ),
		),
		array(
			'heading' => 'Media Audio',
			'content' => '[audio src=https://wptavern.com/wp-content/uploads/2017/11/EPISODE-296-Gutenberg-Telemetry-Calypso-and-More-With-Matt-Mullenweg.mp3]',
		),
		array(
			'heading' => 'Video Playlist',
			'content' => sprintf( '[playlist type="video" ids="%s"]', amp_get_media_items_ids( 'video' ) ),
		),
		array(
			'heading' => 'Audio Playlist',
			'content' => sprintf( '[playlist ids="%s"]', amp_get_media_items_ids( 'audio' ) ),
		),
		array(
			'heading' => 'WordPress Post Embed',
			'content' => 'https://make.wordpress.org/core/2017/12/11/whats-new-in-gutenberg-11th-december/',
		),
		array(
			'heading' => 'Amazon Com Smile Embed',
			'content' => 'https://smile.amazon.com/dp/B00DPM7TIG',
		),
		array(
			'heading' => 'Amazon Co Smile Embed',
			'content' => 'https://smile.amazon.co.uk/dp/B00DPM7TIG',
		),
		array(
			'heading' => 'Amazon Read CN Embed',
			'content' => 'https://read.amazon.cn/kp/embed?asin=B00DPM7TIG',
		),
		array(
			'heading' => 'Animoto Embed',
			'content' => 'https://animoto.com/play/TDfViXkPqIwYj5EjamYjnw',
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
			'content' => 'http://www.dailymotion.com/video/x6bacgf',
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
			'content' => 'http://www.funnyordie.com/videos/2977012a20/i-still-haven-t-found-the-droids-i-m-looking-for',
		),
		array(
			'heading' => 'Hulu Embed',
			'content' => 'https://www.hulu.com/watch/807443',
		),
		array(
			'heading' => 'Instagram Embed',
			'content' => 'https://www.instagram.com/p/bNd86MSFv6/',
		),
		array(
			'heading' => 'Issuu Embed',
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
			'content' => 'http://i1259.photobucket.com/albums/ii543/iamnotpeterpan/EditPostlsaquoDennisDoesCricketmdashWordPress_zpsf72cc13d.png',
		),
		array(
			'heading' => 'Polldaddy Poll Embed',
			'content' => 'https://polldaddy.com/poll/7012505/',
		),
		array(
			'heading' => 'Polldaddy Survey Embed',
			'content' => 'https://rydk.polldaddy.com/s/test-survey',
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
			'heading' => 'Screencast Embed',
			'content' => 'http://www.screencast.com/t/nMCYr3N3uF',
		),
		array(
			'heading' => 'Scribd Embed',
			'content' => 'http://www.scribd.com/doc/110799637/Synthesis-of-Knowledge-Effects-of-Fire-and-Thinning-Treatments-on-Understory-Vegetation-in-Dry-U-S-Forests',
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
			'heading' => 'Someecards Embed',
			'content' => 'https://www.someecards.com/usercards/viewcard/mjaxmi1jmgy2y2exm2m2ngu2ntfi/?tagSlug=christmas',
		),
		array(
			'heading' => 'Someecards Short URL Embed',
			'content' => 'https://some.ly/V3RZUq/',
		),
		array(
			'heading' => 'SoundCloud Embed',
			'content' => 'https://soundcloud.com/jack-villano-villano/mozart-requiem-in-d-minor',
		),
		array(
			'heading' => 'Speaker Deck Embed',
			'content' => 'https://speakerdeck.com/wallat/why-backbone',
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
			'content' => 'http://ifpaintingscouldtext.tumblr.com/post/92003045635/grant-wood-american-gothic-1930',
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
}

/**
 * Get media item ids, using a \WP_Query.
 *
 * @throws Exception When there are not enough posts.
 * @param integer $type The post_mime_type of the media item.
 * @param integer $image_count The number of images for which to query.
 * @return string|WP_CLI::error The media item ids separated by comma on success; error otherwise.
 */
function amp_get_media_items_ids( $type, $image_count = 3 ) {
	$query = new \WP_Query( array(
		'post_type'      => 'attachment',
		'post_mime_type' => $type,
		'post_status'    => 'inherit',
		'posts_per_page' => $image_count,
		'fields'         => 'ids',
	) );
	if ( $query->post_count < $image_count ) {
		throw new Exception( sprintf(
			'Please make sure at least %1$s "%2$s" attachments are accessible and run this script again. There are currently only %3$s.',
			$image_count,
			$type,
			$query->found_posts
		) );
	}
	return implode( ',', $query->get_posts() );
}

/**
 * Create embed test post (page).
 *
 * @param array $data_entries Data.
 *
 * @throws Exception But when database doesn't have enough attachments or in case of error.
 * @return int Page ID.
 */
function amp_create_embed_test_post( $data_entries ) {
	$page = get_page_by_path( '/amp-test-embeds/' );
	if ( $page ) {
		$page_id = $page->ID;
	} else {
		$page_id = wp_insert_post( array(
			'post_name'  => 'amp-test-embeds',
			'post_title' => 'AMP Test Embeds',
			'post_type'  => 'page',
		) );

		if ( ! $page_id || is_wp_error( $page_id ) ) {
			throw new Exception( 'The test page could not be added, please try again.' );
		}
	}

	// Build and update content.
	$content = '';
	foreach ( $data_entries as $entry ) {
		if ( isset( $entry['heading'], $entry['content'] ) ) {
			$content .= sprintf(
				"<h1>%s</h1>\n%s\n\n",
				$entry['heading'],
				$entry['content']
			);
		}
	}

	$update = wp_update_post( wp_slash( array(
		'ID'           => $page_id,
		'post_content' => $content,
	) ) );

	if ( ! $update ) {
		throw new Exception( 'The test page could not be updated, please try again.' );
	}
	return $update;
}

// Bootstrap.
if ( defined( 'WP_CLI' ) ) {
	try {
		$post_id = amp_create_embed_test_post( amp_get_test_data_entries() );
		WP_CLI::success( sprintf( 'Please take a look at: %s', amp_get_permalink( $post_id ) . '#development=1' ) );
	} catch ( Exception $e ) {
		WP_CLI::error( $e->getMessage() );
	}
} else {
	echo "Must be run in WP-CLI via: wp eval-file bin/create-embed-test-post.php\n";
	exit( 1 );
}
