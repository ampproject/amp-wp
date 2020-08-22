## Function `amp_render_scripts`

```php

```

Generate HTML for AMP scripts that have not yet been printed.

This is adapted from `wp_scripts()-&gt;do_items()`, but it runs only the bare minimum required to output the missing scripts, without allowing other filters to apply which may cause an invalid AMP response. The HTML for the scripts is returned instead of being printed.

