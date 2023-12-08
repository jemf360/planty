<?php

namespace ShortPixel\CriticalCSS\Web\Check\Background;

use ShortPixel\CriticalCSS\Background\ProcessAbstract;
use ShortPixel\CriticalCSS\FileLog;
use ShortPixel\CriticalCSS\Queue\Web\Check\Table;

class Process extends ProcessAbstract {
	protected $action = 'shortpixel_critical_css_web_check';

	private $_processed_urls = [];

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
	public function task( $item, $return_job = false ) {
		$ret = false;
        $upload_dir    = wp_upload_dir();
        $url  = shortpixel_critical_css()->get_permalink( $item );
        if ( empty( $url ) ) {
            (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("WEB CHECK - URL IS EMPTY FOR ITEM:", $item);
			return $ret;
        }
        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("WEB CHECK TASK URL: " . $url);

        $item = $this->set_processing();
		if ( isset( $this->_processed_urls[ $url ] ) ) {
            (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("WEB CHECK ALREADY PROCESSED");
			if($return_job) {
				$ret = [
					'status' => Table::STATUS_DONE,
				];
			}
			return $ret;
		}
		$api_queue = shortpixel_critical_css()->get_api_queue();

		if ( $api_queue->get_item_exists( $item ) ) {
            (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("WEB CHECK ALREADY EXISTS");
			if($return_job) {
				$ret = [
					'status' => Table::STATUS_EXISTS, // already added to queue
				];
			}
			return $ret;
		}

		$css_hash  = shortpixel_critical_css()->get_data_manager()->get_css_hash( $item );
		$html_hash = shortpixel_critical_css()->get_data_manager()->get_html_hash( $item );

    $web_check_args = apply_filters( 'shortpixel_critical_css_web_check_request_args', [], $item );
    (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("WEB CHECK CALLING wp_remote_get. REQUEST ARGS: ", $web_check_args);
		$result = wp_remote_get( $url,  $web_check_args);
		$settings = shortpixel_critical_css()->settings_manager->get_settings();
		if( $result instanceof \WP_Error && $result->get_error_code() == 'http_request_failed' ) {
			if($settings['loopback_available']) {
				shortpixel_critical_css()->settings_manager->update_settings(array_merge(
					$settings,
					[ 'loopback_available' => false ]
				));
			}
		} else {
			if(!$settings['loopback_available']) {
				shortpixel_critical_css()->settings_manager->update_settings(array_merge(
					$settings,
					[ 'loopback_available' => true ]
				));
			}
		}
    (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("WEB CHECK wp_remote_get RESPONSE: ",
            is_string($result) ? substr($result, 0, 400) : $result);

		if ( $result instanceof \WP_Error ) {
			if ( empty( $item['error'] ) ) {
				$item['error'] = 0;
			}

			if ( $return_job || $item['error'] <= apply_filters( 'shortpixel_critical_css_web_check_retries', 3 ) ) {
				$item['error'] ++;
				sleep( 1 );
				$item = $this->set_pending();

				$ret = $item;
			}
			return $ret;
		}

		$document = new \DOMDocument();
		if ( ! @$document->loadHTML( $result['body'] ) ) {
			return $ret;
		}
		$xpath = new \DOMXpath( $document );
		$css   = '';
		$urls  = [];
		foreach ( $xpath->query( '((//style|//STYLE)|(//link|//LINK)[@rel="stylesheet"])' ) as $tag ) {
			$name = strtolower( $tag->tagName );
			$rel  = $tag->getAttribute( 'rel' );
			$href = $tag->getAttribute( 'href' );
			if ( 'link' == $name ) {
				if ( 'stylesheet' == $rel ) {

					// If not a stylesheet, rocket_async_css_process_file return false, or exclude regex/file extension matches, move on
					if ( 'stylesheet' !== $rel ) {
						continue;
					}
					if ( 0 === strpos( $href, '//' ) ) {
						//Handle no protocol urls
						$href = 'http:' . $href;
					}
					$href   = set_url_scheme( $href );
					$urls[] = $href;
				} else {
					$css .= $tag->textContent;
				}
			}
		}
		if ( preg_match_all( '#loadCSS\s*\(\s*["\'](.*)["\']\s*#', $result['body'], $matches ) ) {
			foreach ( $matches[1] as $match ) {
				$href = $match;
				if ( 0 === strpos( $href, '//' ) ) {
					//Handle no protocol urls
					$href = 'http:' . $match;
				}
				$href   = set_url_scheme( $href );
				$urls[] = $href;
			}
		}
		$urls = apply_filters( 'shortpixel_critical_css_web_check_css_urls', $urls, $item );
		foreach ( $urls as $url ) {
			$host = parse_url( $url, PHP_URL_HOST );
			if ( empty( $host ) ) {
				$url = site_url( $url );
			}
			$file = wp_remote_get( $url, [
				'sslverify' => false,
			] );
			// Catch Error
			if ( $file instanceof \WP_Error || ( is_array( $file ) && ( empty( $file['response']['code'] ) || ! in_array( $file['response']['code'], [
							200,
							304,
						] ) ) )
			) {
				continue;
			}
			$css .= $file['body'];
		}
		$changed      = false;
		$new_css_hash = hash( "crc32b", $css );
		if ( empty( $css_hash ) || $css_hash != $new_css_hash ) {
			$changed  = true;
			$css_hash = $new_css_hash;
		}

		if ( ! $changed ) {
			$body = $document->getElementsByTagName( 'body' )->item( 0 );
			if ( null !== $body ) {
				$new_html_hash = hash( "crc32b", $document->saveHTML( $body ) );
				if ( empty( $html_hash ) || $html_hash != $new_html_hash ) {
					$changed   = true;
					$html_hash = $new_html_hash;
				}
			}
		}
		if($return_job) {
			$ret = [
				'status' => Table::STATUS_PENDING,
			];
		}
		if ( $changed && ! $api_queue->get_item_exists( $item ) ) {
			$item['css_hash']  = $css_hash;
			$item['html_hash'] = $html_hash;
			shortpixel_critical_css()->get_integration_manager()->disable_integrations();
			shortpixel_critical_css()->get_cache_manager()->purge_page_cache( $item['type'], $item['object_id'], shortpixel_critical_css()->get_permalink( $item ) );
			shortpixel_critical_css()->get_integration_manager()->enable_integrations();
			shortpixel_critical_css()->get_data_manager()->set_cache( $item, '' );
			$api_queue->push_to_queue( $item )->save();

			if($return_job) {
				$ret = [
					'status' => Table::STATUS_DONE,
				];
			}
		}
		$this->_processed_urls[ $url ] = true;

		return $ret;
	}

	private function set_processing() {
		return $this->set_status( Table::STATUS_PROCESSING );
	}

	private function set_status( $status ) {
		$batch          = $this->get_batch();
		$data           = end( $batch->data );
		$data['status'] = $status;
		$batch->data    = [ $data ];
		$this->update( $batch->key, $batch->data );

		return $data;
	}

	private function set_pending() {
		return $this->set_status( Table::STATUS_PENDING );
	}
}
