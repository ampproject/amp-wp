Feature: Run the optimizer via the command line

  Background:
    Given a WP installation with the AMP plugin

  Scenario: Optimize an input file
    Given an input.html file:
      """
      <amp-img src="https://example.com/image.jpg" width="500" height="500"></amp-img>
      """

    When I run the WP-CLI command `amp optimizer optimize input.html`
    Then STDERR should be empty
    And STDOUT should contain:
      """
      transformed="self;v=1"
      """
