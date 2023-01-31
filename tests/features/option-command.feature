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
