<div class="wrap">

	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="post" action="<?php echo esc_html( admin_url( 'admin-post.php' ) ); ?>">

		<div id="analytics-data-container">
			<h2>Analytics Component</h2>
			<div class="options">
				<p>
					<label>Type: </label>
					<input class="option-input" type="text" name=vendor-type value="" />
					<label>Id: </label>
					<input type="text" name=id value="" />
				</p>
				<p>
					<label>JSON Configuration:</label>
					<br />
					<textarea rows="10" cols="100" name="config"></textarea>
				</p>
                <input type="hidden" name="action" value="analytics_options">
			</div><!-- #analytics-data-container -->
			<?php
			submit_button();
			?>

	</form>

</div><!-- .wrap -->