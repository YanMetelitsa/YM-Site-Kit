<?php defined( 'ABSPATH' ) || exit; ?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="POST" action="options.php">
		<?php
			settings_fields( 'ymsk-settings' );
			do_settings_sections( 'ymsk-utilities' );
			submit_button();
		?>
	</form>
</div>