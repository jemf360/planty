<?php

namespace ShortPixel;


use ComposePress\Core\Abstracts\Plugin;
use ShortPixel\CriticalCSS\Admin\UI;
use ShortPixel\CriticalCSS\API;
use ShortPixel\CriticalCSS\API\Background\Process as BackgroundProcess;
use ShortPixel\CriticalCSS\Background\ProcessAbstract;
use ShortPixel\CriticalCSS\Cache\Manager as CacheManager;
use ShortPixel\CriticalCSS\Data\Manager as DataManager;
use ShortPixel\CriticalCSS\FileLog;
use ShortPixel\CriticalCSS\Frontend;
use ShortPixel\CriticalCSS\Installer;
use ShortPixel\CriticalCSS\Integration\Manager as IntegrationManager;
use ShortPixel\CriticalCSS\Log;
use ShortPixel\CriticalCSS\Queue\ListTableAbstract;
use ShortPixel\CriticalCSS\Queue\Log\Table as LogTable;
use ShortPixel\CriticalCSS\Request;
use ShortPixel\CriticalCSS\Settings\ApiKeyTools;
use ShortPixel\CriticalCSS\Settings\Manager as SettingsManager;
use ShortPixel\CriticalCSS\Template\Log as TemplateLog;
use ShortPixel\CriticalCSS\Web\Check\Background\Process;
use ShortPixel\CriticalCSS\Web\Check\Background\Process as WebCheckProcess;
use ShortPixel\CriticalCSS\Queue\Web\Check\Table as CheckTable;

/**
 * Class CriticalCSS
 *
 * @package ShortPixel
 * @property CacheManager                 $cache_manager
 * @property Request                      $request
 * @property DataManager                  $data_manager
 * @property IntegrationManager           $integration_manager
 * @property SettingsManager              $settings_manager
 * @property BackgroundProcess            $api_queue
 * @property \ShortPixel\CriticalCSS\Queue\API\Table $api_data
 * @property WebCheckProcess              $web_check_queue
 * @property \ShortPixel\CriticalCSS\Queue\Web\Check $web_check_data
 * @property Frontend                     $frontend
 * @property UI                           $admin_ui
 * @property Installer                    $installer
 * @property Log                          $log
 * @property LogTable                     $log_data
 * @property \ShortPixel\CriticalCSS\Template\Log $template_log
 */
class CriticalCSS extends Plugin {
	/**
	 *
	 */
	const VERSION = '0.7.7';

	/**
	 *
	 */
	const LANG_DOMAIN = 'shortpixel_critical_css';

	/**
	 *
	 */
	const OPTIONNAME = 'shortpixel_critical_css';

	/**
	 *
	 */
	const TRANSIENT_PREFIX = 'shortpixel_critical_css';

	/**
	 *
	 */
	const PLUGIN_SLUG = 'shortpixel-critical-css';

	/**
	 * @var bool
	 */
	protected $nocache = false;
	/**
	 * @var \ShortPixel\CriticalCSS\Web\Check\Background\Process
	 */
	protected $web_check_queue;
	/**
	 * @var \ShortPixel\CriticalCSS\API\Background\Process
	 */
	protected $api_queue;

    /**
     * @var \ShortPixel\CriticalCSS\Queue\API\Table
     */
    protected $api_data;
	/**
	 * @var \ShortPixel\CriticalCSS\Queue\Web\Check\Table
	 */
	protected $web_check_data;
	/**
	 * @var \ShortPixel\CriticalCSS\Integration\Manager
	 */
	protected $integration_manager;

	/**
	 * @var string
	 */
	protected $template;

	/**
	 * @var \ShortPixel\CriticalCSS\Admin\UI
	 */
	protected $admin_ui;

	/**
	 * @var \ShortPixel\CriticalCSS\Data\Manager
	 */
	protected $data_manager;

	/**
	 * @var \ShortPixel\CriticalCSS\Cache\Manager
	 */
	protected $cache_manager;

	/**
	 * @var \ShortPixel\CriticalCSS\Request
	 */
	protected $request;
	/**
	 * @var \ShortPixel\CriticalCSS\Settings\Manager
	 */
	protected $settings_manager;
	/**
	 * @var \ShortPixel\CriticalCSS\Frontend
	 */
	protected $frontend;
	/**
	 * @var \ShortPixel\CriticalCSS\Installer
	 */
	private $installer;
	/**
	 * @var \ShortPixel\CriticalCSS\Log
	 */
	private $log;
    /**
     * @var \ShortPixel\CriticalCSS\Queue\Log\Table
     */
    private $log_data;

