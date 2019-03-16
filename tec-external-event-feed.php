<?php
/*
Plugin Name: The Events Calendar External Events Feed
Description: Display a list of upcoming events from an external site.
Version: 0.1
Author: Roundup WP
Author URI: roundupwp.com
Text Domain: ru-ext-events
*/

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! class_exists( 'RU_EEF' ) ) :

	final class RU_EEF {
		/** Singleton *************************************************************/
		/**
		 * @var RU_EEF
		 * @since 1.0
		 */
		private static $instance;

		/**
		 * Main RU_EEF Instance.
		 *
		 * Only on instance of the form and functions at a time
		 *
		 * @since 1.0
		 * @return object|RU_EEF
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof RU_EEF ) ) {
				self::$instance = new RU_EEF;
				self::$instance->constants();
				self::$instance->includes();
			}
			return self::$instance;
		}
		/**
		 * Throw error on object clone.
		 *
		 * @since 1.0
		 * @return void
		 */
		public function __clone() {
			// Cloning instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ru-ext-events' ), '1.0' );
		}
		/**
		 * Disable unserializing of the class.
		 *
		 * @since 1.0
		 * @access protected
		 * @return void
		 */
		public function __wakeup() {
			// Unserializing instances of the class is forbidden.
			_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'ru-ext-events' ), '1.0' );
		}

		/**
		 * Setup plugin constants.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function constants() {
			// Plugin version.
			if ( ! defined( 'RU_EEF_VERSION' ) ) {
				define( 'RU_EEF_VERSION', '0.1' );
			}
			// Plugin Folder Path.
			if ( ! defined( 'RU_EEF_PLUGIN_DIR' ) ) {
				define( 'RU_EEF_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
			}
			// Plugin Folder Path.
			if ( ! defined( 'RU_EEF_PLUGIN_URL' ) ) {
				define( 'RU_EEF_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
			}
			// Plugin Base Name
			if ( ! defined( 'RU_EEF_PLUGIN_BASENAME') ) {
				define( 'RU_EEF_PLUGIN_BASENAME', plugin_basename(__FILE__) );
			}
		}
		/**
		 * Include required files.
		 *
		 * @access private
		 * @since 1.0
		 * @return void
		 */
		private function includes() {
			require_once RU_EEF_PLUGIN_DIR . 'inc/class-ru-tribe-events-rest-connect.php';
			require_once RU_EEF_PLUGIN_DIR . 'inc/class-ru-tribe-events-feed.php';

		}
	}
endif; // End if class_exists check.

function RU_EEF() {
	return RU_EEF::instance();
}
// Get RU_EEF Running.
RU_EEF();


function ru_eef_text_domain() {
	load_plugin_textdomain( 'ru-ext-events', false, basename( dirname(__FILE__) ) . '/languages' );
}
add_action( 'plugins_loaded', 'ru_eef_text_domain' );


add_shortcode( 'ru-event-list', 'ru_eef_list_events' );
function ru_eef_list_events( $atts ) {
	wp_enqueue_script( 'ru_eef_js' );
	wp_enqueue_style( 'ru_eef_css' );

	$date_format = get_option( 'date_format' );
	$time_format = get_option( 'time_format' );
	$date_time_format = $date_format . ' ' . $time_format;
	$list_template = RU_EEF_PLUGIN_DIR . 'templates/list.php';
	$single_template = RU_EEF_PLUGIN_DIR . 'templates/single-event.php';

	$params = array();
	$params['per_page'] = 100;

	$cache_name_cat = '';
	if ( ! empty( $atts['categories'] ) ) {
		$params['categories'] = $atts['categories'];
		$cache_name_cat = trim( substr( $params['categories'], 0, 5 ) );
	}

	$cache_name_auth = '';
	if ( ! empty( $atts['author'] ) ) {
		$cache_name_auth = trim( substr( $atts['author'], 0, 5 ) );
	}

	$website = isset( $atts['website'] ) ? trim( $atts['website'] ) : '';

	if ( empty( $website ) ) {
	    return '';
    }

	$cache_name = substr( $website, 0, 15 ) . $cache_name_cat . $cache_name_auth;

	$cache = get_transient( $cache_name );

	if ( ! $cache ) {
		$posts_api_connect = new RU_Tribe_Events_REST_Connect( $website, 'events', $params );
		$posts_api_connect->connect();
		$response_body = $posts_api_connect->get_response_body();
		$response_body_events = isset( $response_body->events ) ? $response_body->events : array();
    } else {
		$response_body_events = $cache;
    }

	if ( ! empty( $response_body_events ) ) {
		$event_feed = new RU_EEF_Feed( $response_body_events, $atts );
		$event_feed->apply_filters();
		$filtered_events = $event_feed->get_events();

		if ( is_array( $filtered_events ) && count( $filtered_events ) < 10 && count( $response_body->events ) > $params['per_page'] - 1 ) {
			$params['page'] = 2;
			$posts_api_connect_2 = new RU_Tribe_Events_REST_Connect( $website, 'events', $params );
			$posts_api_connect_2->connect();
			$response_body_2 = $posts_api_connect_2->get_response_body();

			$event_feed_2 = new RU_EEF_Feed( $response_body_2->events, $atts );
			$event_feed_2->apply_filters();
			$filtered_events_2 = $event_feed_2->get_events();

			$filtered_events = array_merge( $filtered_events, $filtered_events_2 );
		}

		set_transient( $cache_name, $filtered_events, 60 * 60 * 3 );

		ob_start();
		
		include $list_template;
		$html = ob_get_contents();
		ob_end_clean();

		return $html;
	} else {
	    return '<p>No upcoming events right now. Please check back soon!</p>';
    }


}

function ru_eef_scripts_and_styles() {
	wp_register_script( 'ru_eef_js', trailingslashit( RU_EEF_PLUGIN_URL ) . 'assets/ru-eef.js', array( 'jquery' ), RU_EEF_VERSION, true );
	wp_register_style( 'ru_eef_css', trailingslashit( RU_EEF_PLUGIN_URL ) . 'assets/ru-eef.css', array(), RU_EEF_VERSION );
}
add_action( 'wp_enqueue_scripts', 'ru_eef_scripts_and_styles' );