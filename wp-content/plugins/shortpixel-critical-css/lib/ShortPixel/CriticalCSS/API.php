<?php

namespace ShortPixel\CriticalCSS;

use ComposePress\Core\Abstracts\Component;
use ShortPixel\CriticalCSS;

/**
 * Class API
 *
 * @property CriticalCSS $plugin
 */
class API extends Component {
	const STATUS_UNKNOWN = 'JOB_UNKNOWN';
	const STATUS_QUEUED = 'JOB_QUEUED';
	const STATUS_ONGOING = 'JOB_ONGOING';
	const STATUS_DONE = 'JOB_DONE';
	const STATUS_REMOVED = 'JOB_REMOVED';
	//const BASE_URL = 'https://stgcriticalapi.shortpixel.com/';
    const BASE_URL = 'https://api.criticalcss.co/';
    /**
	 * @var
	 */
	protected $api_key;

	/**
	 * CriticalCSS_API constructor.
	 *
	 * @param $api_key
	 */
	public function __construct( $api_key = null ) {
		$this->api_key = $api_key;
	}

	/**
	 * @return bool
	 */
	public function ping() {
		$response = $this->_send_request( 'get', 'ping' );
		$response = (array) $response;

		return empty( $response );
	}

	/**
	 * @param string $type
	 * @param string $endpoint
	 * @param bool   $auth
	 * @param array  $query_args
	 * @param array  $args
	 *
	 * @return array|bool|mixed|object
	 */
	private function _send_request( $type, $endpoint, $auth = true, $query_args = [], $args = [] ) {
		if ( empty( $this->api_key ) ) {
			return false;
		}
		$type = strtolower( $type );
		$func = "wp_remote_{$type}";
		if ( ! function_exists( $func ) ) {
			return false;
		}
		$api_url    = self::BASE_URL;
		$query_args = array_merge( $query_args, [
			'version' => $this->plugin->get_version(),
		] );
		$auth_args  = [];
		if ( $auth ) {
			$auth_args = [
				'headers' => [
					'Authorization' => 'JWT ' . $this->api_key,
				],
			];
			//$api_url   .= 'premium/';
		}
		$api_url  .= $endpoint;
		$all_args = array_merge_recursive( $auth_args, $args );
        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_API) && FileLog::instance()->log("API " . $type . " CALL. URL: " . $api_url . " ARGS:", $all_args);
        //(SPCCSS_DEBUG & FileLog::DEBUG_AREA_API) && FileLog::instance()->log("STACK:", debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));

        $response = $func( add_query_arg( $query_args, $api_url ), $all_args );
        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_API) && FileLog::instance()->log("API CALL RESPONSE: ", $response);

		if ( $response instanceof \WP_Error ) {
			return $response;
		}
		if ( is_array( $response ) ) {
			return json_decode( $response['body'] );
		}

		return false;
	}

	/**
	 * @param $item_id
	 *
	 * @return array|bool|mixed|object
	 */
	public function get_result( $item_id ) {
		return $this->_send_request( 'get', 'results', true, [
			'resultId' => $item_id,
		] );
	}

	/**
	 * @param array $item
	 *
	 * @return array|bool|mixed|object
	 */
	public function generate( array $item ) {
        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_API) && FileLog::instance()->log("Called Generate");
        //when manually ran from api_run, it throws a fatal: "Uncaught Exception: Plugin property is equal to self. Did you forget to set the parent or create a getter?"
		$force_styles    = $this->plugin->settings_manager->get_setting( 'force_include_styles' );
		$force_selectors = [];
		if ( ! empty( $force_styles ) ) {
			$lines = explode( "\n", $force_styles );
			$lines = array_map( 'trim', $lines );
			foreach ( $lines as $index => $line ) {
				$selector = [ 'value' => $line ];
				if ( preg_match( '/^\/.*?\/[gimy]*$/', $line ) ) {
					$selector['type'] = 'RegExp';
				}
				$force_selectors[] = $selector;
			}
		}
		$response = $this->_send_request( 'post', 'generate', true, [], [
			'body' => [
				'height'                   => 900,
				'width'                    => 1300,
				'url'                      => $this->plugin->get_permalink( $item ),
				'aff'                      => 3,
				'version'                  => $this->plugin->get_version(),
				'wpCriticalCssQueueLength' => $this->plugin->api_queue->get_length(),
				'forceInclude' => $force_selectors,
			],
		] );

		return $response instanceof \WP_Error ? $response : $response->job;
	}

	public function get_invalid_url_regexes() {

		$cache_name = CriticalCSS::LANG_DOMAIN . '_invalid_urls';
		$cache      = $this->plugin->cache_manager->get_store()->get_transient( $cache_name );

		if ( empty( $cache ) ) {
			$response = $this->_send_request( 'get', 'invalid-generate-url-rules', false );
			$cache    = $response->rules;
			$this->plugin->cache_manager->get_store()->set_transient( $cache_name, $cache, WEEK_IN_SECONDS * 2 );
		}

		return $cache;
	}

	/**
	 *
	 */
	public function init() {
		// noop
	}

	/**
	 * @param mixed $api_key
	 */
	public function set_api_key( $api_key ) {
		$this->api_key = $api_key;
	}

	/**
	 * @return mixed
	 */
	public function get_api_key() {
		return $this->api_key;
	}
}
