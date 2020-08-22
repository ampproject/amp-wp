## Class `AMP_Widget_Archives`

Class AMP_Widget_Archives

### Methods
* `widget`

	<details>

	```php
	public widget( $args, $instance )
	```

	Echoes the markup of the widget.

Mainly copied from WP_Widget_Archives::widget() Changes include: An id for the &lt;form&gt;. More escaping. The dropdown is now filtered with &#039;wp_dropdown_cats.&#039; This enables adding an &#039;on&#039; attribute, with the id of the form. So changing the dropdown value will redirect to the category page, with valid AMP.


	</details>
