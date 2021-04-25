Feature: List the optimizer transformers via the command line

  Background:
    Given a WP installation with the AMP plugin

  Scenario: Show all configuration data
    When I run the WP-CLI command `amp optimizer transformer config AmpRuntimeCss`

    Then STDERR should be empty

    And STDOUT should be a table containing rows:
      | key     | value |
      | canary  | false |
      | styles  |       |
      | version |       |

  Scenario: Show specific fields
    When I run the WP-CLI command `amp optimizer transformer config AmpRuntimeCss --fields=key`

    Then STDERR should be empty

    And STDOUT should be a table containing rows:
      | key     |
      | canary  |
      | styles  |
      | version |

    And STDOUT should not contain:
      """
      value
      """

    And STDOUT should not contain:
      """
      false
      """

  Scenario: Show a specific configuration key
    When I run the WP-CLI command `amp optimizer transformer config AmpRuntimeCss --key=canary`

    Then STDERR should be empty

    And STDOUT should be a table containing rows:
      | key     | value |
      | canary  | false |

    And STDOUT should not contain:
      """
      styles
      """

    And STDOUT should not contain:
      """
      version
      """

  Scenario: Show a specific field
    When I run the WP-CLI command `amp optimizer transformer config AmpRuntimeCss --field=key`

    Then STDERR should be empty

    And STDOUT should be:
      """
      canary
      styles
      version
      """

  Scenario: Handle lack of configuration
    When I try the WP-CLI command `amp optimizer transformer config ServerSideRendering`

    Then STDOUT should be empty

    And STDERR should contain:
      """
      Error: No configuration class was registered for the transformer 'AmpProject\Optimizer\Transformer\ServerSideRendering'.
      """

  Scenario: Display in JSON format
    When I run the WP-CLI command `amp optimizer transformer config AmpRuntimeCss --format=json`

    Then STDERR should be empty

    And STDOUT should be:
      """
      {"canary":false,"styles":"","version":""}
      """

  Scenario: Display in YAML format
    # Ensure trailing spaces in expected output are not stripped by editors.
    Given I run `echo ' '`
    Then save STDOUT as {TRAILING_SPACE}

    When I run the WP-CLI command `amp optimizer transformer config AmpRuntimeCss --format=yaml`

    Then STDERR should be empty

    And STDOUT should contain:
      """
      ---
      -{TRAILING_SPACE}
        key: canary
        value: false
      -{TRAILING_SPACE}
        key: styles
        value: ""
      -{TRAILING_SPACE}
        key: version
        value: ""
      """
