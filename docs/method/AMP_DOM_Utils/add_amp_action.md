## Method `AMP_DOM_Utils::add_amp_action()`

```php
static public function add_amp_action( \DOMElement $element, $event, $action );
```

Register an AMP action to an event on a given element.

If the element already contains one or more events or actions, the method will assemble them in a smart way.

### Arguments

* `\DOMElement $element` - Element to add an action to.
* `string $event` - Event to trigger the action on.
* `string $action` - Action to add.

### Source

:link: [includes/utils/class-amp-dom-utils.php:343](/includes/utils/class-amp-dom-utils.php#L343-L359)

<details>
<summary>Show Code</summary>

```php
public static function add_amp_action( DOMElement $element, $event, $action ) {
	$event_action_string = "{$event}:{$action}";
	if ( ! $element->hasAttribute( 'on' ) ) {
		// There's no "on" attribute yet, so just add it and be done.
		$element->setAttribute( 'on', $event_action_string );
		return;
	}
	$element->setAttribute(
		'on',
		self::merge_amp_actions(
			$element->getAttribute( 'on' ),
			$event_action_string
		)
	);
}
```

</details>
