Feature: Get AMP plugins options

  Background:
    Given a WP installation with the AMP plugin

  Scenario: Get option with invalid user capability
    When I try the WP-CLI command `amp option get theme_support`
    Then STDERR should be:
      """
      Error: Sorry, you are not allowed to manage options for the AMP plugin for WordPress.
      Try using --user=<id|login|email> to set the user context or set it in wp-cli.yml.
      """
    And STDOUT should be empty
    And the return code should be 1

  Scenario: Get option with valid user capability
    When I run the WP-CLI command `amp option update theme_support standard --user=admin`
    And I run the WP-CLI command `amp option get theme_support --user=admin`
    Then STDERR should be empty
    And STDOUT should be:
      """
      standard
      """
    And the return code should be 0

  Scenario: Get option which does not exist
    When I try the WP-CLI command `amp option get foo --user=admin`
    Then STDERR should be:
      """
      Error: Could not get "foo" option. Does it exist?
      """
    And STDOUT should be empty
    And the return code should be 1

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

    When I try the WP-CLI command `amp option get theme_support --user=admin`
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

    When I run the WP-CLI command `amp option update theme_support standard`
    And I run the WP-CLI command `amp option get theme_support`
    Then STDERR should be empty
    And STDOUT should be:
      """
      standard
      """
    And the return code should be 0
