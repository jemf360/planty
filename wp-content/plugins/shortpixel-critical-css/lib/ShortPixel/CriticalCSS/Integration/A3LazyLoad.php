<?php


namespace ShortPixel\CriticalCSS\Integration;


class A3LazyLoad extends IntegrationAbstract {

	/**
	 * @return void
	 */
	public function enable() {
		add_action( 'shortpixel_critical_css_nocache', [ $this, 'disable_lazyload' ] );
	}

	/**
	 * @return void
	 */
	public function disable() {
		remove_action( 'shortpixel_critical_css_nocache', [ $this, 'disable_lazyload' ] );
	}

	public function disable_lazyload() {
		add_filter( 'a3_lazy_load_run_filter', '__return_false' );
	}
}