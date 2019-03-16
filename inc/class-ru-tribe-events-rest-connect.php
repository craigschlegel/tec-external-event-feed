<?php
/**
 * Class RU_Tribe_Events_REST_Connect
 *
 * Connects to WordPress sites that are using The Events Calendar's
 * REST API
 *
 * @since 1.0
 */
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class RU_Tribe_Events_REST_Connect {

	private $url;
	private $endpoint;
	private $response_status;
	private $response_headers;
	private $response_body;
	private $response_error;

	public function __construct( $url_input, $endpoint, $params = array() )
	{
		$parsed_url = parse_url( $url_input );
		$scheme = 'http';
		$host = '';
		$path = '/wp-json/tribe/events/v1/';
		$this->endpoint = $endpoint;
		$formatted_param_string = $this->get_formatted_params( $params );
		if ( ! empty( $parsed_url['scheme'] ) ) {
			$scheme = $parsed_url['scheme'];
		}
		if ( ! empty( $parsed_url['host'] ) ) {
			$host = $parsed_url['host'];
		} else {
			$host = $url_input;
		}
		$url = $scheme . '://' . $host . $path . trailingslashit( $endpoint ) . $formatted_param_string;
		$this->url = $url;
	}

	public function connect()
	{
		$response = wp_remote_get( $this->url );
		if ( is_wp_error( $response ) ) {
			$this->response_body = array();
			$this->response_status = 'error';
			$error_message = false;
			foreach( $response->errors as $error ) {
				if ( $error_message === false ) {
					$error_message = $error[0];
				}
			}
			$this->response_error = $error_message;
			$ru_eef_report = get_option( 'ru_eef_report', array() );
			$ru_eef_report[ sanitize_text_field( $this->url ) ]['wp_error'] = array(
				'time' => time(),
				'wp_error' => $response->errors
			);
			update_option( 'ru_eef_report', $ru_eef_report, false );
		} elseif ( $response['response']['code'] == 200 ) {
			$this->response_status = 200;
			$this->response_body = json_decode( $response['body'] );
			$this->response_headers = $response['headers'];
			$this->response_error = $response['response']['message'];
		} else {
			$this->response_body = array();
			$this->response_status = $response['response']['code'];
			$this->response_error = $response['response']['message'];
			$ru_eef_report = get_option( 'ru_eef_report', array() );
			$ru_eef_report[ sanitize_text_field( $this->url ) ]['not_two_hundred'] = array(
				'time' => time(),
				'code' => $response['response']['code'],
				'message' => $response['response']['message']
			);
			update_option( 'ru_eef_report', $ru_eef_report, false );
		}
	}

	public function is_successful_connection()
	{
		if ( isset( $this->response_status ) && $this->response_status === 200 ) {
			return true;
		}
		return false;
	}

	public function get_response_body()
	{
		return $this->response_body;
	}

	public function get_response_headers()
	{
		if ( isset( $this->response_headers ) ) {
			return $this->response_headers;
		}
		return false;
	}

	public function get_response_error()
	{
		if ( isset( $this->response_error ) ) {
			return $this->response_error;
		}
		return false;
	}

	public function get_url()
	{
		return $this->url;
	}

	private function get_formatted_params( $params ) {
		if ( ! empty ( $params ) ) {
			return '?' . http_build_query( $params );
		} else {
			return '';
		}
	}

}