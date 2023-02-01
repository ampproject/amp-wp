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

  Scenario: List option with valid user capability
    When I run the WP-CLI command `amp option list --user=admin`
    Then STDERR should be empty
    And STDOUT should contain:
      """
      option_name
      """
    And the return code should be 0

  Scenario: List options which can be managed by CLI.
    When I run the WP-CLI command `amp option list cli-managed-options --user=admin`
    Then STDERR should be empty
    And STDOUT should be:
      """
      reader_theme, theme_support, mobile_redirect
      """
    And the return code should be 0

  Scenario: List Reader themes.
    When I run the WP-CLI command `amp option list reader-themes --user=admin`
    Then STDERR should be empty
    And STDOUT should contain:
      """
      legacy
      """
    And the return code should be 0

  Scenario: List option with invalid subcommand
    When I try the WP-CLI command `amp option list wrong-subcommand --user=admin`
    Then STDERR should be:
      """
      Error: Invalid subcommand: wrong-subcommand
      """
    And STDOUT should be empty
    And the return code should be 1

  Scenario: Get option when options REST endpoint is not available
    Given a wp-content/mu-plugins/options-rest-endpoint-mock-response.php file:
      """
      <?php
      add_filter( 'rest_request_after_callbacks', function( $response, $handler, $request ) {
        if ( '/amp/v1/options' === $request->get_route() ) {
          return new WP_Error(
            'amp_rest_cannot_manage_options',
            __( 'Endpoint not available at this time.', 'amp' ),
          );
        }

        return $response;
      }, 10, 3 );
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

    When I run the WP-CLI command `amp option list --user=admin`
    Then STDERR should be empty
    And STDOUT should contain:
      """
      option_name
      """
    And the return code should be 0
