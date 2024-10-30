<?php
class MC_Webnotes_Helper {
	public static function wp_query($args) {
		return new WP_Query( $args );
	}

	public static function header_status($statusCode) {
		static $status_codes = null;

		if ($status_codes === null) {
			$status_codes = array (
				100 => 'Continue',
				101 => 'Switching Protocols',
				102 => 'Processing',
				200 => 'OK',
				201 => 'Created',
				202 => 'Accepted',
				203 => 'Non-Authoritative Information',
				204 => 'No Content',
				205 => 'Reset Content',
				206 => 'Partial Content',
				207 => 'Multi-Status',
				300 => 'Multiple Choices',
				301 => 'Moved Permanently',
				302 => 'Found',
				303 => 'See Other',
				304 => 'Not Modified',
				305 => 'Use Proxy',
				307 => 'Temporary Redirect',
				400 => 'Bad Request',
				401 => 'Unauthorized',
				402 => 'Payment Required',
				403 => 'Forbidden',
				404 => 'Not Found',
				405 => 'Method Not Allowed',
				406 => 'Not Acceptable',
				407 => 'Proxy Authentication Required',
				408 => 'Request Timeout',
				409 => 'Conflict',
				410 => 'Gone',
				411 => 'Length Required',
				412 => 'Precondition Failed',
				413 => 'Request Entity Too Large',
				414 => 'Request-URI Too Long',
				415 => 'Unsupported Media Type',
				416 => 'Requested Range Not Satisfiable',
				417 => 'Expectation Failed',
				422 => 'Unprocessable Entity',
				423 => 'Locked',
				424 => 'Failed Dependency',
				426 => 'Upgrade Required',
				500 => 'Internal Server Error',
				501 => 'Not Implemented',
				502 => 'Bad Gateway',
				503 => 'Service Unavailable',
				504 => 'Gateway Timeout',
				505 => 'HTTP Version Not Supported',
				506 => 'Variant Also Negotiates',
				507 => 'Insufficient Storage',
				509 => 'Bandwidth Limit Exceeded',
				510 => 'Not Extended'
			);
		}
		if ($status_codes[$statusCode] !== null) {
			$status_string = $statusCode . ' ' . $status_codes[$statusCode];
			header($_SERVER['SERVER_PROTOCOL'] . ' ' . $status_string, true, $statusCode);
		}
	}

	private function get_admin_path() {
		$admin_path = str_replace( get_bloginfo( 'url' ) . '/', ABSPATH, get_admin_url() );
		$admin_path = apply_filters( array( 'MC_Webnotes_Helper', 'get_admin_path'), $admin_path );
		return $admin_path;
	}

	public static function generate_featured_image($image_url, $post_id) {
		$upload_dir = wp_upload_dir();
		$image_data = file_get_contents( $image_url );
		$filename   = basename( $image_url );
		if ( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}
		file_put_contents( $file, $image_data );
		$wp_filetype = wp_check_filetype( $filename, null );
		$attachment  = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);
		$attach_id   = wp_insert_attachment( $attachment, $file, $post_id );
		require_once( self::get_admin_path() . '/includes/image.php' );
		$attach_data  = wp_generate_attachment_metadata( $attach_id, $file );
		$res1         = wp_update_attachment_metadata( $attach_id, $attach_data );
		$post_meta_id = set_post_thumbnail( $post_id, $attach_id );
		return $post_meta_id;
	}

	public static function clean_array_data($data) {
		return array_map (function ($field_value) {
			if (is_array($field_value)) {
				return clean_array_data($field_value);
			} else {
				return wp_kses($field_value);
			}
		}, $data);
	}

	public static function get_post_data() {
		return filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
		//return filter_var_array (filter_input_array(INPUT_POST, FILTER_CALLBACK, array('options' => array('MC_Webnotes_Helper', 'clean_array_data'))), FILTER_SANITIZE_STRING);
	}

	public static function save_file($fileName, $fileData) {
		return file_put_contents( $fileName, $fileData );
	}
}
