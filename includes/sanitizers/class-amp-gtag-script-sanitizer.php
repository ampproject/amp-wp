<?php
/**
 * GA4 Script Sanitizer.
 *
 * - This sanitizer will facilitate using GA4 while waiting on an AMP implementation.
 * - This sanitizer will be only used in Moderate or Loose sandboxing level
 *
 * @since 2.4.0
 * @package AMP
 */

use AmpProject\AmpWP\Option;
use AmpProject\AmpWP\ValidationExemption;

/**
 * Class AMP_GTag_Script_Sanitizer
 *
 * @since 2.4.0
 * @internal
 */
class AMP_GTag_Script_Sanitizer extends AMP_Base_Sanitizer {

	/**
	 * Sanitize the AMP response for GA4 scripts.
	 *
	 * @since 2.4.0
	 */
	public function sanitize() {
		if ( ! AMP_Options_Manager::get_option( Option::SANDBOXING_ENABLED ) ) {
			return;
		}

		$sandboxing_level = AMP_Options_Manager::get_option( Option::SANDBOXING_LEVEL );

		if ( 1 !== $sandboxing_level && 2 !== $sandboxing_level ) {
			return;
		}

		/**
		 * GTag Script looks like this:
		 *
		 * <script async src="https://www.googletagmanager.com/gtag/js?id=xxxxxx"></script>
		 * <script>
		 *   window.dataLayer = window.dataLayer || [];
		 *   function gtag(){dataLayer.push(arguments);}
		 *   gtag('js', new Date());
		 *
		 *   gtag('config', 'xxxxxx');
		 * </script>
		 */
		$scripts = $this->dom->xpath->query( '//script[ ( @async and starts-with( @src, "https://www.googletagmanager.com/gtag/js" ) ) or contains( text(), "function gtag(" ) ]' );
		if ( ! $scripts instanceof DOMNodeList || 0 === $scripts->length ) {
			return;
		}

		foreach ( $scripts as $script ) {
			ValidationExemption::mark_node_as_px_verified( $script );
		}

		/**
		 * Mark inline gtag events as PX verified attributes.
		 *
		 * Such inline events can look like:
		 *
		 * onclick="gtag('event','click', { 'event_category':"click", 'event_label':"contactPage" })"
		 * onsubmit="gtag('event','submit', { 'event_category':"submit", 'event_label':"contactPage" })"
		 * onkeypress="gtag('event','keypress', { 'event_category':"keypress", 'event_label':"contactPage" })"
		 */
		$inline_events = $this->dom->xpath->query(
			'
				//@*[
					starts-with(name(), "on")
					and
					name() != "on"
					and
					contains(., "gtag(")
				]
			'
		);

		if ( $inline_events instanceof DOMNodeList ) {
			foreach ( $inline_events as $inline_event ) {
				ValidationExemption::mark_node_as_px_verified( $inline_event );
			}
		}
	}
}
