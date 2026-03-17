<?php defined( 'ABSPATH' ) || exit; ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php echo esc_attr( get_option( 'blog_charset' ) ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<title><?php echo esc_html__( 'Maintenance', 'ym-site-kit' ); ?></title>

		<style>
			:root {
				--ymsk-maintenance-color-white: #fbfbfb;
				--ymsk-maintenance-color-gray:  #7e7e7e;
				--ymsk-maintenance-color-black: #1f1f1f;

				--ymsk-maintenance-color-background: var( --ymsk-maintenance-color-white );
				--ymsk-maintenance-color-text:       var( --ymsk-maintenance-color-black );
			}

			@media ( prefers-color-scheme: dark ) {
				:root {
					--ymsk-maintenance-color-background: var( --ymsk-maintenance-color-black );
					--ymsk-maintenance-color-text:       var( --ymsk-maintenance-color-white );
				}
			}

			* {
				box-sizing: border-box;
			}

			body {
				background-color: var( --ymsk-maintenance-color-background );
				margin: 0;

				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
				color: var( --ymsk-maintenance-color-text );
			}

			main {
				height: 100svh;

				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
			}

			section {
				width: 600px;
				max-width: 90svw;
				padding: 2.5em;
				border: 1px solid var( --ymsk-maintenance-color-gray );
				border-radius: 10px;

				display: flex;
				flex-direction: column;
				gap: 1em;

				text-align: center;
			}

			h1 {
				margin: 0;

				font-size: 1.5em;
			}

			p {
				margin: 0;

				font-size: 1em;
			}
		</style>
	</head>

	<body>
		<main>
			<section>
				<?php echo wp_kses_post(
					apply_filters( 'ymsk_maintenance_mode_content',
						sprintf( "<h1>%s</h1>\n<p>%s</p>",
							__( 'Maintenance', 'ym-site-kit' ),
							__( 'The site is currently undergoing maintenance. Please check back later.', 'ym-site-kit' ),
						)
					)
				); ?>
			</section>
		</main>
	</body>
</html>