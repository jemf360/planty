<?php


namespace ShortPixel\CriticalCSS\Queue\Log;


use ShortPixel\CriticalCSS\API;
use ShortPixel\CriticalCSS\Queue\ListTableAbstract;

class Table extends ListTableAbstract {
	public function __construct( array $args = [] ) {
		parent::__construct( [
			'singular' => __( 'Processed Log Item', shortpixel_critical_css()->get_lang_domain() ),
			'plural'   => __( 'Processed Log Items', shortpixel_critical_css()->get_lang_domain() ),
			'ajax'     => false,
		] );
	}

	/**
	 * @return array
	 */
	public function get_columns() {
		$columns = [
			'url'       => __( 'URL', shortpixel_critical_css()->get_lang_domain() ),
			'template'  => __( 'Template', shortpixel_critical_css()->get_lang_domain() ),
			'post_type' => __( 'Post type', shortpixel_critical_css()->get_lang_domain() ),
            'status'    => __( 'Status', shortpixel_critical_css()->get_lang_domain() ),
            'updated'  => __( 'Updated', shortpixel_critical_css()->get_lang_domain() ),
		];
		if ( is_multisite() ) {
			$columns = array_merge( [
				'blog_id' => __( 'Blog', shortpixel_critical_css()->get_lang_domain() ),
			], $columns );
		}

		return $columns;
	}

	protected function get_bulk_actions() {
		return [];
	}

	protected function do_prepare_items() {
		$wpdb        = shortpixel_critical_css()->wpdb;
		$table       = $this->get_table_name();
		$this->items = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} ORDER BY updated DESC LIMIT %d,%d", $this->start, $this->per_page ), ARRAY_A );
	}

	protected function get_table_name() {
		return shortpixel_critical_css()->log->get_table_name();
	}

	protected function column_blog_id( array $item ) {
		if ( empty( $item['blog_id'] ) ) {
			return __( 'N/A', shortpixel_critical_css()->get_lang_domain() );
		}

		$details = get_blog_details( [
			'blog_id' => $item['blog_id'],
		] );

		if ( empty( $details ) ) {
			return __( 'Blog Deleted', shortpixel_critical_css()->get_lang_domain() );
		}

		return $details->blogname;
	}

	/**
	 * @param array $item
	 *
	 * @return string
	 */
	protected function column_url( array $item ) {
		if ( ! empty( $item['template'] ) || ! empty( $item['post_type'] ) ) {
			return __( 'N/A', shortpixel_critical_css()->get_lang_domain() );
		}

		return shortpixel_critical_css()->get_permalink( $item );
	}

	/**
	 * @param array $item
	 *
	 * @return string
	 */
	protected function column_post_type( array $item ) {

		if ( ! empty( $item['post_type'] ) ) {
			return $item['post_type'];
		}

		return __( 'N/A', shortpixel_critical_css()->get_lang_domain() );
	}
	/**
	 * @param array $item
	 *
	 * @return string
	 */
	protected function column_template( array $item ) {

		if ( ! empty( $item['template'] ) ) {
			return $item['template'];
		}

		return __( 'N/A', shortpixel_critical_css()->get_lang_domain() );
	}

    /**
     * @param array $item
     *
     * @return string
     */
    protected function column_status( array $item ) {
        if ( ! empty( $item['data'] ) ) {
            $css = shortpixel_critical_css()->retrieve_cached_css($item);
            if($css->cache) {
                $data = unserialize($item['data']);
                if(!empty($data['result_status'])) {
                    $ret = $data['result_status'];
                    if(!empty($data['code'])) {
                        $shotsUrl = API::BASE_URL . 'screenshot/' . $data['code'] . '/';
                        $ret .= ' ( <a class="thickbox spccss-get" data-id="'. $item['id'] . '" href="">CSS</a> |'
                             .' <a class="spccss-screenshot" href="' . $shotsUrl . 'original.png" target="_blank">Original</a> | <a class="spccss-screenshot" href="' . $shotsUrl . 'critical.png" target="_blank">Critical</a> )';
                    }
                    return $ret;
                }
            }
            else {
                return "EXPIRED";
            }
        }

        return __( 'N/A', shortpixel_critical_css()->get_lang_domain() );
    }

    /**
     * @param array $item
     *
     * @return string
     */
    protected function column_updated( array $item ) {

        if ( ! empty( $item['data'] ) ) {
            $data = unserialize($item['data']);
            if(!empty($data['updated'])) {
                $ret = $data['updated'];
                return $ret;
            }
        }

        return __( 'N/A', shortpixel_critical_css()->get_lang_domain() );
    }

}