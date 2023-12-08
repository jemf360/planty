<?php


namespace ShortPixel\CriticalCSS;

use ComposePress\Core\Abstracts\Component;

class Frontend extends Component {

    private $has_critical_css = false;

	public function init() {
		if ( ! is_admin() ) {
			add_action(
				'wp_print_styles', [
				$this,
				'print_styles',
			], 7 );
			add_action(
				'wp', [
					$this,
					'wp_action',
				]
			);
			add_action(
				'wp_head', [
					$this,
					'wp_head',
				]
			);
            if( isset( $_GET['LAZYSTYLES'] ) || 'on' === $this->plugin->settings_manager->get_setting( 'lazy_load_css_files' ) ) {
                add_filter('style_loader_tag', [
                    $this,
                    'lazy_styles'
                ]);
            }
		}
	}

	/**
	 *
	 */
	public function wp_head() {
		if ( get_query_var( 'nocache' ) ) :
			?>
			<meta name="robots" content="noindex, nofollow"/>
		<?php
		endif;
	}

	/**
	 *
	 */
	public function wp_action() {
		set_query_var( 'nocache', $this->plugin->request->is_no_cache() );
		$this->plugin->integration_manager->enable_integrations();
	}

	/**
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	public function print_styles() {
        global $wp_styles;

		if ( get_query_var( 'nocache' ) ) {
			do_action( 'shortpixel_critical_css_nocache' );
		}

        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("CCSS FRONT print_styles");

        if ( ! get_query_var( 'nocache' ) && ! is_404() ) {

            //TODO extract the below into a $this->plugin->retrieve_cached_css() function - will also receive a param URL
            // if present, will search for that, if not for the current page.
            $css = $this->plugin->retrieve_cached_css();
            $cache = $css->cache;
            $manual = $css->manual;

			do_action( 'shortpixel_critical_css_before_print_styles', $cache );

			if ( ! empty( $cache ) ) {
                $this->has_critical_css = true;
				?>
				<style id="criticalcss" data-no-minify="1"><?php echo $cache ?></style>
				<?php
			}

			if(isset($_GET['STRIPSTYLES'])) {
                (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("CCSS STYLES:", $wp_styles->queue);
                if ( is_a( $wp_styles, 'WP_Styles' ) ) foreach($wp_styles->queue as $style) {
                    (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("CCSS STYLE:", $wp_styles->query($style));
                    wp_dequeue_style($style);
                }
            }

            $type = $this->plugin->request->get_current_page_type();

            if(
                    isset($this->plugin->settings_manager->get_setting( 'cache_mode' )['postTypes'])
                    && ! empty( $type['post_type'] )
                    && is_array( $this->plugin->settings_manager->get_setting( 'post_type_values' ) )
                    && in_array( $type['post_type'], $this->plugin->settings_manager->get_setting( 'post_type_values' ) )
            ) {
	            unset($type['template']);
	            if ( empty( $cache ) ) {
		            if ( ! $this->plugin->api_queue->get_item_exists( $type ) ) {
			            $this->plugin->api_queue->push_to_queue( $type )->save();
		            }
		            $this->plugin->template_log->insert( $type );
	            }

            } elseif (
                    isset($this->plugin->settings_manager->get_setting( 'cache_mode' )['templates'])
                    && ! empty( $type['template'] )
                    && is_array( $this->plugin->settings_manager->get_setting( 'template_values' ) )
                    && in_array( $type['template'], $this->plugin->settings_manager->get_setting( 'template_values' ) )
            ) {
	            unset($type['post_type']);
                if ( empty( $cache ) ) {
					if ( ! $this->plugin->api_queue->get_item_exists( $type ) ) {
						$this->plugin->api_queue->push_to_queue( $type )->save();
					}
					$this->plugin->template_log->insert( $type );
				}
            } elseif (
                    isset($this->plugin->settings_manager->get_setting( 'cache_mode' )['posts'] )
            ) {
                unset($type['post_type'], $type['template']);
	            $hash = $this->plugin->data_manager->get_item_hash( $type );
				$check = $this->plugin->cache_manager->get_cache_fragment( [ 'webcheck', $hash ] );
				if ( ! $manual && ( empty( $check ) || ( ! empty( $check ) && empty( $cache ) && null !== $cache ) ) && ! $this->plugin->web_check_queue->get_item_exists( $type ) ) {
					$this->plugin->web_check_queue->push_to_queue( $type )->save();
					$this->plugin->cache_manager->update_cache_fragment( [ 'webcheck', $hash ], true );
				}
			}

			do_action( 'shortpixel_critical_css_after_print_styles' );
		}
	}

    public function lazy_styles($tag) {
        if( !$this->has_critical_css ) return $tag;

        if(!preg_match('/\bmedia\s*=/s', $tag)){
            $tag = preg_replace('/\/?>\s*$/s', ' media="print" onload="this.media=\'all\'"/>', $tag);
        } elseif(!preg_match('/\bmedia\s*=\s*[\'"]?\s*(print|speech)/s', $tag)) {
            $tag = preg_replace('/\bmedia\s*=\s*[\'"]?\s*([-a-zA-Z]+)[\'"]?/s', 'media="print" onload="this.media=\'\\1\'"', $tag);
        }
        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("CCSS lazy_styles TAG: ", $tag);
        return $tag;
    }
}
