<?php

namespace ShortPixel\CriticalCSS\Settings;

class ApiKeyTools {

	static $API_URL = 'https://api.shortpixel.com';

	/**
	 * Returns SPIO API key if found, null otherwise
	 * @return ?string
	 */
	static function getApiKey()
	{
		$settings = shortpixel_critical_css()->settings_manager->get_settings();

		return !empty($settings['apikey'])?$settings['apikey']:null;
	}

	/**
	 * Returns SPIO API key if found, null otherwise
	 * @return ?string
	 */
	static function getSPIOApiKey()
	{
		$spioApiKey = get_option( 'wp-short-pixel-apiKey', false );

		return !empty($spioApiKey)?$spioApiKey:null;
	}

	/**
	 * Validates API key using local checks and remote call. Returns array with boolean status and error string if not valid
	 * @param $key
	 *
	 * @return array
	 */
	static function validateAPIKey(string $key)
	{
		$ret = [
			'status' => true,
		];
		if(strlen($key) !== 20) {
			$ret = [
				'status' => false,
				'error' => 'Invalid API key length. Must be 20 characters'
			];

		} else {
			$result = wp_safe_remote_get( self::$API_URL . '/v2/user-info.php?key=' . urlencode($key) , array('timeout' => 120, 'httpversion' => '1.1'));
			if( is_wp_error( $result ) ) {
				$ret = [
					'status' => false,
					'error' => $result->get_error_message()
				];
			} else {
				if( is_array( $result['response'] ) && $result['response']['code'] == 200 ) {
					$answer = json_decode( $result['body'] );
					if( !empty($answer->Status) ) {
						//error status
						$ret = [
							'status' => false,
							'error' => !empty($answer->Status->Message) ? $answer->Status->Message : 'Unknown error',
						];
					} elseif( !empty($answer->email) ) {
						//api key is valid
						$ret = [
							'status' => true
						];
					} else {
						$ret = [
							'status' => false,
							'error' => 'Invalid API answer',
						];
					}
				}
			}
		}
		return $ret;
	}

	static function updateApiKey( $key )
	{
		$settings = shortpixel_critical_css()->settings_manager->get_settings();
		shortpixel_critical_css()->settings_manager->update_settings(array_merge(
			$settings,
			[ 'apikey' => $key ]
		));

		return true;
	}
}