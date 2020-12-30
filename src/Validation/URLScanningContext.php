<?php
/**
 * Provides settings to be used in site scanning.
 *
 * @package AMP
 * @since 2.1
 */

namespace AmpProject\AmpWP\Validation;

/**
 * URLScanningContext class.
 *
 * @since 2.1
 *
 * @internal
 */
final class URLScanningContext {

	/**
	 * The default number of URLs per type to return.
	 *
	 * @var int
	 */
	const DEFAULT_LIMIT_PER_TYPE = 1;

	/**
	 * Whether to include URLs that don't support AMP.
	 *
	 * @var bool
	 */
	private $include_unsupported;

	/**
	 * An allowlist of conditionals to use for querying URLs.
	 *
	 * Usually, this class will query all of the templates that don't have AMP disabled. This allows inclusion based on only these conditionals.
	 *
	 * @var string[]
	 */
	private $include_conditionals;

	/**
	 * The maximum number of URLs to provide for each content type.
	 *
	 * Templates are each a separate type, like those for is_category() and is_tag(), and each post type is a type.
	 *
	 * @var int
	 */
	private $limit_per_type;

	/**
	 * Class constructor.
	 *
	 * @param int   $limit_per_type       The maximum number of URLs to validate for each type.
	 * @param array $include_conditionals An allowlist of conditionals to use for validation.
	 * @param bool  $include_unsupported  Whether to include URLs that don't support AMP.
	 */
	public function __construct(
		$limit_per_type = self::DEFAULT_LIMIT_PER_TYPE,
		$include_conditionals = [],
		$include_unsupported = false
	) {
		$this->limit_per_type       = $limit_per_type;
		$this->include_conditionals = $include_conditionals;
		$this->include_unsupported  = $include_unsupported;
	}

	/**
	 * Provides the limit_per_type setting.
	 *
	 * @return int
	 */
	public function get_limit_per_type() {
		/**
		 * Filters the number of URLs per content type to check during each run of the validation cron task.
		 *
		 * @since 2.1.0
		 * @param int $url_validation_number_per_type The number of URLs. Defaults to 1. Filtering to -1 will result in all being returned.
		 */
		$url_validation_limit_per_type = (int) apply_filters( 'amp_url_validation_limit_per_type', $this->limit_per_type );

		return max( $url_validation_limit_per_type, -1 );
	}

	/**
	 * Provides the include_conditionals setting.
	 *
	 * @return string[]
	 */
	public function get_include_conditionals() {
		return $this->include_conditionals;
	}

	/**
	 * Provides the include_unsupported setting.
	 *
	 * @return bool
	 */
	public function get_include_unsupported() {
		return $this->include_unsupported;
	}
}
