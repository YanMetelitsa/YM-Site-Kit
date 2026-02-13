<?php defined( 'ABSPATH' ) || exit; ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php echo esc_attr( get_option( 'blog_charset' ) ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<title><?php echo esc_html__( 'Maintenance', 'ym-site-kit' ); ?></title>
	</head>

	<body>
		<h1><?php echo esc_html__( 'Maintenance', 'ym-site-kit' ); ?></h1>
		<p><?php echo esc_html__( 'We are currently performing maintenance on the website. Please check back later.', 'ym-site-kit' ); ?></p>
	</body>
</html>