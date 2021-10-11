<?php
/**
 * Class FeatureContext.
 *
 * Feature tests context class with AmpWP-specific steps.
 *
 * @package AmpProject/AmpWP
 */

namespace AmpProject\AmpWP\Tests\Behat;

use Behat\Behat\Hook\Scope\AfterFeatureScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Behat\Hook\Scope\BeforeFeatureScope;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\ScenarioInterface;
use WP_CLI\Process;
use WP_CLI\Tests\Context\FeatureContext as WP_CLI_FeatureContext;
use WP_CLI\Utils;
use RuntimeException;
use Exception;

use function WP_CLI\Tests\Context\wp_cli_behat_env_debug;

/**
 * Feature tests context class with AmpWP-specific steps.
 *
 * This class extends the one that is provided by the wp-cli/wp-cli-tests package.
 * To see a list of all recognized step definitions, run `vendor/bin/behat -dl`.
 *
 * @package AmpProject\AmpWP
 */
final class FeatureContext extends WP_CLI_FeatureContext {

	/**
	 * The current feature.
	 *
	 * @var FeatureNode|null
	 */
	private static $feature;

	/**
	 * The current scenario.
	 *
	 * @var ScenarioInterface|null
	 */
	private $scenario;

	/**
	 * @BeforeFeature
	 */
	public static function store_feature( BeforeFeatureScope $scope ) {
		self::$feature = $scope->getFeature();
	}

	/**
	 * @BeforeScenario
	 */
	public function store_scenario( BeforeScenarioScope $scope ) {
		$this->scenario = $scope->getScenario();
	}

	/**
	 * @AfterScenario
	 */
	public function forget_scenario( AfterScenarioScope $scope ) {
		$this->scenario = null;
	}

	/**
	 * @AfterFeature
	 */
	public static function forget_feature( AfterFeatureScope $scope ) {
		self::$feature = null;
	}

	/**
	 * @Given a WP install(ation) with the AMP plugin
	 */
	public function given_a_wp_installation_with_the_amp_plugin() {
		$this->install_wp();

		// Symlink the current project folder into the WP folder as a plugin.
		$project_dir = realpath( self::get_vendor_dir() . '/../' );
		$plugin_dir  = $this->variables['RUN_DIR'] . '/wp-content/plugins';
		$this->ensure_dir_exists( $plugin_dir );
		$this->proc( "ln -s {$project_dir} {$plugin_dir}/amp" )->run_check();

		// Activate the plugin.
		$this->proc( 'wp plugin activate amp' )->run_check();
	}

	/**
	 * @When /^I (run|try) the WP-CLI command `([^`]+)`$/
	 */
	public function when_i_run_the_wp_cli_command( $mode, $command ) {
		$command = "wp {$command}";

		$with_code_coverage = getenv( 'BEHAT_CODE_COVERAGE' );
		if ( in_array( $with_code_coverage, [ true, 'true', 1, '1' ], true ) ) {
			$command = "{$command} --require={PROJECT_DIR}/tests/php/maybe-generate-wp-cli-coverage.php";
		}

		$command = $this->replace_variables( $command );

		$this->result = $this->wpcli_tests_invoke_proc(
			$this->proc_with_env(
				$command,
				[
					'BEHAT_PROJECT_DIR'    => $this->variables['PROJECT_DIR'],
					'BEHAT_FEATURE_TITLE'  => self::$feature->getTitle(),
					'BEHAT_SCENARIO_TITLE' => $this->scenario->getTitle(),
				]
			),
			$mode
		);

		list( $this->result->stdout, $this->email_sends ) = $this->wpcli_tests_capture_email_sends( $this->result->stdout );
	}

	/**
	 * Ensure that a requested directory exists and create it recursively as needed.
	 *
	 * @param string $directory Directory to ensure the existence of.
	 */
	private function ensure_dir_exists( $directory ) {
		$parent = dirname( $directory );

		if ( ! empty( $parent ) && ! is_dir( $parent ) ) {
			$this->ensure_dir_exists( $parent );
		}

		if ( ! is_dir( $directory ) && ! mkdir( $directory ) && ! is_dir( $directory ) ) {
			throw new RuntimeException( "Could not create directory '{$directory}'." );
		}
	}

