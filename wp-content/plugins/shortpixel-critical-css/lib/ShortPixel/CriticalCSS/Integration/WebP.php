<?php


namespace ShortPixel\CriticalCSS\Integration;


class WebP extends IntegrationAbstract {

	/**
	 * @return void
	 */
	public function enable() {
		add_action( 'shortpixel_critical_css_nocache', [ $this, 'force_webp_off' ] );
	}

	/**
	 * @return void
	 */
	public function disable() {
		remove_action( 'shortpixel_critical_css_nocache', [ $this, 'force_webp_off' ] );
	}

	public function force_webp_off() {
	    if(isset($_SERVER['HTTP_ACCEPT'])) {
            $_SERVER['HTTP_ACCEPT'] = preg_replace( '~image/webp,?~', '', $_SERVER['HTTP_ACCEPT'] );
        }
	}
}
