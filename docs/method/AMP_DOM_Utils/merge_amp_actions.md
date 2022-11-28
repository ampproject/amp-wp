## Method `AMP_DOM_Utils::merge_amp_actions()`

```php
static public function merge_amp_actions( $first, $second );
```

Merge two sets of AMP events &amp; actions.

### Arguments

* `string $first` - First event/action string.
* `string $second` - First event/action string.

### Return value

`string` - Merged event/action string.

### Source

:link: [includes/utils/class-amp-dom-utils.php:370](/includes/utils/class-amp-dom-utils.php#L370-L408)

<details>
<summary>Show Code</summary>

```php
public static function merge_amp_actions( $first, $second ) {
	$events = [];
	foreach ( [ $first, $second ] as $event_action_string ) {
		$matches = [];
		$results = preg_match_all( self::AMP_EVENT_ACTIONS_REGEX_PATTERN, $event_action_string, $matches );
		if ( ! $results || ! isset( $matches['event'] ) ) {
			continue;
		}
		foreach ( $matches['event'] as $index => $event ) {
			$events[ $event ][] = $matches['actions'][ $index ];
		}
	}
	$value_strings = [];
	foreach ( $events as $event => $action_strings_array ) {
		$actions_array = [];
		array_walk(
			$action_strings_array,
			static function ( $actions ) use ( &$actions_array ) {
				$matches = [];
				$results = preg_match_all( self::AMP_ACTION_REGEX_PATTERN, $actions, $matches );
				if ( ! $results || ! isset( $matches['action'] ) ) {
					$actions_array[] = $actions;
					return;
				}
				$actions_array = array_merge( $actions_array, $matches['action'] );
			}
		);
		$actions         = implode( ',', array_unique( array_filter( $actions_array ) ) );
		$value_strings[] = "{$event}:{$actions}";
	}
	return implode( ';', $value_strings );
}
```

</details>
