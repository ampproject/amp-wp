<?php

add_action('admin_head', 'amp_options_styles');
function amp_options_styles() {
	?>
	<style>
		.analytics-data-container #delete {
			background: red;
			border-color: red;
			text-shadow: 0 0 0;
			margin: 0 5px;
		}
	</style>;
	<?php
}