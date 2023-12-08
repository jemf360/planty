<?php


namespace ShortPixel\CriticalCSS;

use ComposePress\Core\Abstracts\Component;

class Log extends Component {

	/**
	 *
	 */
	public function init() {
		add_action( 'shortpixel_critical_css_purge_log', [ $this, 'purge' ] );
	}

	public function create_table() {
		$wpdb = $this->wpdb;
		if ( ! function_exists( 'dbDelta' ) ) {
			include_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$charset_collate = $wpdb->get_charset_collate();
		$table           = $this->get_table_name();
		$sql             = "CREATE TABLE $table (
  id MEDIUMINT(9) NOT NULL AUTO_INCREMENT,
  template  VARCHAR(255),
  post_type  VARCHAR(255),
  object_id  BIGINT(10),
  type VARCHAR (10),
  url TEXT,
  data TEXT,
  updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP,";
		if ( is_multisite() ) {
			$sql .= "\n" . 'blog_id BIGINT(20),';
		}
		dbDelta( "$sql\nPRIMARY KEY  (id)\n) {$charset_collate};" );
	}

	public function insert( $item ) {
		if(empty($item['url'])) {
			return;
		}
		$wpdb = $this->wpdb;

		$data = $item;

		$item = [
			'template'  => !empty($data['template'])?$data['template']:null,
			'object_id' => !empty($data['object_id'])?$data['object_id']:null,
			'type'      => !empty($data['type'])?$data['type']:null,
			'post_type' => !empty($data['post_type'])?$data['post_type']:null,
			'url'       => !empty($data['url'])?$data['url']:'',
            'data'      => serialize([
                'result_status' => !empty($data['result_status'])?$data['result_status']:'',
                'queue_id' => !empty($data['queue_id'])?$data['queue_id']:'',
                'updated' => !empty($data['updated'])?$data['updated']:'',
                'code' => !empty($data['code'])?$data['code']:'',
                'css_hash' =>!empty($data['css_hash'])?$data['css_hash']:''
            ])
		];
		if ( is_multisite() ) {
			$item['blog_id'] = $data['blog_id'];
		}
        $tableName = $this->get_table_name();
        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_Q) && FileLog::instance()->log("INSERT INTO $tableName: ", $data);
        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_Q) && FileLog::instance()->log("CALLED BY: ", debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        if(!empty($data['post_type'])) {
            $wpdb->query( $wpdb->prepare("DELETE FROM $tableName WHERE post_type = %s", $item['post_type']) );
        } elseif(!empty($data['template'])) {
            $wpdb->query( $wpdb->prepare("DELETE FROM $tableName WHERE template = %s", $item['template']) );
        } else {
            $wpdb->query( $wpdb->prepare("DELETE FROM $tableName WHERE url = %s", $item['url']) );
        }
		$wpdb->insert( $tableName, $item );
	}

	public function get_table_name() {
		$wpdb = $this->wpdb;
		if ( is_multisite() ) {
			return "{$wpdb->base_prefix}{$this->plugin->get_safe_slug()}_processed_items";
		}

		return $table = "{$wpdb->prefix}{$this->plugin->get_safe_slug()}_processed_items";

	}

	public function purge() {
        return; //do not purge for now.
        $tableName = $this->get_table_name();
        (SPCCSS_DEBUG & FileLog::DEBUG_AREA_DB) && FileLog::instance()->log("TRUNCATE TABLE $tableName. STACK: ", debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
		$this->wpdb->query( "TRUNCATE TABLE $tableName" );
	}
}