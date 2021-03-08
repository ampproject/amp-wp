<?php

// Generated via https://github.com/szepeviktor/phpstan-wordpress

namespace {
	/**
	 * Various utilities for WP-CLI commands.
	 */
	class WP_CLI {
		/**
		 * Register a command to WP-CLI.
		 *
		 * WP-CLI supports using any callable class, function, or closure as a
		 * command. `WP_CLI::add_command()` is used for both internal and
		 * third-party command registration.
		 *
		 * Command arguments are parsed from PHPDoc by default, but also can be
		 * supplied as an optional third argument during registration.
		 *
		 * ```
		 * # Register a custom 'foo' command to output a supplied positional param.
		 * #
		 * # $ wp foo bar --append=qux
		 * # Success: bar qux
		 *
		 * /**
		 *  * My awesome closure command
		 *  *
		 *  * <message>
		 *  * : An awesome message to display
		 *  *
		 *  * --append=<message>
		 *  * : An awesome message to append to the original message.
		 *  *
		 *  * @when before_wp_load
		 *  *\/
		 * $foo = function( $args, $assoc_args ) {
		 *     WP_CLI::success( $args[0] . ' ' . $assoc_args['append'] );
		 * };
		 * WP_CLI::add_command( 'foo', $foo );
		 * ```
		 *
		 * @access public
		 * @category Registration
		 *
		 * @param string   $name Name for the command (e.g. "post list" or "site empty").
		 * @param callable $callable Command implementation as a class, function or closure.
		 * @param array    $args {
		 *    Optional. An associative array with additional registration parameters.
		 *
		 *    @type callable $before_invoke Callback to execute before invoking the command.
		 *    @type callable $after_invoke  Callback to execute after invoking the command.
		 *    @type string   $shortdesc     Short description (80 char or less) for the command.
		 *    @type string   $longdesc      Description of arbitrary length for examples, etc.
		 *    @type string   $synopsis      The synopsis for the command (string or array).
		 *    @type string   $when          Execute callback on a named WP-CLI hook (e.g. before_wp_load).
		 *    @type bool     $is_deferred   Whether the command addition had already been deferred.
		 * }
		 * @return bool True on success, false if deferred, hard error if registration failed.
		 */
		public static function add_command($name, $callable, $args = array())
		{
		}
		/**
		 * Display error message prefixed with "Error: " and exit script.
		 *
		 * Error message is written to STDERR. Defaults to halting script execution
		 * with return code 1.
		 *
		 * Use `WP_CLI::warning()` instead when script execution should be permitted
		 * to continue.
		 *
		 * ```
		 * # `wp cache flush` considers flush failure to be a fatal error.
		 * if ( false === wp_cache_flush() ) {
		 *     WP_CLI::error( 'The object cache could not be flushed.' );
		 * }
		 * ```
		 *
		 * @access public
		 * @category Output
		 *
		 * @param string|WP_Error  $message Message to write to STDERR.
		 * @param boolean|integer  $exit    True defaults to exit(1).
		 * @return null
		 */
		public static function error($message, $exit = \true)
		{
		}
		/**
		 * Ask for confirmation before running a destructive operation.
		 *
		 * If 'y' is provided to the question, the script execution continues. If
		 * 'n' or any other response is provided to the question, script exits.
		 *
		 * ```
		 * # `wp db drop` asks for confirmation before dropping the database.
		 *
		 * WP_CLI::confirm( "Are you sure you want to drop the database?", $assoc_args );
		 * ```
		 *
		 * @access public
		 * @category Input
		 *
		 * @param string $question Question to display before the prompt.
		 * @param array $assoc_args Skips prompt if 'yes' is provided.
		 */
		public static function confirm($question, $assoc_args = array())
		{
		}
		/**
		 * Display warning message prefixed with "Warning: ".
		 *
		 * Warning message is written to STDERR.
		 *
		 * Use instead of `WP_CLI::debug()` when script execution should be permitted
		 * to continue.
		 *
		 * ```
		 * # `wp plugin activate` skips activation when plugin is network active.
		 * $status = $this->get_status( $plugin->file );
		 * // Network-active is the highest level of activation status
		 * if ( 'active-network' === $status ) {
		 *   WP_CLI::warning( "Plugin '{$plugin->name}' is already network active." );
		 *   continue;
		 * }
		 * ```
		 *
		 * @access public
		 * @category Output
		 *
		 * @param string $message Message to write to STDERR.
		 * @return null
		 */
		public static function warning($message)
		{
		}
		/**
		 * Display success message prefixed with "Success: ".
		 *
		 * Success message is written to STDOUT.
		 *
		 * Typically recommended to inform user of successful script conclusion.
		 *
		 * ```
		 * # wp rewrite flush expects 'rewrite_rules' option to be set after flush.
		 * flush_rewrite_rules( \WP_CLI\Utils\get_flag_value( $assoc_args, 'hard' ) );
		 * if ( ! get_option( 'rewrite_rules' ) ) {
		 *     WP_CLI::warning( "Rewrite rules are empty." );
		 * } else {
		 *     WP_CLI::success( 'Rewrite rules flushed.' );
		 * }
		 * ```
		 *
		 * @access public
		 * @category Output
		 *
		 * @param string $message Message to write to STDOUT.
		 * @return null
		 */
		public static function success($message)
		{
		}
		/**
		 * Display informational message without prefix, and ignore `--quiet`.
		 *
		 * Message is written to STDOUT. `WP_CLI::log()` is typically recommended;
		 * `WP_CLI::line()` is included for historical compat.
		 *
		 * @access public
		 * @category Output
		 *
		 * @param string $message Message to display to the end user.
		 * @return null
		 */
		public static function line($message = '')
		{
		}
		/**
		 * Display informational message without prefix.
		 *
		 * Message is written to STDOUT, or discarded when `--quiet` flag is supplied.
		 *
		 * ```
		 * # `wp cli update` lets user know of each step in the update process.
		 * WP_CLI::log( sprintf( 'Downloading from %s...', $download_url ) );
		 * ```
		 *
		 * @access public
		 * @category Output
		 *
		 * @param string $message Message to write to STDOUT.
		 */
		public static function log($message)
		{
		}
	}
}
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
