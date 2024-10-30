<?php
/*----------------------------------------------------------------------------------------------------------------------
Plugin Name: MC Web Notes
Description: Allows logged in users to annotate site pages and posts.
Version: 0.3.3
Author: Miguel Calderón
Author URI: https://github.com/miguelcalderon
Plugin URI: 
----------------------------------------------------------------------------------------------------------------------*/
if (!function_exists('is_admin')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}
defined('ABSPATH') or die("No script kiddies please!");
define( 'MC_WEB_NOTES_VERSION', '0.3.3' );
define( 'MC_WEB_NOTES_RELEASE_DATE', date_i18n( 'F j, Y' ) );
define( 'MC_WEB_NOTES_DIR', plugin_dir_path( __FILE__ ) );
define( 'MC_WEB_NOTES_URL', plugin_dir_url( __FILE__ ) );

class MC_Webnotes {
	public static function init() {
		if (is_user_logged_in()) {
			self::load_languages();
			$labels = array(
				'name'                => _x( 'Web Notes', 'Post Type General Name' ),
				'singular_name'       => _x( 'Web Note', 'Post Type Singular Name' ),
				'menu_name'           => __( 'Web Notes', 'mc-webnotes' ),
				'parent_item_colon'   => __( 'Parent Web Note', 'mc-webnotes' ),
				'all_items'           => __( 'All Web Notes', 'mc-webnotes' ),
				'view_item'           => __( 'View Web Note', 'mc-webnotes' ),
				'add_new_item'        => __( 'Add New Web Note', 'mc-webnotes' ),
				'add_new'             => __( 'Add New', 'mc-webnotes' ),
				'edit_item'           => __( 'Edit Web Note', 'mc-webnotes' ),
				'update_item'         => __( 'Update Web Note', 'mc-webnotes' ),
				'search_items'        => __( 'Search Web Note', 'mc-webnotes' ),
				'not_found'           => __( 'Not Found', 'mc-webnotes' ),
				'not_found_in_trash'  => __( 'Not found in Trash', 'mc-webnotes' ),
			);
			$args = array(
				'label'               => __( 'webnote' ),
				'description'         => __( 'Annotations to web content, layout, format, etc', 'mc-webnotes' ),
				'labels'              => $labels,
				'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
				'taxonomies'          => array( '' ),
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'show_in_nav_menus'   => true,
				'show_in_admin_bar'   => true,
				'menu_position'       => 5,
				'show_in_rest'        => true,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capability_type'     => 'post',
			);
			register_post_type( 'webnotes', $args );
			$annotate = $_COOKIE['webnotes_annotate'];;
			if ($annotate) {
				add_action( 'wp_enqueue_scripts', array('MC_Webnotes', 'assets' ));
				include_once 'classes/MC_Webnotes_Helper.php';
				include_once 'classes/MC_Webnotes_Webnote.php';
				add_action( 'wp_ajax_webnotes_savenote', array('MC_Webnotes_Webnote', 'save') );
				add_action( 'wp_ajax_nopriv_webnotes_savenote', array('MC_Webnotes_Webnote', 'save') );
				add_action( 'wp_ajax_webnotes_getnotes', array('MC_Webnotes_Webnote', 'get_ajax') );
				add_action( 'wp_ajax_nopriv_webnotes_getnotes', array('MC_Webnotes_Webnote', 'get_ajax') );
				add_action( 'wp_ajax_webnotes_deletenote', array('MC_Webnotes_Webnote', 'delete') );
				add_action( 'wp_ajax_nopriv_webnotes_deletenote', array('MC_Webnotes_Webnote', 'delete') );
				if (is_admin()) {
					add_action( 'save_post', array('MC_Webnotes', 'save_meta_box' ));
					add_action( 'add_meta_boxes', array('MC_Webnotes', 'register_meta_boxes'));
				}
			}
			add_action( 'admin_bar_menu', array('MC_Webnotes', 'admin_toolbar_item'), 999 );
		}
	}
	private static function load_languages() {
		load_plugin_textdomain('mc-webnotes', false, basename( dirname( __FILE__ ) ) . '/languages' );
	}
	public static function assets() {
		global $post;
		if (is_user_logged_in()) {
			$annotate = $_COOKIE['webnotes_annotate'];
			if ($annotate) {
				self::load_languages();
				$url = get_permalink( $post);
				wp_enqueue_style('webnotes', plugins_url('css/mc-webnotes.css', __FILE__));
				wp_enqueue_script('webnotes-domtoimage', plugins_url('third-party/js/dom-to-image.min.js', __FILE__), array(), '1.0.0', true);
				wp_enqueue_script('webnotes', plugins_url('js/mc-webnotes.js', __FILE__), array('jquery'), '1.0.0', true);
				wp_localize_script( 'webnotes', 'MC_Webnotes_Data', array(
					'initialState' => MC_Webnotes_Webnote::get($url),
					'objectL10n' => array(
						'author' => esc_html__('Author', 'mc-webnotes'),
						'viewAllSiteNotes' => esc_html__('View all site notes', 'mc-webnotes'),
						'addNote' => esc_html__('Add note', 'mc-webnotes'),
						'noteTitle' => esc_html__('Note title', 'mc-webnotes'),
						'noteText' => esc_html__('Note text', 'mc-webnotes'),
						'saveNote' => esc_html__('Save note', 'mc-webnotes'),
						'annotator' => esc_html__('Annotator', 'mc-webnotes'),
						'annotationReady' => esc_html__('Annotation ready!', 'mc-webnotes'),
					)
				));
			}
		}
	}
	public static function admin_toolbar_item($wp_admin_bar) {
		if (is_user_logged_in()) {
			$args = array(
				'id'    => 'webnotes_adminbar-item',
				'href'  => '/',
			);
			$annotate = $_COOKIE['webnotes_annotate'];
			if ($annotate) {
				$args['title'] = esc_html__('Turn annotation mode off', 'mc-webnotes');
			} else {
				$args['title'] = esc_html__('Turn annotation mode on', 'mc-webnotes');
			}
			$wp_admin_bar->add_node($args);
			wp_enqueue_script('webnotes-adminbar', plugins_url('js/mc-webnotes-adminbar.js', __FILE__), array('jquery'), '1.0.0', true);
			wp_localize_script( 'webnotes-adminbar', 'admin_objectL10n', array(
				'turnAnnotationModeOn' => esc_html__('Turn annotation mode on', 'mc-webnotes'),
				'turnAnnotationModeOff' => esc_html__('Turn annotation mode off', 'mc-webnotes'),
			));
		}
	}
	public static function register_meta_boxes() {
		add_meta_box('mc-webnotes_notes', __('Post notes', 'mc-webnotes'), array('MC_Webnotes', 'display_meta_boxes'));
	}
	public static function display_meta_boxes( $post ) {
		// Save logic goes here. Don't forget to include nonce checks!
		$url = get_permalink( $post);
		$result = MC_Webnotes_Webnote::get($url);
		$output = '';
		if (count($result['notes']) > 0) {
			$output.= '<table>';
			$output.= '<thead>';
			$output.= '<th>'.__('Note title', 'mc-webnotes').'</th>';
			$output.= '<th>'.__('Note text', 'mc-webnotes').'</th>';
			$output.= '<th>'.__('Author', 'mc-webnotes').'</th>';
			$output.= '</thead>';
			$output.= '<tbody>';
			foreach ($result['notes'] as $note) {
				$output.= '<tr>';
				$output.= '<td>'.$note['title'].'</td>';
				$output.= '<td>'.$note['text'].'</td>';
				$output.= '<td>'.$note['author'].'</td>';
				$output.= '</tr>';
			}
			$output.= '</tbody>';
			$output.= '</table>';
			echo $output;
		}
	}
	public static function save_meta_box( $post_id ) {
		// Not used yet
		// Save logic goes here. Don't forget to include nonce checks!
	}
}
add_filter( 'init', array('MC_Webnotes', 'init') );