	/**
	 * @var \ShortPixel\CriticalCSS\Template\Log
	 */
	private $template_log;

	/**
	 * @var string
	 */
	public $beta_api_key = '6p7RX3VpPHtIR6rRmxfs'; //default beta key

	/**
	 * CriticalCSS constructor.
	 *
	 * @param \ShortPixel\CriticalCSS\Settings\Manager                                                           $settings_manager
	 * @param \ShortPixel\CriticalCSS\Admin\UI                                                                   $admin_ui
	 * @param \ShortPixel\CriticalCSS\Data\Manager                                                               $data_manager
	 * @param \ShortPixel\CriticalCSS\Cache\Manager                                                              $cache_manager
	 * @param \ShortPixel\CriticalCSS\Request                                                                    $request
	 * @param \ShortPixel\CriticalCSS\Integration\Manager                                                 $integration_manager
	 * @param \ShortPixel\CriticalCSS\API\Background\Process|\ShortPixel\CriticalCSS\Web\Check\Background\Process $api_queue
     * @param \ShortPixel\CriticalCSS\Queue\API\Table                                                            $api_data
	 * @param \ShortPixel\CriticalCSS\Frontend                                                                   $frontend
	 * @param \ShortPixel\CriticalCSS\Web\Check\Background\Process                                               $web_check_queue
	 * @param \ShortPixel\CriticalCSS\Installer                                                                  $installer
	 * @param \ShortPixel\CriticalCSS\Log                                                                        $log
     * @param \ShortPixel\CriticalCSS\Queue\Log\Table                                                            $log_table
	 * @param \ShortPixel\CriticalCSS\Template\Log                                                               $template_log
	 */
	public function __construct(
		SettingsManager $settings_manager,
		UI $admin_ui,
		DataManager $data_manager,
		CacheManager $cache_manager,
		CriticalCSS\Request $request,
		IntegrationManager $integration_manager,
		BackgroundProcess $api_queue,
        \ShortPixel\CriticalCSS\Queue\API\Table $api_data,
		Frontend $frontend,
		WebCheckProcess $web_check_queue,
		\ShortPixel\CriticalCSS\Queue\Web\Check\Table $web_check_data,
		Installer $installer,
		Log $log,
        LogTable $log_data,
		TemplateLog $template_log
	) {
		$this->settings_manager    = $settings_manager;
		$this->admin_ui            = $admin_ui;
		$this->data_manager        = $data_manager;
		$this->cache_manager       = $cache_manager;
		$this->request             = $request;
		$this->integration_manager = $integration_manager;
		$this->api_queue           = $api_queue;
		$this->api_data            = $api_data;
		$this->web_check_queue     = $web_check_queue;
		$this->web_check_data      = $web_check_data;
		$this->frontend            = $frontend;
		$this->installer           = $installer;
		$this->log                 = $log;
        $this->log_data            = $log_data;
		$this->template_log        = $template_log;
		parent::__construct();
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Installer
	 */
	public function get_installer() {
		return $this->installer;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Admin\UI
	 */
	public function get_admin_ui() {
		return $this->admin_ui;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Frontend
	 */
	public function get_frontend() {
		return $this->frontend;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Settings\Manager
	 */
	public function get_settings_manager() {
		return $this->settings_manager;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Web\Check\Background\Process
	 */
	public function get_web_check_queue() {
		return $this->web_check_queue;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Data\Manager
	 */
	public function get_data_manager() {
		return $this->data_manager;
	}

	/**
	 * @return CriticalCSS\Cache\Manager
	 */
	public function get_cache_manager() {
		return $this->cache_manager;
	}

	/**
	 *
	 */
	public function activate() {
		$this->installer->activate();
	}

    public function first_install() {
        if(false === $this->settings_manager->get_setting('init_settings')) {
            $settings = $this->settings_manager->get_settings();
            $settings['init_settings'] = true;
            $this->settings_manager->update_settings($settings);
            wp_redirect( admin_url( 'options-general.php?page=' .  self::LANG_DOMAIN) );
        }
    }

	/**
	 *
	 */
	public function deactivate() {
		$this->installer->deactivate();
	}

	/**
	 * @param array $object
	 *
	 * @return false|mixed|string|\\WP_Error
	 */
	public
	function get_permalink(
		array $object
	) {
		if ( ! empty( $object['blog_id'] ) ) {
			switch_to_blog( $object['blog_id'] );
		}
		$enable_integration = false;
		if ( $this->integration_manager->is_enabled() ) {
			$this->integration_manager->disable_integrations();
			$enable_integration = true;
		}
		if ( ! empty( $object['object_id'] ) ) {
			$object['object_id'] = absint( $object['object_id'] );
		}
		switch ( $object['type'] ) {
			case 'post':
				$url = get_permalink( $object['object_id'] );
				break;
			case 'term':
				$url = get_term_link( $object['object_id'] );
				break;
			case 'author':
				$url = get_author_posts_url( $object['object_id'] );
				break;
			default:
				//case 'url' is default behaviour
				$url = preg_replace('%nocache/$%', '', $object['url'], 1);
		}
		if ( $enable_integration ) {
			$this->integration_manager->enable_integrations();
		}
		if ( $url instanceof \WP_Error ) {
			return false;
		}

		$url_parts = parse_url( $url );
		if ( empty( $url_parts['path'] ) ) {
			$url_parts['path'] = '/';
		}
        if(!preg_match('/\/nocache\/?$/', $url_parts['path'])) {
            $url_parts['path'] = trailingslashit( $url_parts['path'] ) . 'nocache/';
        }
		if ( class_exists( 'http\Url' ) ) {
			/**
			 * @noinspection PhpUndefinedClassInspection
			 */
			$url = new \http\Url( $url_parts );
			$url = $url->toString();
		} else {
			$url = http_build_url( $url_parts );
		}
		if ( ! empty( $object['blog_id'] ) ) {
			restore_current_blog( $object['blog_id'] );
		}

		return apply_filters( 'shortpixel_critical_css_get_permalink', $url );
	}

	/**
	 * @return \ShortPixel\CriticalCSS\API\Background\Process
	 */
	public
	function get_api_queue() {
		return $this->api_queue;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Request
	 */
	public
	function get_request() {
		return $this->request;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Integration\Manager
	 */
	public
	function get_integration_manager() {
		return $this->integration_manager;
	}

	/**
	 * @return void
	 */
	public function uninstall() {
		// noop
	}

	/**
	 * @return string
	 */
	public function get_option_name() {
		return static::OPTIONNAME;
	}

	/**
	 * @return string
	 */
	public function get_lang_domain() {
		return static::LANG_DOMAIN;
	}

	/**
	 * @return string
	 */
	public function get_transient_prefix() {
		return static::TRANSIENT_PREFIX;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Log
	 */
	public function get_log() {
		return $this->log;
	}

	/**
	 * @return \ShortPixel\CriticalCSS\Template\Log
	 */
	public function get_template_log() {
		return $this->template_log;
	}

    /**
     * @param array $rec
     * @return mixed|void
     */
    public function get_item(array $rec)
    {
        if ($rec === null || ($item = (object)unserialize($rec['data'])) === null) {
            return false;
        }
        //var_dump($item);//exit();
        $item->url = $rec['url'];
        $item->type = $rec['type'];
        $item->object_id = $rec['object_id'];
        $item->template = $rec['template'];
        $item->post_type = $rec['post_type'];
        return $item;
    }

    protected function setup_components() {
		$components = $this->get_components();
		$this->set_component_parents( $components );
		foreach ( $components as $component ) {
			$component->init();
		}
	}

    public function retrieve_cached_css($request = false) {

        // In order to do the equiv. of get_current_page_type() for a URL, see: https://wordpress.stackexchange.com/questions/252998/detect-page-type-by-url-archive-single-page-author
        // and get_post_type( url_to_postid( $url ) ) for the post that doesn't seem to be covered by the above solution
        // This function will also be used in the admin to provide the CSS and also check if the CSS is not expired ( case in which EXPIRED will be displayed as Status)
        if($request) {
            $post_type = 'post' === $request['type'] ? get_post_type($request['object_id']) : false;
            $is_archive = false;// != $post_type ?
            $call_type = "ADMIN";
        } else {
            $request  = $this->request->get_current_page_type();
            $post_type = get_post_type();
            $is_archive = false !== $post_type && is_post_type_archive();
            $call_type = "FRONT";
        }

        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("CCSS $call_type request", $request);
        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("CCSS $call_type post_type", $post_type);

        $manual   = true;
        if ( 'post' === $request['type'] ) {
            $manual = apply_filters( 'shortpixel_critical_css_manual_post_css', true );
        }
        if ( 'term' === $request['type'] ) {
            $manual = apply_filters( 'shortpixel_critical_css_manual_term_css', true );
        }
        $manual_cache = null;
        //$fallback_css = trim( $this->settings_manager->get_setting( 'fallback_css' ) );
        $fallback_css = wp_get_custom_css_post('fallback_critical_css');
        $fallback_css = $fallback_css ? trim( wp_get_custom_css_post('fallback_critical_css')->post_content ) : '';

        if ( $manual ) {
            $manual_cache = trim(  $this->data_manager->get_manual_css() ?: '' );
        }

        $cache = trim( $this->data_manager->get_cache($call_type === 'ADMIN' ? $request : []) ?: '' );
        if ( 'on' === $this->settings_manager->get_setting( 'prioritize_manual_css' ) ) {
            $cache = $manual_cache;
            if ( empty( $cache ) ) {
                if ( false !== $post_type && 'on' === $this->settings_manager->get_setting( 'single_post_type_css_' . $post_type ) ) {
                    if ( $is_archive ) {
                        $cache = $this->settings_manager->get_setting( 'single_post_type_css_' . $post_type . '_archive_css' );
                    }
                    if ( empty( $cache ) ) {
                        $cache = $this->settings_manager->get_setting( 'single_post_type_css_' . $post_type . '_css' );
                    }
                }
                if ( 'term' === $request['type'] && 'on' === $this->settings_manager->get_setting( 'single_taxonomy_css_' . $post_type ) ) {
                    $cache = $this->settings_manager->get_setting( 'single_taxonomy_css_' . get_queried_object()->taxonomy . '_css' );
                }
            }
            if ( empty( $cache ) ) {
                $manual   = false;
                $cache    = $fallback_css;
            }
        } else {
            $manual = false;
        }

        if ( empty( $cache ) ) {
            $manual   = true;
            $cache    = $manual_cache;
        }
        if ( empty( $cache ) ) {
            $manual   = false;
            $cache    = $fallback_css;
        }

        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_INIT) && FileLog::instance()->log("CCSS $call_type get cache:", $cache);

        $cache = apply_filters( 'shortpixel_critical_css_print_styles_cache', $cache );

        return (object)['cache' => $cache, 'manual' => $manual];
    }

    /* AJAX calls */

	public function web_queue_run(int $queueId) {
		$this->run($this->web_check_data, $this->web_check_queue, $queueId);
	}

    public function api_run(int $queueId) {
       $this->run($this->api_data, $this->api_queue, $queueId);
    }

	/**
	 * run the call for a specific API or Web Check Queue item (manually triggered from the API Queue list)
	 *
	 * @param ListTableAbstract $dataSource
	 * @param ProcessAbstract $queue
	 * @param int $queueId
	 *
	 * @return void
	 */
	protected function run($dataSource, $queue, $queueId) {
		$rec = $dataSource->get_item($queueId);
		//var_dump($rec);
		header('Content-Type: application/json; charset=utf-8');

		if($rec === null) {
			exit(json_encode(["status" => API::STATUS_DONE]));
		}
		$item = $this->get_item($rec);
		if($item === false) {
			exit(json_encode(["status" => API::STATUS_DONE]));
		}
		//TODO MOVE TO API_QUEUE FROM HERE and use $this->api_queue->lock_process();
		$response = $queue->task((array)$item, true);
		if(is_object($response)) {
			$response = (array)$response;
		}

		if(!empty($response['status']) &&
		   in_array($response['status'], [
			   API::STATUS_DONE,
			   CheckTable::STATUS_DONE,
			   CheckTable::STATUS_EXISTS] ) ) {
			$queue->delete(intval($rec['id']));
		} elseif(!empty($response['status']) && !empty($response['status'])) {
			$queue->update(intval($rec['id']), [$response]);
		}

		echo json_encode($response);
		exit();
	}

	public function web_queue_remove(int $queueId)
	{
		$this->queue_remove($this->web_check_queue, $queueId);
	}

	public function api_queue_remove(int $queueId)
	{
		$this->queue_remove($this->api_queue, $queueId);
	}

	protected function queue_remove($queue, int $queueId)
	{
		header('Content-Type: application/json; charset=utf-8');
		$queue->delete($queueId);
		echo json_encode( [ 'status' => API::STATUS_REMOVED ] );
		exit;

	}
    /**
     * run the API call for a specific API Queue item (manually triggered from the API Queue list)
     */
    public function get_ccss() {
        $rec = $this->log_data->get_item(intval($_POST['log_id']));
        //var_dump($rec);
        $item = $this->get_item($rec);
        if($item === false) {
            exit(json_encode(["status" => API::STATUS_DONE]));
        }
        $css = $this->retrieve_cached_css((array)$item);
        $css->url = $this->get_permalink((array)$item);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($css);
        exit();
    }

	public function use_spio_key()
	{
		$key = ApiKeyTools::getSPIOApiKey();
		//no validation here
		ApiKeyTools::updateApiKey($key);
		$ret = [
			'status' => 'ok'
		];
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($ret);
		exit();
	}

	public function get_apikey()
	{
		$apiKey = ApiKeyTools::getApiKey();
		$ret = [
			'status' => 'ok',
			'key' => $apiKey,
		];
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($ret);
		exit();
	}

	public function update_apikey() {
		$key = trim( $_POST['key'] );
		if ( $key != '' ) {
			$validateRes = ApiKeyTools::validateAPIKey( $key );
		}


		if ( $key == '' || $validateRes['status'] ) {
			ApiKeyTools::updateApiKey( $key );
			$ret = [
				'status' => 'ok',
				'key'    => $key,
			];
		} else {
			$ret = [
				'status' => 'error',
				'error'  => $validateRes['error'],
				'key'    => $key,
			];
		}

		header( 'Content-Type: application/json; charset=utf-8' );
		echo json_encode( $ret );
		exit();
	}
  
	public function dismiss_notification() {
		$causer = !empty($_POST['causer'])?$_POST['causer']:null;
		if(!empty($causer) && in_array($causer, [
			//white list of possible notifications to dismiss to avoid spam in db
				'ccss_cron_disabled_notice',
				'ccss_spio_apikey_found'
			])) {
			$settings = $this->settings_manager->get_settings();
			$settings[$causer . '_dismissed'] = true;
			$this->settings_manager->update_settings($settings);
		}
		echo json_encode(['status' => 'ok']);
		exit;
	}

	function get_cache_mode_posttypes()
	{
		$ret = [];
		$postTypes = get_post_types([], 'objects');

		foreach($postTypes as $postType) {
			if($postType->public && !$postType->exclude_from_search) {
				$ret[$postType->name] = $postType->label;
			}
		}
		return $ret;
	}

	function get_cache_mode_templates()
	{
		$ret = [];

		//block theme such as twenty twenty-two, get templates list from the system
		if(function_exists('wp_is_block_theme') && wp_is_block_theme()) {
			$postTemplates = get_block_templates();
			foreach($postTemplates as $postTemplate) {
				$ret[ $postTemplate->slug ] = $postTemplate->title;
			}
		} else {
			//classic theme such as twenty twenty-one use template files list instead
			$postTemplates = wp_get_theme()->offsetGet('Template Files');

			if(!empty($postTemplates)) {
				foreach($postTemplates as $key => $value) {
					//use short path as label and path format of request template for key

					$ret[ str_replace( trailingslashit( WP_CONTENT_DIR ), '', $value ) ] = $key;
				}
			}
		}
		return $ret;
	}

	/**
	 * Actions on switching theme (i.e. cleaning templates options)
	 * @return void
	 */
	public function switch_theme()
	{
		//cleanup templates selection
		$settings = $this->settings_manager->get_settings();
        if($settings['cache_mode']['templates']) {
            $settings['template_values'] = $this->get_cache_mode_templates();
        }
		//unset($settings['cache_mode']['templates']);
		//$settings['template_values'] = [];
		$this->settings_manager->update_settings($settings);
		//purge cache
		$this->get_cache_manager()->purge_page_cache();
  }

	public function force_web_check() {
		$this->get_cache_manager()->purge_page_cache();
		$this->get_cache_manager()->reset_post_meta_cache();
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode(['status' => 'ok']);
		exit;
	}
	/**
	 * proxy contact form call to shortpixel.com
	 */
	public function contact() {

		$body = [
			'source' => sanitize_text_field( !empty($_POST['source'])?$_POST['source']:'' ),
			'quriobot_answers' => sanitize_text_field( !empty($_POST['quriobot_answers'])?$_POST['quriobot_answers']:'' ),
			'name' => sanitize_text_field( !empty($_POST['name'])?$_POST['name']:'' ),
			'email' => sanitize_text_field( !empty($_POST['email'])?$_POST['email']:'' ),
			'message' => '[CriticalCSS plugin contact form] ' . sanitize_textarea_field( !empty($_POST['message'])?$_POST['message']:'' ),
			'submit' => 'Send',
		];
		$options = [
			'body' => $body,
			'headers' => [
				'Content-Type' => 'application/x-www-form-urlencoded',
			],
		];

		$response = wp_remote_post( 'https://shortpixel.com/contact', $options );
		if($response instanceof \WP_Error) {
			$ret = [
				'status' => 'error',
			];
		} else {
			$ret = [
				'status' => 'ok',
				'response' => $response['body'],
			];
		}

		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($ret);
		exit();
	}
}
