<?php

namespace ShortPixel\CriticalCSS\Settings;

use ComposePress\Core\Abstracts\Component;

/**
 * weDevs Settings API wrapper class
 *
 * @version 1.3 (27-Sep-2016)
 *
 * @author  Tareq Hasan <tareq@weDevs.com>
 * @link    https://tareq.co Tareq Hasan
 * @example example/oop-example.php How to use the class
 * @SuppressWarnings(PHPMD)
 * @property \ShortPixel\CriticalCSS $plugin
 */
class API extends Component {

	/**
	 * settings sections array
	 *
	 * @var array
	 */
	protected $settings_sections = [];

	/**
	 * Settings fields array
	 *
	 * @var array
	 */
	protected $settings_fields = [];

	/**
	 * Enqueue scripts and styles
	 */
	public function admin_enqueue_scripts() {
		wp_enqueue_style( 'wp-color-picker' );

		wp_enqueue_media();
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'beautify-css', plugin_dir_url( dirname(__FILE__ ) ) . 'assets/js/beautify-css.min.js' , [], false, true );
		wp_enqueue_script( 'highlightjs', plugin_dir_url( dirname(__FILE__ ) ) . 'assets/js/highlight.min.js' , [], false, true );
		wp_add_inline_script( 'highlightjs', $this->inline_scripts(), 'after' );
		wp_enqueue_script ( 'ccss_admin_ui', plugin_dir_url( dirname(__FILE__ ) ) . 'assets/js/ui.min.js' , [], false, true );
		wp_localize_script( 'ccss_admin_ui', 'ccssUILocal', [
			'forceWebCheck' => __( "The Critical CSS cache has been flushed and the Critical CSS generation will start again for each page when it's visited.", shortpixel_critical_css()->get_lang_domain() ),

		] );
		wp_enqueue_style ( 'ccss_admin_ui', plugin_dir_url( dirname(__FILE__ ) ) . 'assets/css/ui.min.css');
		wp_enqueue_style ( 'hightlightjs-theme', plugin_dir_url( dirname(__FILE__ ) ) . 'assets/css/atelier-lakeside.min.css');

