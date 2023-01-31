Feature: Manage AMP plugin options via WP CLI.

  Background:
    Given a WP installation with the AMP plugin

  Scenario: Get an option when user does not have manage_options capability
    When I run the WP-CLI command `amp option get theme_support`

    Then STDERR should be:
      """
      Error: Sorry, you are not allowed to manage options for the AMP plugin for WordPress.
      Try using --user=<id|login|email> to set the user context or set it in wp-cli.yml.
      """

    And STDOUT should be empty

  Scenario: Get an option with user with correct capability
    When I run the WP-CLI command `amp option get theme_support --user=admin`

    Then STDERR should be empty

    And STDOUT should not be empty

  Scenario: Get an option which does not exist
    When I run the WP-CLI command `amp option get non_existent_option --user=admin`

    Then STDERR should be:
      """
      Error: Could not get non_existent_option option. Does it exist?
      """

  Scenario: Update an option with user with capability
    When I run the WP-CLI command `amp option update theme_support reader --user=admin`

    Then STDERR should be empty

    And STDOUT should be:
      """
      Success: Updated theme_support option.
      """

  Scenario: Update an option which is not allowed to be managed by CLI
    When I run the WP-CLI command `amp option update plugin_configured 1 --user=admin`

    Then STDERR should be:
      """
      Error: You are not allowed to update plugin_configured option via the CLI.
      """

    And STDOUT should be empty

  Scenario: Update an option which does not exist
    When I run the WP-CLI command `amp option update non_existent_option 1 --user=admin`

    Then STDERR should be:
      """
      Error: Could not update non_existent_option option. Does it exist?
      """

    And STDOUT should be empty

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

  Scenario: Use wrong subcommand with list command.
    When I run the WP-CLI command `amp option list wrong-subcommand --user=admin`

    Then STDERR should be:
      """
      Error: Invalid subcommand: wrong-subcommand
      """

    And STDOUT should be empty
