<?php defined( 'ABSPATH' ) || exit; ?>

<fieldset>
	<legend class="screen-reader-text">
		<span><?php echo esc_html( $this->title ); ?></span>
	</legend>
	
	<label for="<?php echo esc_attr( $args[ 'label_for' ] ); ?>">
		<?php printf( '<input name="ymsk-enabled-utilities[]" type="checkbox" id="%s" value="%s" %s>',
			esc_attr( $args[ 'label_for' ] ),
			esc_attr( $this->slug ),
			checked( $args[ 'is_checked' ], true, false ),
		); ?>

		<?php echo esc_html( $this->description ); ?>
	</label>
</fieldset>