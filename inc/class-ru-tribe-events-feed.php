<?php
// Don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

class RU_EEF_Feed {

	private $posts;

	private $filtered_out;

	private $settings;

	public function __construct( $posts, $settings )
	{
		$this->posts = $posts;
		$this->settings = $settings;
	}

	public function apply_filters() {
		$author_filter = isset( $this->settings['author'] ) ? (int)$this->settings['author'] : false;
		$filtered_out = array();
		if ( ! empty( $author_filter ) ) {
			$filtered_events = array();
			foreach ( $this->posts as $event ) {

				if ( (int)$event->author === $author_filter ) {
					$filtered_events[] = $event;
				} else {
					$filtered_out[] = $event;
				}

			}

			$this->posts = $filtered_events;

		}

		$this->filtered_out = $filtered_out;
	}

	public function get_events() {
		return $this->posts;
	}

}