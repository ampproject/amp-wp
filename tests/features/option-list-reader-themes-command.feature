Feature: List AMP plugins reader themes

  Background:
    Given a WP installation with the AMP plugin

  Scenario: List reader themes with invalid user capability
    When I try the WP-CLI command `amp option list-reader-themes`
    Then STDERR should be:
      """
      Error: Sorry, you are not allowed to manage options for the AMP plugin for WordPress.
      Try using --user=<id|login|email> to set the user context or set it in wp-cli.yml.
      """
    And STDOUT should be empty
    And the return code should be 1

  Scenario: List reader themes with valid user capability
    When I run the WP-CLI command `amp option list-reader-themes --user=admin`
    Then STDERR should be empty
    And STDOUT should contain:
      """
      legacy
      """
    And the return code should be 0

  Scenario: Get option with user context from wp-cli.yml config file
    Given a wp-cli.yml file:
      """
      user: admin
      """

    When I run the WP-CLI command `amp option list-reader-themes`
    Then STDERR should be empty
    And STDOUT should contain:
      """
      legacy
      """
    And the return code should be 0