	/**
	 * Create a new process with added environment variables.
	 *
	 * @param string $command Command to run.
	 * @param array  $env     Associative array of environment variables to add.
	 * @return Process Process to execute.
	 */
	public function proc_with_env( $command, $env = [] ) {
		$env = array_merge(
			self::get_process_env_variables(),
			$env
		);

		if ( isset( $this->variables['SUITE_CACHE_DIR'] ) ) {
			$env['WP_CLI_CACHE_DIR'] = $this->variables['SUITE_CACHE_DIR'];
		}

		if ( isset( $this->variables['RUN_DIR'] ) ) {
			$cwd = "{$this->variables['RUN_DIR']}/";
		} else {
			$cwd = null;
		}

		return Process::create( $command, $cwd, $env );
	}

	/**
	 * Get the environment variables required for launched `wp` processes.
	 *
	 * This is copied over from WP_CLI\Tests\Context\FeatureContext, to enable an adaption of FeatureContext::proc().
	 */
	private static function get_process_env_variables() {
		// Ensure we're using the expected `wp` binary.
		$bin_path = self::get_bin_path();
		wp_cli_behat_env_debug( "WP-CLI binary path: {$bin_path}" );

		if ( ! file_exists( "{$bin_path}/wp" ) ) {
			wp_cli_behat_env_debug( "WARNING: No file named 'wp' found in the provided/detected binary path." );
		}

		if ( ! is_executable( "{$bin_path}/wp" ) ) {
			wp_cli_behat_env_debug( "WARNING: File named 'wp' found in the provided/detected binary path is not executable." );
		}

		$path_separator = Utils\is_windows() ? ';' : ':';
		$env            = [
			'PATH'      => $bin_path . $path_separator . getenv( 'PATH' ),
			'BEHAT_RUN' => 1,
			'HOME'      => sys_get_temp_dir() . '/wp-cli-home',
		];

		$config_path = getenv( 'WP_CLI_CONFIG_PATH' );
		if ( false !== $config_path ) {
			$env['WP_CLI_CONFIG_PATH'] = $config_path;
		}

		$term = getenv( 'TERM' );
		if ( false !== $term ) {
			$env['TERM'] = $term;
		}

		$php_args = getenv( 'WP_CLI_PHP_ARGS' );
		if ( false !== $php_args ) {
			$env['WP_CLI_PHP_ARGS'] = $php_args;
		}

		$php_used = getenv( 'WP_CLI_PHP_USED' );
		if ( false !== $php_used ) {
			$env['WP_CLI_PHP_USED'] = $php_used;
		}

		$php = getenv( 'WP_CLI_PHP' );
		if ( false !== $php ) {
			$env['WP_CLI_PHP'] = $php;
		}

		$travis_build_dir = getenv( 'TRAVIS_BUILD_DIR' );
		if ( false !== $travis_build_dir ) {
			$env['TRAVIS_BUILD_DIR'] = $travis_build_dir;
		}

		// Dump environment for debugging purposes, but before adding the GitHub token.
		wp_cli_behat_env_debug( 'Environment:' );
		foreach ( $env as $key => $value ) {
			wp_cli_behat_env_debug( "   [{$key}] => {$value}" );
		}

		$github_token = getenv( 'GITHUB_TOKEN' );
		if ( false !== $github_token ) {
			$env['GITHUB_TOKEN'] = $github_token;
		}

		return $env;
	}

	/**
	 * @Then /^STDOUT should contain following STRINGS:$/
	 */
	public function then_stdout_should_contain_following_strings( TableNode $expected ) {

		$output          = $this->result->stdout;
		$missing_strings = [];

		foreach ( $expected->getRows() as $row ) {

			if ( ! empty( $row[0] ) && false === strpos( $output, $row[0] ) ) {
				$missing_strings[] = $row;
			}
		}
		if ( ! empty( $missing_strings ) ) {
			throw new Exception( $output );
		}
	}
}
