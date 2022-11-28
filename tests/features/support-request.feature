Feature: AMP Support Request

  Background:
    Given a WP installation with the AMP plugin

  Scenario: Check AMP Support request data.
    When I run the WP-CLI command `amp support send-diagnostic --print=json-pretty`

    Then STDERR should be empty

    And STDOUT should contain following STRINGS:
      | "site_info": {             |
      | "site_url":                |
      | "site_title":              |
      | "php_version":             |
      | "mysql_version":           |
      | "wp_version":              |
      | "wp_language":             |
      | "wp_https_status":         |
      | "wp_multisite":            |
      | "wp_active_theme": {       |
      | "plugins": [               |
      | "themes": [                |
      | "errors": [                |
      | "error_sources": [         |
      | "urls": [                  |
      | "error_log": {             |
