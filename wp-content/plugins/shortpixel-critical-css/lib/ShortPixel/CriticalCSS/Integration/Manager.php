<?php


namespace ShortPixel\CriticalCSS\Integration;

/**
 * Class Manager
 *
 * @package ShortPixel\CriticalCSS\Integration
 */
class Manager extends \ComposePress\Core\Abstracts\Manager {
	/**
	 * @var bool
	 */
	protected $enabled = false;
	/**
	 * @var array
	 */
	protected $modules = [
		'RocketAsyncCSS',
		'RootRelativeURLS',
		'WPRocket',
		'WPEngine',
		'A3LazyLoad',
		'Kinsta',
		'Elementor',
		'WebP',
	];

	/**
	 * @return bool
	 */
	public function is_enabled() {
		return $this->enabled;
	}

	/**
	 *
	 */
	public function init() {
		parent::init();
		$this->enable_integrations();
	}

	/**
	 *
	 */
	public function enable_integrations() {
		do_action( 'shortpixel_critical_css_enable_integrations' );
		$this->enabled = true;

	}

	/**
	 *
	 */
	public function disable_integrations() {
		do_action( 'shortpixel_critical_css_disable_integrations' );
		$this->enabled = false;
	}
}
