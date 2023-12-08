<?php

namespace ShortPixel\CriticalCSS\Cache;

use ComposePress\Core\Abstracts\Component;
use pcfreak30\WordPress\Cache\Store;
use ShortPixel\CriticalCSS\FileLog;

/**
 * Class Manager
 *
 * @package ShortPixel\CriticalCSS\Cache
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @property \ShortPixel\CriticalCSS $plugin
 */
class Manager extends Component {

	/**
	 * @var \pcfreak30\WordPress\Cache\Store
	 */
	private $store;


	/**
	 * Manager constructor.
	 *
	 * @param \pcfreak30\WordPress\Cache\Store $store
	 */
	public function __construct( Store $store ) {
		$this->store = $store;
	}


	/**
	 *
	 */
	public function init() {
		add_action(
			'after_switch_theme', [
				$this,
				'reset_web_check_transients',
			]
		);
		add_action(
			'upgrader_process_complete', [
				$this,
				'reset_web_check_transients',
			]
		);
		if ( ! ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			add_action(
				'shortpixel_critical_css_purge_cache', [
					$this,
					'reset_web_check_transients',
				]
			);
		}
		if (
				$this->plugin->settings_manager->get_setting( 'cache_mode' )
		        && isset( $this->plugin->settings_manager->get_setting( 'cache_mode' )['posts'] )
		) {
			add_action(
				'post_updated', [
					$this,
					'reset_web_check_post_transient',
				]
			);
			add_action(
				'edited_term', [
					$this,
					'reset_web_check_term_transient',
				]
			);
		}
		$this->store->set_prefix( $this->plugin->get_transient_prefix() );

		$interval = 0;
		if ( function_exists( 'get_rocket_purge_cron_interval' ) ) {
			$interval = get_rocket_purge_cron_interval();
		}
		$this->store->set_expire( apply_filters( 'shortpixel_critical_css_cache_expire_period', $interval ) );
		$this->store->set_max_branch_length( apply_filters( 'shortpixel_critical_css_max_branch_length', 50 ) );
	}

	/**
	 * @param array $path
	 *
	 * @return bool|mixed
	 */
	public function delete_cache_branch( $path = [] ) {
        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_CACHE) && FileLog::instance()->log("CCSS CACHE delete branch", $path);
		return $this->store->delete_cache_branch( $path );
	}

	/**
	 * @param array $path
	 *
	 * @return bool
	 */
	public function delete_cache_leaf( $path = [] ) {
		return $this->store->delete_cache_leaf( $path );
	}

	/**
	 * @param $path
	 *
	 * @return mixed
	 */
	public function get_cache_fragment( $path ) {
		return $this->store->get_cache_fragment( $path );
	}

	/**
	 * @param $path
	 * @param $value
	 */
	public function update_cache_fragment( $path, $value ) {
		return $this->store->update_cache_fragment( $path, $value );
	}

	/**
	 * @param $type
	 * @param $object_id
	 * @param $url
	 */
	public function purge_page_cache( $type = null, $object_id = null, $url = null ) {
		$url = preg_replace( '#nocache/$#', '', $url );
		do_action( 'shortpixel_critical_css_purge_cache', $type, $object_id, $url );
	}

	/**
	 *
	 */
	public function reset_web_check_transients($type = null, $object_id = null, $url = null) {
		(SPCCSS_DEBUG & FileLog::DEBUG_AREA_DB) && FileLog::instance()->log("reset_web_check_transients. STACK: ", debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));

		switch($type) {
			case 'post':
				$this->reset_web_check_post_transient($object_id);
				break;
			case 'term':
				$this->reset_web_check_term_transient($object_id);
				break;
		}
		//if called with any params, avoid overall clear
		if(is_null($type)) {
			$this->store->delete_cache_branch();
		}
	}

	/**
	 * Remove post meta cache
	 * @return void
	 */
	public function reset_post_meta_cache() {
		$table       = $this->plugin->log->get_table_name();
		$wpdb        = $this->plugin->wpdb;
		$name        = "{$this->plugin->get_safe_slug()}_cache";
		//$res = $wpdb->get_results( $wpdb->prepare( "SELECT object_id, type FROM {$table} WHERE type IN ('post', 'term', 'author') AND template IS NULL AND post_type IS NULL " ), ARRAY_A );
        $res = $wpdb->get_results( $wpdb->prepare( "SELECT object_id, type FROM {$table} WHERE type IN ('post', 'term', 'author')" ), ARRAY_A );
		foreach($res as $row) {
			switch($row['type']) {
				case 'post':
					delete_post_meta($row['object_id'], $name);
					break;
				case 'author':
					delete_user_meta($row['object_id'], $name);
					break;
				case 'term':
					delete_term_meta($row['object_id'], $name);
					break;
			}

		}
	}

	/**
	 * @param array $path
	 */

	/**
	 * @param $post
	 */
	public function reset_web_check_post_transient( $post ) {
		$post = get_post( $post );
		$hash = $this->plugin->data_manager->get_item_hash(
			[
				'object_id' => $post->ID,
				'type'      => 'post',
			]
		);
		$this->store->delete_cache_branch( [ 'webcheck', $hash ] );
	}

	/**
	 * @param $term
	 *
	 * @internal param \WP_Term $post
	 */
	public function reset_web_check_term_transient( $term ) {
		$term = get_term( $term );
		$hash = $this->plugin->data_manager->get_item_hash(
			[
				'object_id' => $term->term_id,
				'type'      => 'term',
			]
		);
		$this->store->delete_cache_branch( [ 'webcheck', $hash ] );
	}

	/**
	 *
	 */

	/**
	 * @internal param \WP_Term $post
	 */
	public function reset_web_check_home_transient() {
		$page_for_posts = get_option( 'page_for_posts' );
		if ( ! empty( $page_for_posts ) ) {
			$post_id = $page_for_posts;
		}
		if ( empty( $post_id ) || ( ! empty( $post_id ) && get_permalink( $post_id ) != site_url() ) ) {
			$page_on_front = get_option( 'page_on_front' );
			if ( ! empty( $page_on_front ) ) {
				$post_id = $page_on_front;
			} else {
				$post_id = false;
			}
		}
		if ( ! empty( $post_id ) && get_permalink( $post_id ) == site_url() ) {
			$hash = $this->plugin->data_manager->get_item_hash(
				[
					'object_id' => $post_id,
					'type'      => 'post',
				]
			);
		} else {
			$hash = $this->plugin->data_manager->get_item_hash(
				[
					'type' => 'url',
					'url'  => site_url(),
				]
			);
		}
		$this->store->delete_cache_branch( [ 'webcheck', $hash ] );
	}

	/**
	 * @return \pcfreak30\WordPress\Cache\Store
	 */
	public function get_store() {
		return $this->store;
	}
}
