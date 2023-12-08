<?php

namespace ShortPixel\CriticalCSS\API\Background;

use ShortPixel\CriticalCSS\API;
use ShortPixel\CriticalCSS\FileLog;
use \ShortPixel\CriticalCSS\Background\ProcessAbstract;

/**
 * Class Process
 *
 * @package ShortPixel\CriticalCSS\API\Background
 */
class Process extends ProcessAbstract {
	protected $action = 'shortpixel_critical_css_api';
	private $ping_checked = false;
	/**
	 * @var \ShortPixel\CriticalCSS\API
	 */
	private $api;

	/**
	 * Process constructor.
	 *
	 * @param \ShortPixel\CriticalCSS\API $api
	 */
	public function __construct( API $api ) {
		$this->api = $api;
		parent::__construct();
	}

	/**
	 * Task
	 *
	 * Override this method to perform any actions required on each
	 * queue item. Return the modified item for further processing
	 * in the next pass through. Or, return false to remove the
	 * item from the queue.
	 *
	 * @param mixed $item Queue item to iterate over
	 *
	 * @return mixed
	 */
	public function task( $item, $return_job_done = false ) {
		if ( null === $this->api->parent ) {
			$this->api->parent = shortpixel_critical_css();
		}
		$settings = shortpixel_critical_css()->settings_manager->settings;

		if ( empty( $settings ) ) {
			return false;
		}

		//use beta api key if it is not entered in settings
		if( empty( $settings['apikey'] ) ) {
			$this->api->api_key = $this->api->parent->beta_api_key;
		} else {
			$this->api->api_key = $settings['apikey'];
		}

		if ( ! empty( $item['timestamp'] ) && $item['timestamp'] + 8 >= time() ) {
			return $item;
		}
		if ( ! $this->ping_checked ) {
			if ( $this->api->ping() ) {
				$this->ping_checked = true;
			} else {
				return false;
			}
		}
		$item['timestamp'] = time();
		$url               = shortpixel_critical_css()->get_permalink( $item );
		if ( empty( $url ) ) {
			return false;
		}
		$bad_urls = $this->api->get_invalid_url_regexes();
		$bad_urls = array_filter( $bad_urls, function ( $regex ) use ( $url ) {
			return preg_match( "~$regex~", $url );
		} );
		if ( ! empty( $bad_urls ) ) {
			return false;
		}
		if ( 2083 <= strlen( $url ) ) {
			return false;
		}
		if ( ! empty( $item['queue_id'] ) ) {
			$result = $this->api->get_result( $item['queue_id'] );

			(SPCCSS_DEBUG & FileLog::DEBUG_AREA_API) && FileLog::instance()->log(" GET RESULT RETURNED ", $result);

			if ( $result instanceof \WP_Error ) {
				return false;
			}
			if ( ! empty( $result->status ) ) {
				$item['status'] = $result->status;
			}
			// @codingStandardsIgnoreLine
			if ( ! empty( $result->resultStatus ) ) {
				// @codingStandardsIgnoreLine
				$item['result_status'] = $result->resultStatus;
			}
			if ( ! empty( $result->error ) || in_array( $result->status, [
					'JOB_UNKNOWN',
					'JOB_FAILED',
				] ) ) {
				unset( $item['queue_id'] );

				return $item;
			}
			if ( 'JOB_QUEUED' === $result->status ) {
				// @codingStandardsIgnoreLine
				$item['queue_index'] = @$result->queueIndex;

				return $item;
			}
			if ( 'JOB_ONGOING' === $result->status ) {
				return $item;
			}
			if ( 'JOB_DONE' === $result->status ) {
                $item['code'] = $result->code;
				// @codingStandardsIgnoreLine
				if ( 'GOOD' === $result->resultStatus && ! empty( $result->css ) ) {
					shortpixel_critical_css()->integration_manager->disable_integrations();
					if ( !empty( $item['template'] ) || !empty( $item['post_type'] ) ) {
						$template = !empty( $item['template'] )?$item['template']:null;
						$post_type = !empty( $item['post_type'] )?$item['post_type']:null;
						$logs = shortpixel_critical_css()->template_log->get( $template, $post_type );
						foreach ( $logs as $log ) {
							$url = shortpixel_critical_css()->get_permalink( $log );
							if ( ! parse_url( $url, PHP_URL_QUERY ) ) {
								shortpixel_critical_css()->cache_manager->purge_page_cache( $log['type'], $log['object_id'], $url );
							}
							shortpixel_critical_css()->template_log->delete( $log['object_id'], $log['type'], $log['url'] );
						}
						shortpixel_critical_css()->cache_manager->purge_page_cache( $item['type'], $item['object_id'], shortpixel_critical_css()->get_permalink( $item ) );
					} else {
						shortpixel_critical_css()->cache_manager->purge_page_cache( $item['type'], $item['object_id'], shortpixel_critical_css()->get_permalink( $item ) );
					}
					shortpixel_critical_css()->integration_manager->enable_integrations();
					shortpixel_critical_css()->data_manager->set_cache( $item, $result->css );
					if ( empty( $item['template'] ) ) {
						shortpixel_critical_css()->data_manager->set_css_hash( $item, $item['css_hash'] );
						shortpixel_critical_css()->data_manager->set_html_hash( $item, $item['html_hash'] );
					}
					if( empty($item['url']) ) {
						$item['url'] = preg_replace('%nocache/$%', '', $url, 1);
					}
					shortpixel_critical_css()->log->insert( $item );
				}
                if($return_job_done) {
                    return $result;
                }
			} else {
				unset( $item['queue_id'] );

				return $item;
			}
		} else {
			$result = $this->api->generate( $item );
			if ( $result instanceof \WP_Error || empty($result->id) ) {
				return false;
			}
			$item['queue_id'] = $result->id;
			$item['status']   = $result->status;

			return $item;
		}

		return false;
	}
}
