Feature: List AMP plugins options

  Background:
    Given a WP installation with the AMP plugin

  Scenario: List option with invalid user capability
    When I try the WP-CLI command `amp option list`
    Then STDERR should be:
      """
      Error: Sorry, you are not allowed to manage options for the AMP plugin for WordPress.
      Try using --user=<id|login|email> to set the user context or set it in wp-cli.yml.
      """
    And STDOUT should be empty
    And the return code should be 1

  Scenario: List option with valid user capability and no shell pipe
    When I run `SHELL_PIPE=0 wp amp option list --user=admin`
    Then STDERR should be empty
    And STDOUT should contain:
      """
      option_name
      """
    And STDOUT should contain:
      """
      theme_support
      """
    And STDOUT should contain:
      """
      sandboxing_enabled
      """
    And STDOUT should contain:
      """
      delete_data_at_uninstall
      """
    And STDOUT should contain:
      """
      Only the above listed options can currently be updated via the CLI.
      """
    And STDOUT should contain:
      """
      Please raise a feature request
      """
    And the return code should be 0

  Scenario: List option with valid user capability and shell pipe
    When I run `SHELL_PIPE=1 wp amp option list --user=admin`
    Then STDERR should be empty
    And STDOUT should contain:
      """
      option_name
      """
    And STDOUT should contain:
      """
      theme_support
      """
    And STDOUT should contain:
      """
      sandboxing_enabled
      """
    And STDOUT should contain:
      """
      delete_data_at_uninstall
      """
    And STDOUT should not contain:
      """
      Only the above listed options can currently be updated via the CLI.
      """
    And STDOUT should not contain:
      """
      Please raise a feature request
      """
    And the return code should be 0

  Scenario: Get option when options REST endpoint is not available
    Given a wp-content/mu-plugins/options-rest-endpoint-mock-response.php file:
      """
      <?php
        add_filter(
          'rest_request_after_callbacks',
          function( $response, $handler, $request ) {
            if ( '/amp/v1/options' === $request->get_route() ) {
                return new WP_Error(
                  'amp_rest_cannot_manage_options',
                  __( 'Endpoint not available at this time.', 'amp' )
                );
            }

            return $response;
          },
          10,
          3
        );
      """

    When I try the WP-CLI command `amp option list --user=admin`
    Then STDERR should be:
      """
      Error: Could not retrieve options: Endpoint not available at this time.
      """
    And STDOUT should be empty
    And the return code should be 1

  Scenario: Get option with user context from wp-cli.yml config file
    Given a wp-cli.yml file:
      """
      user: admin
      """
