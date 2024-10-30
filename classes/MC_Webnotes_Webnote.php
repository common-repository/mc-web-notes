<?php
class MC_Webnotes_Webnote {
	public static function get_ajax() {
		$post_data = MC_Webnotes_Helper::get_post_data();
		$result = self::get($post_data['url']);
		$output = json_encode( $result );
		echo $output;
		wp_die();
		return $output;
	}
	public static function get($url) {
		$args   = array(
			'post_status' => 'publish',
			'post_type'   => 'webnotes',
			'posts_per_page' => -1,
			'meta_query'  => array(
				array(
					'key'   => 'url',
					'value' => $url,
				)
			)
		);
		$notes  = MC_Webnotes_Helper::wp_query( $args );
		$author = get_currentuserinfo();
		$result = array( 'notes' => array(), 'author' => $author->user_login, 'url' => $url );
		if ( $notes->have_posts() ) {
			while ( $notes->have_posts() ) {
				$notes->the_post();
				$note_author            = get_userdata( intval( $notes->post->post_author ) );
				$context = get_metadata('post', $notes->post->ID);
				$result['notes'][] = array(
					'note_id' => $notes->post->ID,
					'url'     => $url,
					'text'    => $notes->post->post_content,
					'title'   => $notes->post->post_title,
					'author'  => $note_author->user_login,
					'context' => array(
						'left' => $context['left'][0],
						'top'  => $context['top'][0],
						'user_agent' => $context['user_agent'][0],
						'viewport_width' => $context['viewport_width'][0],
						'viewport_height' => $context['viewport_height'][0],
					),
				);
			}
		}
		wp_reset_postdata();
		return $result;
	}

	public static function save() {
		global $user_ID;
		$post_data = MC_Webnotes_Helper::get_post_data();
		get_currentuserinfo();
		$new_note = array(
			'post_content' => $post_data['note']['text'],
			'post_status'  => 'publish',
			'post_author'  => $user_ID,
			'post_type'    => 'webnotes',
			'post_title'   => $post_data['note']['title'],
			'meta_input'   => array(
				'url'  => $post_data['note']['url'],
				'left' => $post_data['note']['context']['left'],
				'top'  => $post_data['note']['context']['top'],
				'user_agent' => $post_data['note']['context']['user_agent'],
				'viewport_width'  => $post_data['note']['context']['viewport_width'],
				'viewport_height' => $post_data['note']['context']['viewport_height'],
			),
		);
		$result   = wp_insert_post( $new_note, true );
		if ( is_wp_error( $result ) ) {
			MC_Webnotes_Helper::header_status( '500' );
			echo $result->get_error_message();
		} else {
			$encodedImage1 = str_replace( ' ', '+', $post_data['note']['context']['image'] );
			$encodedImage2 = substr( $encodedImage1, strpos( $encodedImage1, ',' ) + 1 );
			$decodedJpeg   = base64_decode( $encodedImage2 );
			$imageName     = time() . '_' . $result . '_' . $user_ID . '.png';
			$wp_upload_dir = wp_upload_dir();
			MC_Webnotes_Helper::save_file( $wp_upload_dir['path'] . '/' . $imageName, $decodedJpeg );
			MC_Webnotes_Helper::generate_featured_image( $wp_upload_dir['url'] . '/' . $imageName, $result );
			echo $result;
		}
		wp_die();
		return $result;
	}

	public static function delete() {
		$post_data = MC_Webnotes_Helper::get_post_data();
		$note = get_post($post_data['note_id']);
		if ($note->post_type == 'webnotes') {
			if( has_post_thumbnail( $note->ID) ) {
				$attachment_id = get_post_thumbnail_id( $note->ID );
				wp_delete_attachment( $attachment_id );
			}
			wp_delete_post( $post_data['note_id'] );
		}
		echo $post_data['note_id'];
		wp_die();
		return $post_data['note_id'];
	}
}
