<?php

// Generated via https://github.com/szepeviktor/phpstan-wordpress

namespace WP_CLI\Utils {
    /**
     * Render a collection of items as an ASCII table, JSON, CSV, YAML, list of ids, or count.
     *
     * Given a collection of items with a consistent data structure:
     *
     * ```
     * $items = array(
     *     array(
     *         'key'   => 'foo',
     *         'value'  => 'bar',
     *     )
     * );
     * ```
     *
     * Render `$items` as an ASCII table:
     *
     * ```
     * WP_CLI\Utils\format_items( 'table', $items, array( 'key', 'value' ) );
     *
     * # +-----+-------+
     * # | key | value |
     * # +-----+-------+
     * # | foo | bar   |
     * # +-----+-------+
     * ```
     *
     * Or render `$items` as YAML:
     *
     * ```
     * WP_CLI\Utils\format_items( 'yaml', $items, array( 'key', 'value' ) );
     *
     * # ---
     * # -
     * #   key: foo
     * #   value: bar
     * ```
     *
     * @access public
     * @category Output
     *
     * @param string       $format Format to use: 'table', 'json', 'csv', 'yaml', 'ids', 'count'.
     * @param array        $items  An array of items to output.
     * @param array|string $fields Named fields for each item of data. Can be array or comma-separated list.
     * @return null
     */
    function format_items($format, $items, $fields)
    {
    }
    /**
     * Create a progress bar to display percent completion of a given operation.
     *
     * Progress bar is written to STDOUT, and disabled when command is piped. Progress
     * advances with `$progress->tick()`, and completes with `$progress->finish()`.
     * Process bar also indicates elapsed time and expected total time.
     *
     * ```
     * # `wp user generate` ticks progress bar each time a new user is created.
     * #
     * # $ wp user generate --count=500
     * # Generating users  22 % [=======>                             ] 0:05 / 0:23
     *
     * $progress = \WP_CLI\Utils\make_progress_bar( 'Generating users', $count );
     * for ( $i = 0; $i < $count; $i++ ) {
     *     // uses wp_insert_user() to insert the user
     *     $progress->tick();
     * }
     * $progress->finish();
     * ```
     *
     * @access public
     * @category Output
     *
     * @param string  $message  Text to display before the progress bar.
     * @param integer $count    Total number of ticks to be performed.
     * @param int     $interval Optional. The interval in milliseconds between updates. Default 100.
     * @return \cli\progress\Bar|\WP_CLI\NoOp
     */
    function make_progress_bar($message, $count, $interval = 100)
    {
    }

    /**
	 * Return the flag value or, if it's not set, the $default value.
	 *
	 * Because flags can be negated (e.g. --no-quiet to negate --quiet), this
	 * function provides a safer alternative to using
	 * `isset( $assoc_args['quiet'] )` or similar.
	 *
	 * @access public
	 * @category Input
	 *
	 * @param array  $assoc_args Arguments array.
	 * @param string $flag       Flag to get the value.
	 * @param mixed  $default    Default value for the flag. Default: NULL.
	 * @return mixed
	 */
	function get_flag_value( $assoc_args, $flag, $default = null )
	{
	}
}
