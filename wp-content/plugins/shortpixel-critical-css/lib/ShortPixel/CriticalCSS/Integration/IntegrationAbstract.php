<?php

namespace ShortPixel\CriticalCSS\Integration;
/**
 * Class IntegrationAbstract
 */

use ComposePress\Core\Abstracts\Component;

/**
 * Class IntegrationAbstract
 *
 * @package ShortPixel\CriticalCSS\Integration
 * @property \ShortPixel\CriticalCSS $plugin
 */
abstract class IntegrationAbstract extends Component {

	/**
	 *
	 */
	public function init() {
		add_action( 'shortpixel_critical_css_enable_integrations', [
			$this,
			'enable',
		] );
		add_action( 'shortpixel_critical_css_disable_integrations', [
			$this,
			'disable',
		] );
	}

	/**
	 * @return void
	 */
	abstract public function enable();

	/**
	 * @return void
	 */
	abstract public function disable();
}
