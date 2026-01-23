<?php defined( 'ABSPATH' ) || exit; ?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<form method="POST" action="options.php">
		<?php
			settings_fields( 'ym-site-kit' );
			do_settings_sections( 'ym-site-kit' );
			submit_button();
		?>
	</form>
</div>