<?php

defined( 'ABSPATH' ) || exit;

class YMSK_Logger extends YMSK_Utility {
	/**
	 * Returns list of allowed file names.
	 * 
	 * @return string[]
	 */
	public static function get_allowed_files () : array {
		return apply_filters( 'ymsk_logger_allowed_files', [
			'common',
		]);
	}

	/**
	 * Returns `true` if this file name allowed.
	 *
	 * @param string $file_name File name without extension. Example: `common`.
	 *
	 * @return bool
	 */
	public static function is_file_allowed ( string $file_name ) : bool {
		return in_array( $file_name, self::get_allowed_files() );
	}

	/**
	 * Returns log files extension.
	 *
	 * @return string
	 */
	public static function get_file_extension () : string {
		return apply_filters( 'ymsk_logger_file_extension', 'txt' );
	}

	/**
	 * Returns log file path.
	 * 
	 * @param string $file_name File name without extension. Example: `common`.
	 * 
	 * @return string
	 */
	public static function get_file_path ( string $file_name ) {
		return WP_CONTENT_DIR . "/uploads/logs/ymsk-{$file_name}." . self::get_file_extension();
	}

	/**
	 * Returns log file URI.
	 * 
	 * @param string $file_name File name without extension. Example: `common`.
	 * 
	 * @return string
	 */
	public static function get_file_uri ( string $file_name ) {
		return WP_CONTENT_URL . "/uploads/logs/ymsk-{$file_name}." . self::get_file_extension();
	}


	/**
	* Writes a new line to the log file.
	 *
	 * @param string $data Data to write.
	 * @param string $file File name without extension.
	 *
	 * @return bool Whether the line was successfully written.
	 */
	public static function write ( string $data, string $file = 'common' ) : bool {
		if ( ! self::is_file_allowed( $file ) ) {
			return false;
		}

		$log_dir_path  = WP_CONTENT_DIR . '/uploads/logs/';
		$log_file_path = self::get_file_path( $file );
		$max_lines     = apply_filters( 'ymsk_logger_max_lines', 1000 );

		// Create folder if not exists.
		if ( ! file_exists( $log_dir_path ) && ! wp_mkdir_p( $log_dir_path ) ) {
			return false;
		}

		// Form new line.
		$datetime_string = ( function_exists( 'wp_date' ) ? 'wp_date' : 'current_time' )( 'Y-m-d H:i:s' );
		$new_line        = sprintf( '[%s]: %s', $datetime_string, $data );

		// Read existing lines.
		$lines = [];

		if ( file_exists( $log_file_path ) ) {
			$lines = file( $log_file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

			if ( ! $lines ) {
				$lines = [];
			}
		}

		// Add new line to start.
		array_unshift( $lines, $new_line );

		// Limit lines.
		if ( count( $lines ) > $max_lines ) {
			$lines = array_slice( $lines, 0, $max_lines );
		}

		// Write lines back.
		$content = implode( PHP_EOL, $lines ) . PHP_EOL;

		return false !== file_put_contents( $log_file_path, $content, LOCK_EX );
	}

	/**
	 * Retrieves log file lines.
	 *
	 * @param string $file File name without extension.
	 *
	 * @return array
	 */
	public static function read ( string $file = 'common' ) : array {
		if ( ! self::is_file_allowed( $file ) ) {
			return [];
		}

		$log_file_path = self::get_file_path( $file );

		// Read lines.
		$lines = [];

		if ( file_exists( $log_file_path ) ) {
			$lines = file( $log_file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES );

			if ( ! $lines ) {
				$lines = [];
			}
		}

		return $lines;
	}
}

new YMSK_Logger( 'logger', [
	'is_beta'      => true,
	'is_permanent' => true,
	'section'      => 'permanent',
	'title'        => _x( 'Logger', 'Utility Title', 'ym-site-kit' ),
	'description'  => sprintf( '%s %s %s',
		/* translators: %s – YMSK_Logger */
		sprintf( __( 'Provides built-in functionality for simple data logging via the %s class.', 'ym-site-kit' ),
			'<code>YMSK_Logger</code>',
		),
		sprintf( '<a href="%s" target="_blank">%s</a>.',
			esc_url( 'https://yanmet.com/blog/ym-site-kit-wordpress-plugin-documentation#logger' ),
			__( 'Learn more', 'ym-site-kit' ),
		),
		sprintf( '<br>%s: %s.',
			__( 'View logs', 'ym-site-kit' ),
			implode( ', ', array_map( function ( string $file_name ) {
				return sprintf( '<a href="%s" target="_blank">%s</a>',
					YMSK_Logger::get_file_uri( $file_name ),
					$file_name,
				);
			}, YMSK_Logger::get_allowed_files() ) ),
		),
	),
	'callback' => function () {
		// Adds disallow rule for logs directory.
		add_filter( 'robots_txt', function ( string $output, bool $public ) : string {
			if ( ! $public ) {
				return $output;
			}

			if ( false === strpos( $output, 'Disallow: /wp-content/uploads/logs' ) ) {
				$output = str_ireplace( "User-agent: *", "User-agent: *\nDisallow: /wp-content/uploads/logs", $output );
			}

			return $output;
		}, 1000, 2 );
	},
]);