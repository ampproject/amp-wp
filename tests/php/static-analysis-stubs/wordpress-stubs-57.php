<?php
/**
 * Prints inline JavaScript wrapped in `<script>` tag.
 *
 * It is possible to inject attributes in the `<script>` tag via the  {@see 'wp_script_attributes'}  filter.
 * Automatically injects type attribute if needed.
 *
 * @since 5.7.0
 *
 * @param string $javascript Inline JavaScript code.
 * @param array  $attributes Optional. Key-value pairs representing `<script>` tag attributes.
 */
function wp_print_inline_script_tag( $javascript, $attributes = [] ) {}