    $this->enqueue_contact_form_scripts();
	}

	function inline_scripts() {
		return "
	
        jQuery(document).ready(function ($) {
	        $('.spccss_notice .dismiss-button').click( function(e){
		        const causer = $(this).parents('.spccss_notice').attr('data-dismissed-causer');
		        $.ajax( {
                    method     : 'post',
                    url        : 'admin-ajax.php',
                    data       : {
			        action : 'shortpixel_critical_css_dismiss',
                        causer : causer,
                    },
                    success    : function( response ) {
		            }
                });
                $(this).parents('.spccss_notice').find('.notice-dismiss').click();
            });
            
            $('#spccss_usekey').click( function(e){
                
                $.ajax( {
                    method     : 'post',
                    url        : 'admin-ajax.php',
                    data       : {
                        action : 'shortpixel_critical_css_usekey',
                        
                    },
                    success    : function( response ) {
                        location.reload();
                    }
                });
            }); 
        });
        ";
    }


	/**
	 * Enqueue contct form scripts
	 */
    function enqueue_contact_form_scripts()
    {
	    wp_enqueue_script( 'contact-form', plugin_dir_url( dirname(__FILE__ ) ) . 'assets/js/shortpixel-contact-form.min.js' , [], false, true );
	    wp_localize_script( 'contact-form', 'ccssContactFormLocal', [
		    'description' => __( "We understand that this might feel complicated at first. The plugin team would be more than happy to help or to receive feed-back on how the plugin could be improved. \nJust fill in the form below and hit \"Send\" to get in touch with us." ),
		    'name'        => __( "Your Name" ),
		    'email'       => __( "Your E-mail" ),
		    'texthint'    => __( "Please let us know the domain name, PHP and WP version and any other information you think is relevant." ),
		    'send'        => __( "Send" ),
		    'buttonTitle' => __( "Get help or send us feed-back." ),
		    'close'       => __( "Close" ),
		    'buttonText'  => __( "Need help?" ),
		    'formError'   => __( "Error sending the message. Please try again." ),
		    'formSuccess' => __( "Your message has been sent. Thank you!" ),
	    ] );
	    wp_enqueue_style ( 'contact-form-style', plugin_dir_url( dirname(__FILE__ ) ) . 'assets/css/shortpixel-contact-form.min.css');
    }

	/**
	 * Set settings sections
	 *
	 * @param array $sections setting sections array
	 *
	 * @return $this
	 */
	public function set_sections( $sections ) {
		$this->settings_sections = $sections;

		return $this;
	}

	/**
	 * Add a single section
	 *
	 * @param array $section
	 *
	 * @return $this
	 */
	public function add_section( $section ) {
		$section                   = wp_parse_args( $section, [
			'form' => true,
		] );
		$this->settings_sections[] = $section;

		return $this;
	}

	/**
	 * Set settings fields
	 *
	 * @param array $fields settings fields array
	 *
	 * @return $this
	 */
	public function set_fields( $fields ) {
		$this->settings_fields = $fields;

		return $this;
	}

	public function add_field( $section, $field ) {
		$defaults = [
			'name'  => '',
			'label' => '',
			'desc'  => '',
			'type'  => 'text',
		];

		$arg                                 = wp_parse_args( $field, $defaults );
		$this->settings_fields[ $section ][] = $arg;

		return $this;
	}

	/**
	 * Initialize and registers the settings sections and fileds to WordPress
	 *
	 * Usually this should be called at `admin_init` hook.
	 *
	 * This function gets the initiated settings sections and fields. Then
	 * registers them to WordPress and ready for use.
	 */
	public function admin_init() {
		//register settings sections
		foreach ( $this->settings_sections as $section ) {
			if ( false == get_option( $section['id'] ) ) {
				add_option( $section['id'] );
			}

			if ( isset( $section['desc'] ) && ! empty( $section['desc'] ) ) {
				$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
				$callback        = function() use( $section )  { echo str_replace('"', '\"', $section['desc']) ; }; //create_function( '', 'echo "' . str_replace( '"', '\"', $section['desc'] ) . '";' );
			} elseif ( isset( $section['callback'] ) ) {
				$callback = $section['callback'];
			} else {
				$callback = null;
			}

			add_settings_section( $section['id'], $section['title'], $callback, $section['id'] );
		}

		//register settings fields
		foreach ( $this->settings_fields as $section => $field ) {
			foreach ( $field as $option ) {

				$name     = $option['name'];
				$type     = isset( $option['type'] ) ? $option['type'] : 'text';
				$label    = isset( $option['label'] ) ? $option['label'] : '';
				$callback = isset( $option['callback'] ) ? $option['callback'] : [
					$this,
					'callback_' . $type,
				];

				$args = [
					'id'                => $name,
					'class'             => isset( $option['class'] ) ? $option['class'] : $name,
					'label_for'         => "{$section}[{$name}]",
					'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
					'name'              => $label,
					'section'           => $section,
					'size'              => isset( $option['size'] ) ? $option['size'] : null,
					'options'           => isset( $option['options'] ) ? $option['options'] : '',
					'std'               => isset( $option['default'] ) ? $option['default'] : '',
					'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
					'type'              => $type,
					'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : '',
                    'autocomplete'      => isset( $option['autocomplete'] ) ? $option['autocomplete'] : '',
					'min'               => isset( $option['min'] ) ? $option['min'] : '',
					'max'               => isset( $option['max'] ) ? $option['max'] : '',
					'step'              => isset( $option['step'] ) ? $option['step'] : '',
				];

				add_settings_field( "{$section}[{$name}]", $label, $callback, $section, $section, $args );
			}
		}

		// creates our settings in the options table
		foreach ( $this->settings_sections as $section ) {
			register_setting( $section['id'], $section['id'], [
				$this,
				'sanitize_options',
			] );
		}
	}
	/**
	 * Displays a hidden field for a settings field (still may use description)
	 *
	 * @param array $args settings field args
	 */
	public function callback_hidden( $args ) {
		$this->callback_text( $args );
	}
	/**
	 * Displays a url field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_url( $args ) {
		$this->callback_text( $args );
	}

	/**
	 * Displays a text field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_text( $args ) {

		$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$type        = isset( $args['type'] ) ? $args['type'] : 'text';
		$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
        $autocomplete= empty( $args['autocomplete'] ) ? '' : ' autocomplete="' . $args['autocomplete'] . '"';

		$text_input = sprintf( '<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $autocomplete );
		$text_input .= $this->get_field_description( $args );

		echo $text_input;
	}

	/**
	 * Get the value of a settings field
	 *
	 * @param string $option  settings field name
	 * @param string $section the section name this field belongs to
	 * @param string $default default text if it's not found
	 *
	 * @return string
	 */
	public function get_option( $option, $section, $default = '' ) {

		$options = $this->plugin->settings_manager->get_settings();

		if ( isset( $options[ $option ] ) ) {
			return $options[ $option ];
		}

		return $default;
	}

	/**
	 * Get field description for display
	 *
	 * @param array $args settings field args
	 *
	 * @return string
	 */
	public function get_field_description( $args ) {
		if ( ! empty( $args['desc'] ) ) {
			$desc = sprintf( '<p class="description">%s</p>', $args['desc'] );
		} else {
			$desc = '';
		}

		return $desc;
	}

    private function echo_option($option_html) {
        echo $option_html;return;
        echo wp_kses($option_html, [
            'input' => ['type' => [], 'class' => [], 'id' => [], 'name' => [], 'value' => [], 'placeholder' => [], 'min' => [], 'max' => [], 'step' => [], 'checked' => [], 'autocomplete' => []],
            'textarea' => ['class' => [], 'id' => [], 'name' => [], 'cols' => [], 'rows' => [], 'placeholder' => []],
            'select' => ['class' => [], 'id' => [], 'name' => [], 'value' => [], 'placeholder' => []],
            'option' => ['value' => [], 'selected' => [], 'style' => []],
            'label' => ['for' => [], 'style' => []],
            'p' => ['class' => [], 'style' => []],
            'span' => ['class' => [], 'style' => []],
            'strong' => ['class' => [], 'style' => []],
            'a' => ['href' => [], 'target' => [], 'class' => [], 'data-id' => [], 'style' => []],
            'table' => ['class' => [], 'style' => []],
            'th' => ['class' => [], 'style' => []],
            'tr' => ['class' => [], 'style' => []],
            'td' => ['class' => [], 'style' => []],
            'div' => ['class' => [], 'id' => [], 'style' => []],
            'style' => ['type' => []],
            ]);

    }

	/**
	 * Displays a number field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_number( $args ) {
		$value       = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$type        = isset( $args['type'] ) ? $args['type'] : 'number';
		$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';
		$min         = empty( $args['min'] ) ? '' : ' min="' . $args['min'] . '"';
		$max         = empty( $args['max'] ) ? '' : ' max="' . $args['max'] . '"';
		$step        = empty( $args['max'] ) ? '' : ' step="' . $args['step'] . '"';

		$number_input = sprintf( '<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step );
		$number_input .= $this->get_field_description( $args );

        $this->echo_option($number_input);
	}

	/**
	 * Displays a checkbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_checkbox( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );

		$check_input = '<fieldset>';
		$check_input .= sprintf( '<label for="wpuf-%1$s[%2$s]">', $args['section'], $args['id'] );
		$check_input .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id'] );
		$check_input .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked( $value, 'on', false ) );
		$check_input .= sprintf( '%1$s</label>', $args['desc'] );
		$check_input .= '</fieldset>';

        $this->echo_option($check_input);
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_multicheck( $args ) {

		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$html  = '<fieldset>';
		$html  .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id'] );
		foreach ( $args['options'] as $key => $label ) {
			$checked = isset( $value[ $key ] ) ? $value[ $key ] : '0';
			$html    .= sprintf( '<label for="wpuf-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
			$html    .= sprintf( '<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $checked, $key, false ) );
			$html    .= sprintf( '%1$s</label><br>', $label );
		}

		$html .= $this->get_field_description( $args );
		$html .= '</fieldset>';

        $this->echo_option($html);
	}

	/**
	 * Displays a multicheckbox a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_radio( $args ) {

		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$html  = '<fieldset>';

		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<label for="wpuf-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key );
			$html .= sprintf( '<input type="radio" class="radio" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked( $value, $key, false ) );
			$html .= sprintf( '%1$s</label><br>', $label );
		}

		$html .= $this->get_field_description( $args );
		$html .= '</fieldset>';

        $this->echo_option($html);
	}

	/**
	 * Displays a selectbox for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_select( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$html  = sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id'] );

		foreach ( $args['options'] as $key => $label ) {
			$html .= sprintf( '<option value="%s"%s>%s</option>', $key, selected( $value, $key, false ), $label );
		}

		$html .= sprintf( '</select>' );
		$html .= $this->get_field_description( $args );

        $this->echo_option($html);
	}

	/**
	 * Displays a textarea for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_textarea( $args ) {

		$value       = esc_textarea( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size        = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$placeholder = empty( $args['placeholder'] ) ? '' : ' placeholder="' . $args['placeholder'] . '"';

		$html = sprintf( '<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>', $size, $args['section'], $args['id'], $placeholder, $value );
		$html .= $this->get_field_description( $args );

        $this->echo_option($html);
	}

	/**
	 * Displays a textarea for a settings field
	 *
	 * @param array $args settings field args
	 *
	 * @return void
	 */
	public function callback_html( $args ) {
        $this->echo_option($args['desc']);
	}

	/**
	 * Displays a rich text textarea for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_wysiwyg( $args ) {

		$value = $this->get_option( $args['id'], $args['section'], $args['std'] );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : '500px';

		echo '<div style="max-width: ' . $size . ';">';

		$editor_settings = [
			'teeny'         => true,
			'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
			'textarea_rows' => 10,
		];

		if ( isset( $args['options'] ) && is_array( $args['options'] ) ) {
			$editor_settings = array_merge( $editor_settings, $args['options'] );
		}

		wp_editor( $value, $args['section'] . '-' . $args['id'], $editor_settings );

		echo '</div>';

		echo $this->get_field_description( $args );
	}

	/**
	 * Displays a file upload field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_file( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';
		$id    = $args['section'] . '[' . $args['id'] . ']';
		$label = isset( $args['options']['button_label'] ) ? $args['options']['button_label'] : __( 'Choose File' );

		$html = sprintf( '<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
		$html .= '<input type="button" class="button wpsa-browse" value="' . $label . '" />';
		$html .= $this->get_field_description( $args );

        $this->echo_option($html);
	}

	/**
	 * Displays a password field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_password( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html = sprintf( '<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value );
		$html .= $this->get_field_description( $args );

        $this->echo_option($html);
	}

	/**
	 * Displays a color picker field for a settings field
	 *
	 * @param array $args settings field args
	 */
	public function callback_color( $args ) {

		$value = esc_attr( $this->get_option( $args['id'], $args['section'], $args['std'] ) );
		$size  = isset( $args['size'] ) && ! is_null( $args['size'] ) ? $args['size'] : 'regular';

		$html = sprintf( '<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std'] );
		$html .= $this->get_field_description( $args );

        $this->echo_option($html);
	}

	public function sanitize_options( $options ) {

		if ( ! $options ) {
			return $options;
		}

		foreach ( $options as $option_slug => $option_value ) {
			$sanitize_callback = $this->get_sanitize_callback( $option_slug );

			// If callback is set, call it
			if ( $sanitize_callback ) {
				$options[ $option_slug ] = call_user_func( $sanitize_callback, $options );
				continue;
			}
		}

		return $options;
	}

	/**
	 * Get sanitization callback for given option slug
	 *
	 * @param string $slug option slug
	 *
	 * @return mixed string or bool false
	 */
	public function get_sanitize_callback( $slug = '' ) {
		if ( empty( $slug ) ) {
			return false;
		}

		// Iterate over registered fields and see if we can find proper callback
		foreach ( $this->settings_fields as $section => $options ) {
			foreach ( $options as $option ) {
				if ( $option['name'] != $slug ) {
					continue;
				}

				// Return the callback name
				return isset( $option['sanitize_callback'] ) && is_callable( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : false;
			}
		}

		return false;
	}

	/**
	 * Show navigations as tab
	 *
	 * Shows all the settings section labels as tab
	 */
	public function show_navigation() {
		$html = '<h2 class="nav-tab-wrapper">';

		$count = count( $this->settings_sections );

		// don't show the navigation if only one section exists
		if ( 1 === $count ) {
			return;
		}

		foreach ( $this->settings_sections as $tab ) {
			$html .= sprintf( '<a href="#%1$s" class="nav-tab" id="%1$s-tab">%2$s</a>', $tab['id'], $tab['title'] );
		}
        $html .= '<button class="ccss_top_actions" id="ccssFlushWebCheck" title="Clear the cache and force a Web Check on all pages."><span class="ccss-icon"></span></button>';
		$html .= '</h2>';

        echo wp_kses($html, 'post');
	}

	/**
	 * Show the section settings forms
	 *
	 * This function displays every sections in a different form
	 */
	public function show_forms() {
		?>
		<div class="metabox-holder">
			<?php foreach ( $this->settings_sections as $form ) { ?>
				<div id="<?php echo $form['id']; ?>" class="group" style="display: none;">
					<?php if ( $form['form'] ) : ?>
					<form method="post" action="<?php if ( is_multisite() ) : ?>../<?php endif; ?>options.php">
						<?php endif; ?>
						<?php
						do_action( 'wsa_form_top_' . $form['id'], $form );
						if ( $form['form'] ) {
							settings_fields( $form['id'] );
						}
						do_settings_sections( $form['id'] );
						do_action( 'wsa_form_bottom_' . $form['id'], $form );
						if ( $form['form'] && isset( $this->settings_fields[ $form['id'] ] ) ) :
							?>
							<div style="padding-left: 10px">
								<?php submit_button(); ?>
							</div>
						<?php endif; ?>
						<?php if ( $form['form'] ) : ?>
					</form>
				<?php endif; ?>
				</div>
			<?php } ?>
		</div>
        <div id="shortpixelContactFormContainer"></div>
		<?php
		$this->script();
	}

	/**
	 * Tabbable JavaScript codes & Initiate Color Picker
	 *
	 * This code uses localstorage for displaying active tabs
	 */
	public function script() {
		?>
		<script>
					jQuery(document).ready(function ($) {
						//Initiate Color Picker
						$('.wp-color-picker-field').wpColorPicker();

						// Switches option sections
						$('.group').hide();
						var activetab = '';
						if (typeof(localStorage) != 'undefined') {
							activetab = localStorage.getItem("activetab");
						}
						if (activetab != '' && $(activetab).length) {
							$(activetab).fadeIn();
						} else {
							$('.group:first').fadeIn();
						}
						$('.group .collapsed').each(function () {
							$(this).find('input:checked').parent().parent().parent().nextAll().each(
								function () {
									if ($(this).hasClass('last')) {
										$(this).removeClass('hidden');
										return false;
									}
									$(this).filter('.hidden').removeClass('hidden');
								});
						});

						if (activetab != '' && $(activetab + '-tab').length) {
							$(activetab + '-tab').addClass('nav-tab-active');
						}
						else {
							$('.nav-tab-wrapper a:first').addClass('nav-tab-active');
						}
						$('.nav-tab-wrapper a').click(function (evt) {
							$('.nav-tab-wrapper a').removeClass('nav-tab-active');
							$(this).addClass('nav-tab-active').blur();
							var clicked_group = $(this).attr('href');
							if (typeof(localStorage) != 'undefined') {
								localStorage.setItem("activetab", $(this).attr('href'));
							}
							$('.group').hide();
							$(clicked_group).fadeIn();
							evt.preventDefault();
						});

						$('.wpsa-browse').on('click', function (event) {
							event.preventDefault();

							var self = $(this);

							// Create the media frame.
							var file_frame = wp.media.frames.file_frame = wp.media({
								title: self.data('uploader_title'),
								button: {
									text: self.data('uploader_button_text')
								},
								multiple: false
							});

							file_frame.on('select', function () {
								attachment = file_frame.state().get('selection').first().toJSON();
								self.prev('.wpsa-url').val(attachment.url).change();
							});

							// Finally, open the modal
							file_frame.open();
						});

                        $('.spccss-close, .spccss-modal-background').on('click', function(){
                            $('.spccss-modal')[0].style.display = "none";
                            $('.spccss-modal-background')[0].style.display = "none";
                        });

                        $('.spccss-get').on('click', function(event) {

                            event.preventDefault();
                            event.stopPropagation();
                            const link = event.target;

                            $.ajax( {
                                method     : 'post',
                                url        : 'admin-ajax.php',
                                data       : {
                                    action : 'shortpixel_critical_css_get',
                                    log_id   : event.target.dataset['id'],
                                },
                                success    : function( response ) {
                                    /*
                                     * Open the Critical CSS modal, to display the CCSS that is assigned to that page.
                                     */
                                    let modal = document.getElementById('ccss_modal'),
                                    modalBackground = document.getElementById('ccss_modal_background');
                                    modal.querySelector('.spccss-modal-css').innerHTML = '<pre>' + hljs.highlightAuto(css_beautify(response.cache)).value + '</pre>';
                                    modal.style.display = 'flex';
                                    modalBackground.style.display= "block";
                                    var hideModal = function(event) {
                                        if (event.target == modal) {
                                            modal.style.display = "none";
                                            window.removeEventListener('click', hideModal);
                                        }
                                    }
                                    window.addEventListener('click', hideModal);
                                },
                                error : function(xhr, ajaxOptions, thrownError) {
                                    alert(thrownError + " Please retry.");
                                }
                            } );
                        });

                        $('.spccss-api-action').on('click', function(event) {

                            event.preventDefault();
                            event.stopPropagation();

                            let button = event.target, span, action, part;
                            action = button.dataset['action'];
                            if( ['api-remove', 'api-run', 'web-run', 'web-remove'].indexOf(action) === -1 ) {
                                return;
                            }
                            part = 'shortpixel_critical_css_' + action.replace('-', '_');

                            if(button.classList.contains('dashicons')) {
                                span = $(button);
                                button = button.parentElement;
                            } else {
                                span = $('span.dashicons', button);
                            }
                            span.addClass('dashicons-clock');
                            span.removeClass('dashicons-controls-play');
                            button.disabled = true;

                            $.ajax( {
                                method     : 'post',
                                url        : 'admin-ajax.php',
                                data       : {
                                    action : part,
                                    queue_id   : button.dataset['id'],
                                },
                                success    : function( response ) {
                                    //if finalized, will change to check and clicking on it will show details.
                                    //else (generate) will let the button call results
                                    if(response.status == '<?= \ShortPixel\CriticalCSS\API::STATUS_DONE ?>') {
                                        alert("Job completed, " + ( !!response.resultStatus ? "the Critical CSS is " + response.resultStatus +" and has a size of "
                                        + (new Number(response.size)).toLocaleString() + " bytes": "please check the Processed Log"));
                                        window.location.reload();
                                    } else if(response.status == '<?= \ShortPixel\CriticalCSS\Queue\Web\Check\Table::STATUS_DONE ?>' ||
                                        response.status == '<?= \ShortPixel\CriticalCSS\Queue\Web\Check\Table::STATUS_EXISTS ?>') {
                                        alert("Web check completed, added to the API Queue.");
                                        window.location.reload();
                                    }  else if(response.status == '<?= \ShortPixel\CriticalCSS\API::STATUS_QUEUED ?>') {
                                        alert("The job is queued for processing in the ShortPixel Critical CSS processing cloud (" + response.queue_id + ")");
                                    } else if(response.status == '<?= \ShortPixel\CriticalCSS\Queue\Web\Check\Table::STATUS_PENDING ?>'||
                                        response.status == '<?= \ShortPixel\CriticalCSS\Queue\Web\Check\Table::STATUS_PROCESSING ?>'
                                    ) {
                                        alert("The job is pending web check before pushing to ShortPixel Critical CSS processing queue");
                                    } else if(response.status == '<?= \ShortPixel\CriticalCSS\API::STATUS_REMOVED ?>') {
                                        $(button).closest('tr').hide();
                                        window.location.reload();
                                    }

                                    $(button).removeAttr( 'disabled' );
                                    span.addClass('dashicons-controls-play');
                                    span.removeClass('dashicons-clock');
                                },
                                error : function(xhr, ajaxOptions, thrownError) {
                                    alert(thrownError + " Please retry.");
                                    $(button).removeAttr( 'disabled' );
                                    span.addClass('dashicons-controls-play');
                                    span.removeClass('dashicons-clock');
                                }
                            } );

                        });
					});
		</script>
		<?php
		$this->_style_fix();
	}

	public function _style_fix() {
		global $wp_version;

		if ( version_compare( $wp_version, '3.8', '<=' ) ) :
			?>
			<style type="text/css">
				/** WordPress 3.8 Fix **/
				.form-table th {
					padding: 20px 10px;
				}

				#wpbody-content .metabox-holder {
					padding-top: 5px;
				}
			</style>
			<?php
		endif;
	}

	/**
	 *
	 */
	public function init() {
		add_action( 'admin_enqueue_scripts', [
			$this,
			'admin_enqueue_scripts',
		] );
	}
}
