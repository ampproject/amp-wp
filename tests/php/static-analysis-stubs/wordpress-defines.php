<?php

// These were copied from https://github.com/szepeviktor/phpstan-wordpress.

// There are no core functions to read these constants.
define( 'ABSPATH', './' );
define( 'WP_DEBUG', true );
define( 'WP_DEBUG_LOG', true );
define( 'WPMU_PLUGIN_DIR', './' );
define( 'EMPTY_TRASH_DAYS', 30 * 86400 );

// Constants for expressing human-readable intervals.
define( 'MINUTE_IN_SECONDS', 60 );
define( 'HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
define( 'DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS );
define( 'WEEK_IN_SECONDS',    7 * DAY_IN_SECONDS );
define( 'MONTH_IN_SECONDS',  30 * DAY_IN_SECONDS );
define( 'YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS );

// wpdb method parameters.
define( 'OBJECT', 'OBJECT' );
define( 'OBJECT_K', 'OBJECT_K' );
define( 'ARRAY_A', 'ARRAY_A' );
define( 'ARRAY_N', 'ARRAY_N' );
