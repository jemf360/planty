<?php

namespace ShortPixel\CriticalCSS\Admin;

use ComposePress\Core\Abstracts\Component;
use ShortPixel\CriticalCSS;
use ShortPixel\CriticalCSS\Admin\UI\Post;
use ShortPixel\CriticalCSS\Admin\UI\Term;
use ShortPixel\CriticalCSS\API;
use ShortPixel\CriticalCSS\Queue\API\Table as APITable;
use ShortPixel\CriticalCSS\Queue\Log\Table as LogTable;
use ShortPixel\CriticalCSS\Queue\Web\Check\Table as WebCheckTable;
use ShortPixel\CriticalCSS\Settings\API as SettingsAPI;

/**
 * Class UI
 *
 * @package ShortPixel\CriticalCSS\Admin
 * @property \ShortPixel\CriticalCSS $plugin
 */
class UI extends Component {
	/**
	 * @var \ShortPixel\CriticalCSS\Settings\API
	 */
	private $settings_ui;
	/**
	 * @var \ShortPixel\CriticalCSS\Queue\ListTableAbstract
	 */
	private $api_table;

	/**
	 * @var \ShortPixel\CriticalCSS\API
	 */
	private $api;
	/**
	 * @var \ShortPixel\CriticalCSS\Queue\Web\Check\Table
	 */
	private $web_check_table;
	/**
	 * @var \ShortPixel\CriticalCSS\Queue\Log\Table
	 */
	private $log_table;

	/**
	 * @var \ShortPixel\CriticalCSS\Admin\UI\Post
	 */
	private $post_ui;

	/**
	 * @var \ShortPixel\CriticalCSS\Admin\UI\Term
	 */
	private $term_ui;

	/**
	 * UI constructor.
	 *
	 * @param \ShortPixel\CriticalCSS\API|\ShortPixel\CriticalCSS\Settings\API $settings_ui
	 * @param \ShortPixel\CriticalCSS\API                              $api
	 * @param APITable                                         $api_table
	 * @param WebCheckTable                                    $web_check_table
	 * @param \ShortPixel\CriticalCSS\Queue\Log\Table                  $log_table
	 * @param \ShortPixel\CriticalCSS\Admin\UI\Post                    $post_ui
	 * @param \ShortPixel\CriticalCSS\Admin\UI\Term                    $term_ui
	 */
	public function __construct( SettingsAPI $settings_ui, API $api, APITable $api_table, WebCheckTable $web_check_table, LogTable $log_table, Post $post_ui, Term $term_ui ) {
		$this->settings_ui     = $settings_ui;
		$this->api             = $api;
		$this->api_table       = $api_table;
		$this->web_check_table = $web_check_table;
		$this->log_table       = $log_table;
		$this->post_ui         = $post_ui;
		$this->term_ui         = $term_ui;

        if(!defined('DISALLOW_FILE_EDIT') || !DISALLOW_FILE_EDIT ) {
            add_action('customize_register',[$this, 'spccss_customize_register']);
        }
    }

