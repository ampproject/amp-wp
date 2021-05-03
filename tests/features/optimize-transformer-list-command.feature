Feature: List the optimizer transformer configurations via the command line

  Background:
    Given a WP installation with the AMP plugin

  Scenario: List all transformers
    When I run the WP-CLI command `amp optimizer transformer list`

    Then STDERR should be empty

    And STDOUT should be a table containing rows:
      | transformer          | source  |
      | AmpSchemaOrgMetadata | plugin  |
      | ServerSideRendering  | toolbox |

  Scenario: Filter the transformers by source
    When I run the WP-CLI command `amp optimizer transformer list --source=plugin`

    Then STDERR should be empty

    And STDOUT should be a table containing rows:
      | transformer          | source  |
      | AmpSchemaOrgMetadata | plugin  |

    And STDOUT should not contain:
      """
      ServerSideRendering
      """

    And STDOUT should not contain:
      """
      toolbox
      """

  Scenario: Display specific fields
    When I run the WP-CLI command `amp optimizer transformer list --fields=transformer`

    Then STDERR should be empty

    And STDOUT should be a table containing rows:
      | transformer          |
      | AmpSchemaOrgMetadata |
      | ServerSideRendering  |

    And STDOUT should not contain:
      """
      source
      """

    And STDOUT should not contain:
      """
      plugin
      """

    And STDOUT should not contain:
      """
      toolbox
      """

  Scenario: Display a single field
    When I run the WP-CLI command `amp optimizer transformer list --field=transformer`

    Then STDERR should be empty

    And STDOUT should contain:
      """
      AmpSchemaOrgMetadata
      """

    And STDOUT should contain:
      """
      ServerSideRendering
      """

    And STDOUT should not contain:
      """
      transformer
      """

    And STDOUT should not contain:
      """
      source
      """

    And STDOUT should not contain:
      """
      plugin
      """

    And STDOUT should not contain:
      """
      toolbox
      """

  Scenario: Display in JSON format
    When I run the WP-CLI command `amp optimizer transformer list --format=json`

    Then STDERR should be empty

    And STDOUT should contain:
      """
      {"transformer":"AmpSchemaOrgMetadata","source":"plugin"}
      """

    And STDOUT should contain:
      """
      {"transformer":"ServerSideRendering","source":"toolbox"}
      """

  Scenario: Display in YAML format
    # Ensure trailing spaces in expected output are not stripped by editors.
    Given I run `echo ' '`
    Then save STDOUT as {TRAILING_SPACE}

    When I run the WP-CLI command `amp optimizer transformer list --format=yaml`

    Then STDERR should be empty

    And STDOUT should contain:
      """
      -{TRAILING_SPACE}
        transformer: AmpSchemaOrgMetadata
        source: plugin
      """

    And STDOUT should contain:
      """
      -{TRAILING_SPACE}
        transformer: ServerSideRendering
        source: toolbox
      """
