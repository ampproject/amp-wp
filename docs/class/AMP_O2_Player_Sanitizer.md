## Class `AMP_O2_Player_Sanitizer`

Class AMP_O2_Player_Sanitizer

Converts &lt;div class=&quot;vdb_player&gt;&lt;script&gt;&lt;/script&gt;&lt;/div&gt; embed to &lt;amp-o2-player&gt;

### Methods
* `sanitize`

	<details>

	```php
	public sanitize()
	```

	Sanitize the O2 Player elements from the HTML contained in this instance&#039;s Dom\Document.


	</details>
* `create_amp_o2_player`

	<details>

	```php
	private create_amp_o2_player( Document $dom, \DOMElement $node )
	```

	Replaces node with amp-o2-player


	</details>
* `get_o2_player_attributes`

	<details>

	```php
	private get_o2_player_attributes( $src )
	```

	Gets O2 Player&#039;s required attributes from script src


	</details>