	/**
	 * @return \ShortPixel\CriticalCSS\Queue\ListTableAbstract
	 */
	public function get_api_table() {
		return $this->api_table;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\API
	 */
	public function get_api() {
		return $this->api;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Queue\Web\Check\Table
	 */
	public function get_web_check_table() {
		return $this->web_check_table;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Queue\Log\Table
	 */
	public function get_log_table() {
		return $this->log_table;
	}


	/**
	 *
	 */
	public function init() {
		$this->setup_components();
		$this->api_table->set_queue( $this->plugin->api_queue );
		$this->log_table->set_queue( $this->plugin->log );
		$this->web_check_table->set_queue( $this->plugin->web_check_queue );
		if ( is_admin() ) {
			add_action( 'network_admin_menu', [
				$this,
				'settings_init',
			] );
			add_action( 'admin_menu', [
				$this,
				'settings_init',
			] );
			add_action( 'pre_update_option_shortpixel_critical_css', [
				$this,
				'sync_options',
			], 10, 2 );
			add_action( 'update_option_shortpixel_critical_css_web_check_queue', [
				$this,
				'delete_dummy_option',
			], 10, 2 );
			add_action( 'update_option_shortpixel_critical_css_api_queue', [
				$this,
				'delete_dummy_option',
			] );
			add_action( 'update_option_shortpixel_critical_css_log', [
				$this,
				'delete_dummy_option',
			] );
		}
	}


	/**
	 * Build settings page configuration
	 */
	public function settings_init() {
		wp_enqueue_script( 'sp_ccss_apikey', plugin_dir_url( dirname(__FILE__ ) ) . 'assets/js/apikey.min.js' , [], false, true );
		wp_localize_script( 'sp_ccss_apikey', 'ccssApikeyLocal', [
			'descriptionRegister' => __( 'API Key for ShortPixel\'s Critical CSS. Get yours for free at <a href="https://ShortPixel.com/free-sign-up" target="_blank">ShortPixel.com</a>. If you use our <a href="https://wordpress.org/plugins/shortpixel-image-optimiser/" target="_blank">ShortPixel Image Optimization</a> plugin, you can use the same API Key.', CriticalCSS::LANG_DOMAIN ),
			'descriptionLogin'    => __( 'API Key for ShortPixel\'s Critical CSS. Manage your key on <a href="https://ShortPixel.com/login" target="_blank">ShortPixel.com</a>', CriticalCSS::LANG_DOMAIN ),
			'buttonText'          => __( 'Validate & save api key' ),
		] );

        $api_key = $this->plugin->settings_manager->get_setting( 'apikey' );
		if ( is_multisite() ) {
			$hook = add_submenu_page( 'settings.php', 'ShortPixel Critical CSS Settings', 'SP Critical CSS', 'manage_network_options', CriticalCSS::OPTIONNAME, [
				$this,
				'settings_ui',
			] );
		} else {
			$hook = add_options_page( 'ShortPixel Critical CSS Settings', 'SP Critical CSS', 'manage_options', CriticalCSS::OPTIONNAME, [
				$this,
				'settings_ui',
			] );
		}
		add_action( "load-$hook", [
			$this,
			'screen_option',
		] );
		$this->settings_ui->add_section( [
			'id'    => $this->plugin->get_option_name(),
			'title' => __( 'Options', CriticalCSS::LANG_DOMAIN ),
            'desc' => ( $api_key === ''
                ? __('<h4>Thank you for installing ShortPixel Critical CSS! To start generating critical CSS for your pages, you will need an API key. You can get it for free when you register at <a href="https://www.ShortPixel.com/free-sign-up" target="_blank">ShortPixel.com</a></h4>', CriticalCSS::LANG_DOMAIN ) : ''),
		] );
        //api field rendered in js, there is only label and description
		$this->settings_ui->add_field( $this->plugin->get_option_name(), [
			'name'              => 'apikey',
			'label'             => 'API Key',
			'type'              => 'hidden',
            'autocomplete'      => 'off',
			'sanitize_callback' => [
				$this,
				'validate_criticalcss_apikey',
			],
			'desc'              =>
                ($api_key === '')
                ? __( 'API Key for ShortPixel\'s Critical CSS. Get yours for free at <a href="https://ShortPixel.com/free-sign-up" target="_blank">ShortPixel.com</a>. If you use our <a href="https://wordpress.org/plugins/shortpixel-image-optimiser/" target="_blank">ShortPixel Image Optimization</a> plugin, you can use the same API Key.', CriticalCSS::LANG_DOMAIN )
                : __( 'API Key for ShortPixel\'s Critical CSS. Manage your key on <a href="https://ShortPixel.com/login" target="_blank">ShortPixel.com</a>', CriticalCSS::LANG_DOMAIN ),
		] );
		if( $api_key === '' &&
            is_null( $this->plugin->beta_api_key ) ) {

        } else {
//            if ('on' !== $this->plugin->settings_manager->get_setting('cache_mode')['templates']) {
//                $this->settings_ui->add_field($this->plugin->get_option_name(), [
//                    'name' => 'force_web_check',
//                    'label' => 'Force Web Check',
//                    'type' => 'checkbox',
//                    'desc' => __('Force a web check on all pages for css changes. This will run for new web requests.', $this->plugin->get_lang_domain()),
//                ]);
//            }
            if ('on' !== $this->plugin->settings_manager->get_setting('prioritize_manual_css')) {
                $cacheModeOptions = [];
                $cacheModeOptions['postTypes'] = __('Based on Post Types');

                if( !function_exists('ct_plugin_setup') ) { //oxygen builder plugin is not compatible with templates cache
                   $cacheModeOptions['templates'] = __('Based on WordPress templates');
                }
                $cacheModeOptions['posts'] = __('Based on the individual URL');

	            $this->settings_ui->add_field($this->plugin->get_option_name(), [
                    'name' => 'cache_mode',
                    'label' => 'Cache Mode',
                    'type' => 'multicheck',
                    'options' => $cacheModeOptions,
                    'desc' => __('<p style="font-style: italic;">At least one option should be selected. </p>', $this->plugin->get_lang_domain()),
                ]);
                $postTypes = $this->plugin->get_cache_mode_posttypes();
	            $this->settings_ui->add_field($this->plugin->get_option_name(), [
		            'name' => 'post_type_values',
		            'label' => 'Post Types',
		            'type' => 'multicheck',
		            'options' => $postTypes,
		            //'desc' => __('Cache Critical CSS based on WordPress templates and not the post, page, term, author page, or arbitrary url. <p style="font-weight: bold;">This option may be useful if your website contains lots of content, pages, posts, etc. </p>', $this->plugin->get_lang_domain()),
	            ]);
                $templates = $this->plugin->get_cache_mode_templates();
	            $this->settings_ui->add_field($this->plugin->get_option_name(), [
		            'name' => 'template_values',
		            'label' => 'Templates',
		            'type' => 'multicheck',
		            'options' => $templates,
		            //'desc' => __('Cache Critical CSS based on WordPress templates and not the post, page, term, author page, or arbitrary url. <p style="font-weight: bold;">This option may be useful if your website contains lots of content, pages, posts, etc. </p>', $this->plugin->get_lang_domain()),
	            ]);
            }
            $this->settings_ui->add_field($this->plugin->get_option_name(), [
                'name' => 'prioritize_manual_css',
                'label' => 'Enable Manual CSS Override',
                'type' => 'checkbox',
                'desc' => __('Allow the CSS for a post, term, post type or taxonomy to always override the generated CSS. By default, the generated CSS takes precedence if it is present.', $this->plugin->get_lang_domain()),
            ]);

            if (!apply_filters('shortpixel_critical_css_cache_integration', false)) {
                $this->settings_ui->add_field($this->plugin->get_option_name(), [
                    'name' => 'web_check_interval',
                    'label' => 'Web Check Interval',
                    'type' => 'number',
                    'desc' => __('How often in seconds web pages should be checked for changes to regenerate the critical CSS for them.', $this->plugin->get_lang_domain()),
                ]);
            }
            $this->settings_ui->add_field($this->plugin->get_option_name(), [
                'name' => 'force_include_styles',
                'label' => 'Force Styles to be included',
                'type' => 'textarea',
                'desc' => __('A list of CSS selectors and/or regex patterns for CSS selectors', $this->plugin->get_lang_domain()),
                'sanitize_callback' => [
                    $this,
                    'validate_force_include_styles',
                ],
            ]);
            if(!defined('DISALLOW_FILE_EDIT') || !DISALLOW_FILE_EDIT ) {
                $this->settings_ui->add_field($this->plugin->get_option_name(), [
                    'name' => 'fallback_css',
                    'label' => 'Fallback CSS',
                    'type' => 'html',
                    'desc' => __('<p>Global CSS to be uses as fallback when generated and manual post CSS are not available can be added via your <a href="customize.php" target="_blank"><strong>Customizer</strong></a>. In the <strong>Custom CSS</strong> section, please fill in the <strong>Fallback Critical CSS</strong> field.</p>', $this->plugin->get_lang_domain()),
                ]);
            }
            $this->settings_ui->add_field($this->plugin->get_option_name(), [
                'name' => 'lazy_load_css_files',
                'label' => 'Lazy-load CSS files',
                'type' => 'checkbox',
                'desc' => __('Load CSS files asynchronously to speed up the Largest Contentful Paint of your pages (Core Web Vital).', $this->plugin->get_lang_domain()),
            ]);
            if ('on' === $this->plugin->settings_manager->get_setting('prioritize_manual_css')) {
                foreach (get_post_types() as $post_type) {
                    $label = get_post_type_object($post_type)->labels->singular_name;

                    $this->settings_ui->add_field($this->plugin->get_option_name(), [
                        'name' => "single_post_type_css_{$post_type}",
                        'label' => 'Use Single CSS for ' . $label,
                        'type' => 'checkbox',
                        'desc' => sprintf(__('Use a single CSS for all %s items', $this->plugin->get_lang_domain()), $label),
                    ]);
                    if ('on' === $this->plugin->settings_manager->get_setting("single_post_type_css_{$post_type}")) {
                        $this->settings_ui->add_field($this->plugin->get_option_name(), [
                            'name' => "single_post_type_css_{$post_type}_css",
                            'label' => $label . ' post type CSS input',
                            'type' => 'textarea',
                            'desc' => sprintf(__('Enter CSS for all %s items', $this->plugin->get_lang_domain()), $label),
                        ]);
                        $this->settings_ui->add_field($this->plugin->get_option_name(), [
                            'name' => "single_post_type_css_{$post_type}_archive_css",
                            'label' => $label . ' post type archive CSS input',
                            'type' => 'textarea',
                            'desc' => sprintf(__('Enter CSS for %s archive listings', $this->plugin->get_lang_domain()), $label),
                        ]);
                    }
                }
                foreach (get_taxonomies() as $taxonomy) {
                    $label = get_taxonomy($taxonomy)->labels->singular_name;
                    $this->settings_ui->add_field($this->plugin->get_option_name(), [
                        'name' => "single_taxonomy_css_{$taxonomy}",
                        'label' => 'Use Single CSS for ' . $label,
                        'type' => 'checkbox',
                        'desc' => sprintf(__('Use a single CSS for all %s items', $this->plugin->get_lang_domain()), $label),
                    ]);
                    if ('on' === $this->plugin->settings_manager->get_setting("single_taxonomy_css_{$taxonomy}_css")) {
                        $this->settings_ui->add_field($this->plugin->get_option_name(), [
                            'name' => "single_taxonomy_css_{$taxonomy}_css",
                            'label' => $label . ' taxonomy CSS input',
                            'type' => 'textarea',
                            'desc' => sprintf(__('Enter CSS for all %s items', $this->plugin->get_lang_domain()), $label),
                        ]);
                    }
                }

            }
        }
		$this->settings_ui->admin_init();
	}

    public function spccss_customize_register( $wp_customize ) {
        $custom_css_setting = new \WP_Customize_Custom_CSS_Setting (
            $wp_customize,
            'custom_css[fallback_critical_css]',
            array(
                'capability' => 'edit_css',
                'default'    => '',
            )
        );
        $wp_customize->add_setting( $custom_css_setting );

        $wp_customize->add_control( new \WP_Customize_Code_Editor_Control (
            $wp_customize,
            'fallback_critical_css',
            array(
                'label'       => __( 'Fallback Critical CSS' ),
                'section'     => 'custom_css',
                'settings'    => array( 'default' => $custom_css_setting->id ),
                'code_type'   => 'text/css',
                'input_attrs' => array(
                    'aria-describedby' => 'editor-keyboard-trap-help-1 editor-keyboard-trap-help-2 editor-keyboard-trap-help-3 editor-keyboard-trap-help-4',
                ),
            )
        ) );
    }


    /**
	 * Render settings page
	 */
	public function settings_ui() {
        echo('<div class="wrap spccss-settings"><h1>' . __('ShortPixel Critical CSS Settings', $this->plugin->get_lang_domain()) . '</h1>');
		require_once ABSPATH . 'wp-admin/options-head.php';
		if (    ( '' !== $this->plugin->settings_manager->get_setting( 'apikey' ) ||
                  !is_null( $this->plugin->beta_api_key ) )
                 ) {
			$this->settings_ui->add_section( [
				'id'    => 'shortpixel_critical_css_web_check_queue',
				'title' => 'Web Check Queue',
				'form'  => false,
			] );
		}
		if ( '' !== $this->plugin->settings_manager->get_setting( 'apikey' ) ||
		     !is_null( $this->plugin->beta_api_key ) ) {
			$this->settings_ui->add_section( [
				'id'    => 'shortpixel_critical_css_api_queue',
				'title' => 'API Queue',
				'form'  => false,
			] );
			$this->settings_ui->add_section( [
				'id'    => 'shortpixel_critical_css_log',
				'title' => 'Processed Log',
				'form'  => false,
			] );
			?>
			<style type="text/css">
				.form-table .api_queue > th, .form-table .web_check_queue > th {
					display: none;
				}

				.no-items, .manage-column, .form-table .api_queue td, .form-table .web_check_queue td {
					text-align: center !important;
				}

				.form-table th {
					width: auto;
				}

				.group h2 {
					display: none;
				}
			</style>

				<?php ob_start(); ?>
				<p>
					<?php _e( 'This queue is designed to process your content only when the cache mode based on the URL is used. It detects changes to the content and sends them to the "API Queue" when they are found.', $this->plugin->get_lang_domain() ); ?>
				</p>
                <?php if( !$this->plugin->settings_manager->get_setting('loopback_available') ): ?>
                    <div class="error" style="text-align: left; margin: 10px 0;">
                        <p>
                            <?php _e( 'The Web check queue may not work as intended because the web loop-back call failed. Please check your server configuration to make sure <code>wp_remote_get( home_url())</code> is allowed.', $this->plugin->get_lang_domain() ); ?>
                        </p>
                    </div>
                <?php endif; ?>
				<form method="post">
					<?php
					$this->web_check_table->prepare_items();
					$this->web_check_table->display();
					?>
				</form>
				<?php
				$this->settings_ui->add_field( 'shortpixel_critical_css_web_check_queue', [
					'name'  => 'web_check_queue',
					'label' => null,
					'type'  => 'html',
					'desc'  => ob_get_clean(),
				] );
			ob_start(); ?>
			<p>
				<?php _e( 'This queue processes requests by sending them to ShortPixel Critical CSS and waiting for them to be processed. When it\'s done, the supported cache is flushed and the page gets faster. :)', $this->plugin->get_lang_domain() ); ?>
			</p>
			<form method="post">
				<?php
				$this->api_table->prepare_items();
				$this->api_table->display();
				?>
			</form>
			<?php
			$this->settings_ui->add_field( 'shortpixel_critical_css_api_queue', [
				'name'  => 'api_queue',
				'label' => null,
				'type'  => 'html',
				'desc'  => ob_get_clean(),
			] );

			ob_start(); ?>
			<p>
				<?php _e( 'This is a list of all pages, links, and/or templates that have already been processed.', $this->plugin->get_lang_domain() ); ?>
			</p>
            <div id="ccss_modal_background" class="spccss-modal-background">
            </div>
            <div id="ccss_modal" class="spccss-modal">
                <span class="spccss-close">&times;</span>
                <div class="spccss-modal-content">

                    <p class="spccss-modal-css">...</p>
                </div>
            </div>
            <style>
                .spccss-settings .nav-tab-active {
                    background: #fff;
                    border-bottom: 1px solid white;
                }
                .spccss-settings .metabox-holder {
                    background-color: #fff;
                    border: 1px solid lightgrey;
                    border-top: none;
                    padding: 10px;
                }
                /* The Modal (background) */
                .spccss-modal-background {
                    display: none; /* Hidden by default */
                    position: fixed; /* Stay in place */
                    z-index: 1; /* Sit on top */
                    left: 0;
                    top: 0;
                    width: 100%; /* Full width */
                    height: 100%; /* Full height */
                    overflow: auto; /* Enable scroll if needed */
                    background-color: rgb(0,0,0); /* Fallback color */
                    background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
                }
                .spccss-modal {
                    display: none; /* Hidden by default */
                    position: fixed; /* Stay in place */
                    z-index: 1; /* Sit on top */
                    left: 25%;
                    top: 10%;
                    right: 10%;
                    bottom: 10%;
                    overflow: hidden;
                    z-index: 2;
                    border: 1px solid #888;
                }
                /* Modal Content/Box */
                .spccss-modal-content {
                    background-color: #fefefe;
                    padding: 20px;
                    overflow: auto;
                    position: relative;
                }
                .spccss-modal-css {
                    white-space: pre;
                }
                /* The Close Button */
                .spccss-close {
                    color: #aaa;
                    font-size: 28px;
                    font-weight: bold;
                    position: absolute;
                    right: 30px;
                    top: 10px;
                    z-index: 3;
                }

                .spccss-close:hover,
                .spccss-close:focus {
                    color: black;
                    text-decoration: none;
                    cursor: pointer;
                }
            </style>

            <form method="post">
				<?php
				$this->log_table->prepare_items();
				$this->log_table->display();
				?>
			</form>
			<?php
			$this->settings_ui->add_field( 'shortpixel_critical_css_log', [
				'name'  => 'log',
				'label' => null,
				'type'  => 'html',
				'desc'  => ob_get_clean(),
			] );
		}

		$this->settings_ui->admin_init();
		$this->settings_ui->show_navigation();
		$this->settings_ui->show_forms();
		?>
        </div>
		<?php
	}


	/**
	 * Validate API key is real and error if so
	 *
	 * @param $options
	 *
	 * @return bool
	 */
	public function validate_criticalcss_apikey( $options ) {
		$valid = true;
		if ( empty( $options['apikey'] ) ) {
			return '';
		}
        $result = CriticalCSS\Settings\ApiKeyTools::validateAPIKey($options['apikey']);

		if ( ! $result['status'] ) {
			add_settings_error( 'apikey', 'invalid_apikey', $result['error'] );
			$valid = false;
		}

		$this->api->set_api_key( $options['apikey'] );
        if ( ! $this->api->ping() ) {
			add_settings_error( 'apikey', 'invalid_apikey', 'ShortPixel API Key is invalid' );
			$valid = false;
		}

		return $options['apikey'];
	}

	/**
	 * @param $options
	 *
	 * @return bool|string
	 */
	public function validate_force_include_styles( $options ) {
		$valid = true;
		if ( ! empty( $options['force_include_styles'] ) ) {
			$lines = explode( "\n", $options['force_include_styles'] );
			$lines = array_map( 'trim', $lines );
			foreach ( $lines as $index => $line ) {
				if ( preg_match( '/^\/.*?\/[gimy]*$/', $line ) ) {
					preg_match( $line, null );
					if ( PREG_NO_ERROR !== preg_last_error() ) {
						add_settings_error( 'force_include_styles', 'invalid_force_include_styles_regex', sprintf( 'Line %d is an invalid regex for a force included style', $index + 1 ) );
						$valid = false;
						break;
					}

				}
			}
			if ( $valid ) {
				$options['force_include_styles'] = implode( "\n", $lines );
			}
		}

		return ! $valid ? $valid : $options['force_include_styles'];
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Settings\API
	 */
	public function get_settings_ui() {
		return $this->settings_ui;
	}

	/**
	 *
	 */
	public function screen_option() {
		$this->api_table->init();
		$this->web_check_table->init();
		$this->log_table->init();
	}

	/**
	 * @param $value
	 * @param $old_value
	 *
	 * @return array
	 */
	public function sync_options( $value, $old_value ) {
		$original_old_value = $old_value;
		if ( ! is_array( $old_value ) ) {
			$old_value = [];
		}

		if ( is_multisite() ) {
			$old_value = $this->plugin->settings_manager->get_settings();
		}

		$value = array_merge( $old_value, $value );

		if ( isset( $value['force_web_check'] ) && 'on' === $value['force_web_check'] ) {
			$value['force_web_check'] = 'off';
			$this->plugin->get_cache_manager()->purge_page_cache();
		}
		if ( isset($old_value['web_check_interval']) && $value['web_check_interval'] != $old_value['web_check_interval'] ) {
			$scheduled = wp_next_scheduled( 'shortpixel_critical_css_purge_log' );
			if ( $scheduled ) {
				wp_unschedule_event( $scheduled, 'shortpixel_critical_css_purge_log' );
			}

		}

		if ( is_multisite() ) {
			update_site_option( $this->plugin->get_option_name(), $value );
			$value = $original_old_value;
		}

		return $value;
	}

	/**
	 * @param        $old_value
	 * @param        $value
	 * @param string $option
	 */
	public function delete_dummy_option( $old_value, $value, $option ) {
		delete_option( $option );
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Admin\UI\Post
	 */
	public function get_post_ui() {
		return $this->post_ui;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Admin\UI\Term
	 */
	public function get_term_ui() {
		return $this->term_ui;
	}


}
