Feature: Update AMP plugins options

  Background:
    Given a WP installation with the AMP plugin

  Scenario: Update option with invalid user capability
    When I try the WP-CLI command `amp option update theme_support reader`
    Then STDERR should be:
      """
      Error: Sorry, you are not allowed to manage options for the AMP plugin for WordPress.
      Try using --user=<id|login|email> to set the user context or set it in wp-cli.yml.
      """
    And STDOUT should be empty
    And the return code should be 1

  Scenario: Update option with valid user capability
    When I run the WP-CLI command `amp option update theme_support reader --user=admin`
    Then STDERR should be empty
    And STDOUT should be:
      """
      Success: Updated theme_support option.
      """
    And the return code should be 0

  Scenario: Update option which does not exist
    When I try the WP-CLI command `amp option update foo bar --user=admin`
    Then STDERR should be:
      """
      Error: Could not update foo option. Does it exist?
      """
    And STDOUT should be empty
    And the return code should be 1

  Scenario: Update option which is not allowed to be managed by WP CLI
    When I try the WP-CLI command `amp option update use_native_img_tag true --user=admin`
    Then STDERR should be:
      """
      Error: You are not allowed to update use_native_img_tag option via the CLI.
      """
    And STDOUT should be empty
    And the return code should be 1

  Scenario: Update option when options REST endpoint is not available
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

    When I try the WP-CLI command `amp option update theme_support reader --user=admin`
    Then STDERR should be:
      """
      Error: Could not get options: Endpoint not available at this time.
      """
    And STDOUT should be empty
    And the return code should be 1

  Scenario: Update option with user context from wp-cli.yml config file
    Given a wp-cli.yml file:
      """
      user: admin
      """

    When I run the WP-CLI command `amp option update theme_support standard`
    Then STDERR should be empty
    And STDOUT should be:
      """
      Success: Updated theme_support option.
      """
    And the return code should be 0
