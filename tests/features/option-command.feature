Feature: Manage AMP plugin options via WP CLI.

  Background:
    Given a WP installation with the AMP plugin

  Scenario: Get an option with user with correct capability
    When I run the WP-CLI command `amp option get theme_support --user=admin`

    Then STDERR should be empty

    And STDOUT should not be empty

  Scenario: Update an option with user with capability
    When I run the WP-CLI command `amp option update theme_support reader --user=admin`

    Then STDERR should be empty

    And STDOUT should be:
      """
      Success: Updated theme_support option.
      """

  Scenario: List AMP plugin options
    When I run the WP-CLI command `amp option list --user=admin`

    Then STDERR should be empty

    And STDOUT should contain:
      """
      option_name
      """

  Scenario: List AMP plugin options which can be managed by CLI.
    When I run the WP-CLI command `amp option list cli-managed-options --user=admin`

    Then STDERR should be empty

    And STDOUT should be:
      """
      reader_theme, theme_support, mobile_redirect
      """

  Scenario: List Reader themes.
    When I run the WP-CLI command `amp option list reader-themes --user=admin`

    Then STDERR should be empty

    And STDOUT should contain:
      """
      legacy
      """

  Scenario: Get an option when user with right capability is not setup
    When I try `wp amp option get reader_theme`

    Then STDERR should contain:
      """
      Error: Sorry, you are not allowed to manage options for the AMP plugin for WordPress.
      """

    And STDOUT should contain:
      """
      Try using --user=<id|login|email> to set the user context or set it in wp-cli.yml.
      """

    And the return code should be 1

  Scenario: Use wrong subcommand with list command.
    When I try `wp amp option list wrong-subcommand --user=admin`

    Then STDERR should be:
      """
      Error: Invalid subcommand: wrong-subcommand
      """

    And STDOUT should be empty
    And the return code should be 1

  Scenario: Update an option which is not allowed to be managed by CLI
    When I try `wp amp option update plugin_configured 1 --user=admin`

    Then STDERR should be:
      """
      Error: You are not allowed to update plugin_configured option via the CLI.
      """

    And STDOUT should be empty
    And the return code should be 1

  Scenario: Update an option which does not exist
    When I try `wp amp option update non_existent_option 1 --user=admin`

    Then STDERR should be:
      """
      Error: Could not update non_existent_option option. Does it exist?
      """

    And STDOUT should be empty
    And the return code should be 1
