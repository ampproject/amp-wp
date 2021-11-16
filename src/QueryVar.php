<?php
/**
 * Interface QueryVar.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

/**
 * An interface to capture URL query vars used in the plugin.
 *
 * @package AmpProject\AmpWP
 * @since 2.0
 * @internal
 */
interface QueryVar {

	/**
	 * Query var used to signal the request for an AMP page.
	 *
	 * Historically this value may be filtered via `amp_query_var` or overridden via the `AMP_QUERY_VAR` constant. The
	 * value then was also used as an endpoint (`/amp/`) in addition to being used as a URL query var (`?amp`).
	 * Nevertheless, in 2.0 this will no longer be supported in the theme itself when a Reader theme is selected.
	 * Additionally, the logic for determining whether a request is for an AMP endpoint or not may also be arbitrarily
	 * overridden as of amp-wp#2204.
	 *
	 * @link https://github.com/ampproject/amp-wp/issues/2204
	 * @see amp_get_slug()
	 * @var string
	 */
	const AMP = 'amp';

	/**
	 * Query var used to signal the request for an non-AMP page.
	 *
	 * This is used in a paired mode (Transitional or Reader) in two ways:
	 *
	 * - To indicate that a mobile visitor should not be redirected to the AMP version. This is used in links from the
	 *   AMP version back to the non-AMP version to prevent redirection. The value for the query var in this case is
	 *   the NOAMP_MOBILE constant below.
	 * - To indicate that the AMP version should not even be made available when making a request. This is used when a
	 *   user had tried to access the AMP version but there were validation errors which have kept invalid markup,
	 *   causing the AMP version to not be available. In such case, non-authenticated users who cannot validate are then
	 *   redirected to the non-AMP version with this query var added, along the below NOAMP_AVAILABLE constant as the value.
	 *
	 * This has no effect on an AMP-first site (Standard mode).
	 *
	 * @var string
	 */
	const NOAMP = 'noamp';

	/**
	 * Value for the noamp query var to indicate that a mobile visitor should not be redirected to the AMP version of a page.
	 *
	 * @var string
	 */
	const NOAMP_MOBILE = 'mobile';

	/**
	 * Value for the noamp query var to indicate that AMP should be disabled entirely for a given request.
	 *
	 * @var string
	 */
	const NOAMP_AVAILABLE = 'available';

	/**
	 * Value for the query var that allows switching to verbose server-timing output.
	 *
	 * @var string
	 */
	const VERBOSE_SERVER_TIMING = 'amp_verbose_server_timing';

	/**
	 * Query parameter provided to customize.php to indicate that the preview should be loaded with an AMP URL.
	 *
	 * @var string
	 */
	const AMP_PREVIEW = 'amp_preview';

	/**
	 * Query parameter provided to Settings Screen to indicate that a scan should be automatically initiated if the results are stale.
	 *
	 * @var string
	 */
	const AMP_SCAN_IF_STALE = 'amp-scan-if-stale';
}
