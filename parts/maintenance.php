<?php defined( 'ABSPATH' ) || exit; ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php echo esc_attr( get_option( 'blog_charset' ) ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title><?php echo esc_html__( 'Maintenance', 'ym-site-kit' ); ?></title>
	</head>

	<body>
		<?php echo wp_kses_post(
			apply_filters( 'ymsk_maintenance_mode_content',
				sprintf( "<h1>%s</h1>\n<p>%s</p>",
					__( 'Maintenance', 'ym-site-kit' ),
					__( 'The site is currently undergoing maintenance. Please check back later.', 'ym-site-kit' ),
				)
			)
		); ?>
	</body>
</html>