<?php
	defined( 'ABSPATH' ) || exit;

	if ( get_settings_errors() ) {
		do_action( 'ymsk_utilities_saved' );
	}
?>

<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php settings_errors(); ?>

	<form method="POST" action="options.php">
		<?php
			settings_fields( 'ymsk-settings' );
			do_settings_sections( 'ymsk-utilities' );
			submit_button();
		?>
	</form>
</div>