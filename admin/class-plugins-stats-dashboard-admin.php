<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://dream-encode.com
 * @since      1.0.0
 *
 * @package    Plugins_Stats_Dashboard
 * @subpackage Plugins_Stats_Dashboard/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Plugins_Stats_Dashboard
 * @subpackage Plugins_Stats_Dashboard/admin
 * @author     David Baumwald <david.baumwald@gmail.com>
 */
class Plugins_Stats_Dashboard_Admin {

	/**
	 * Plugin options.
	 *
	 * @since 		1.0.0
	 * @access 		private
	 * @var 		string 			$options    The plugin options.
	 */
	private $options;

	/**
	 * Settings sections.
	 *
	 * @since 		1.0.0
	 * @access 		private
	 * @var 		string 			$settings_sections
	 */
	private $settings_sections;

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->set_options();

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Plugins_Stats_Dashboard_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Plugins_Stats_Dashboard_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */


		$screen = get_current_screen();						

		if ( $screen->id == "dashboard" ) {
			wp_enqueue_style( $this->plugin_name.'-progress-bar', plugin_dir_url( __FILE__ ) . '../vendor/nprogress/nprogress.css', array(), $this->version, 'all' );
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/dist/css/plugins-stats-dashboard-admin.css', array(), $this->version, 'all' );
		}

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		$screen = get_current_screen();						

		if ( $screen->id == "dashboard" ) {
			wp_enqueue_script( $this->plugin_name.'-progress-bar', plugin_dir_url( __FILE__ ) . '../vendor/nprogress/nprogress.js', array( 'jquery' ), $this->version, false );

			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/dist/js/plugins-stats-dashboard-admin.js', array( 'jquery', $this->plugin_name.'-progress-bar' ), $this->version, false );

			$params = array(
  				'security' => wp_create_nonce( $this->plugin_name.'-admin' ),
			);

			wp_localize_script( $this->plugin_name, 'ajax_object', $params );
		}
	}


	/**
	 * Sets the class variable $options
	 *
	 * @since    1.0.0
	 */
	private function set_options() {

		$this->options = get_option( $this->plugin_name );

	} 


	/**
	 * Updated options (Checking for our plugin key)
	 *
	 * @since  1.0.0
	 */
	public function updated_option( $option_name, $old_value, $value ) {
	
		if ( $option_name == $this->plugin_name ) {
			delete_transient( 'plugins-stats-dashboard-results' );
		}
	
	}


	/**
	 * AJAX action for the getting plugin stats data
	 *
	 * @since    1.0.0
	 */
	public function plugin_stats_dashboard_ajax() {

		if ( empty($_POST) || !check_ajax_referer( $this->plugin_name.'-admin', 'security' ) ) wp_die('Security check');

		$current_stat = set_transient( $this->plugin_name.'-current-stat', $_POST['current_stat'], YEAR_IN_SECONDS );

		if ( false === ( $plugin_info = get_transient( $this->plugin_name.'-results' ) ) ) {
			$args = (object) array( 
				'author' => $this->options['author'],
				'fields' => array(
					'downloaded' => true,
					'active_installs' => true
				)
			);
		 
		    $request = array( 'action' => 'query_plugins', 'timeout' => 15, 'request' => serialize( $args ) );
		 
		    $url = 'http://api.wordpress.org/plugins/info/1.0/';
		 
		    $response = wp_remote_post( $url, array( 'body' => $request ) );
		 
		    $plugin_info = unserialize( $response['body'] );

		    set_transient( $this->plugin_name.'-results', $plugin_info, $this->results_transient_length() );
		 }

		 wp_send_json($plugin_info);

	}


	/**
	 * Add the dashboard widget.
	 *
	 * @since  1.0.0
	 */
	public function add_dashboard_widget() {
	
		wp_add_dashboard_widget( 'plugin-stats-dashboard', __( 'My Plugin Stats', $this->plugin_name ), array( $this, 'display_dashboard_widget' ) );
	
	}


	/**
	 * Display the dashboard widget.
	 *
	 * @since  1.0.0
	 */
	public function display_dashboard_widget() {
	
		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-widget.php' );
	
	}


	/**
	 * Adds a "Settings" link to the plugins page
	 *
	 * @since 		1.0.0
	 * @param 		array 		$links 		The current array of links
	 * @return 		array 					The modified array of links
	 */
	public function plugin_settings_link( $links ) {

		$links[] = sprintf( '<a href="%s">%s</a>', esc_url( admin_url( 'admin.php?page=' . $this->plugin_name ) ), esc_html__( 'Settings', $this->plugin_name ) );

		return $links;

	} 


	/**
	 * Adds a settings page link to a menu
	 *
	 * @since 		1.0.0
	 */
	public function add_settings_page_to_menu() {

		add_menu_page( 
			__( 'Plugins Stats Dashboard', $this->plugin_name ),
			__( 'Plugins Stats Dashboard', $this->plugin_name ),
			'manage_options', 
			$this->plugin_name,
			array( $this, 'settings_view' ),
			'dashicons-chart-line',
			99
		);

	}


	/**
	 * Creates the settings page
	 *
	 * @since 		1.0.0
	 * @return 		void
	 */
	public function settings_view() {

		include( plugin_dir_path( __FILE__ ) . 'partials/' . $this->plugin_name . '-settings.php' );

	}


	/**
	 * Render settings fields based on type.
	 *
	 * @since  1.0.0
	 * @return 	string
	 */
	function settings_field_render( $field_data ) {	

		$html = '';
		$classes = array();					
		
		if ( is_array( $field_data ) && !empty( $field_data ) ) {
			$field_key = $field_data['key'];
			$field_name = $this->plugin_name.'['.$field_key.']';
			$field_id = $this->plugin_name.'-'.$field_data['key'];
			$field_value = isset( $this->options[$field_key] ) ? $this->options[$field_key] : '';
			$field_params = $field_data['args'];
			
			$field_attrs = '';
			
			if ( is_array( $field_params ) && !empty( $field_params ) ) {						
				if (isset( $field_params['attrs'] ) && is_array( $field_params['attrs'] ) && !empty( $field_params['attrs'] ) ) {
					foreach ( $field_params['attrs'] as $attr_key => $attr_val ) {
						$field_attrs .= ' '.$attr_key.'="'.esc_attr( $attr_val ).'"'; 
					}
				}
				
				if ( isset( $field_params['toggle'] ) && !empty( $field_params['toggle'] ) ) {
					$field_attrs .= ' data-toggle="'.$this->plugin_name.'-'.$field_params['toggle'].'" data-toggle-value="'.$field_params['toggle_value'].'"'; 
				}
					
				if ( isset( $field_params['toggled_by'] ) && !empty( $field_params['toggled_by'] ) ) {
					$field_attrs .= ' data-toggled-by="'.$this->plugin_name.'-'.$field_params['toggled_by'].'"'; 
					
					$toggler_value = $this->options[$field_params['toggled_by']];
					
					if ( $toggler_value != $this->settings_sections['Settings']['fields'][$field_params['toggled_by']]['args']['toggle_value'] ) {
						$classes[] = ' conditional-hidden';
					}
				}
				
				if ( isset( $field_params['class'] ) && !empty( $field_params['class'] ) ) {
					foreach ( $field_params['class'] as $class ) {
						$classes[] = $class;
					}
				}
				
				if ( !empty( $classes ) ) {
					$field_attrs .= ' class="'.implode( " ", $classes ).'"';
				}
			}			
			
			switch ( $field_data['type'] ) {
				case 'text':		
				case 'hidden':		
				case 'password':		
				case 'number':	
				case 'email':			
				case 'tel':		
				case 'url':
				case 'search':		
				case 'range':	
				case 'date':		
				case 'datetime':	
				case 'datetime-local':	
				case 'month':	
				case 'week':
				case 'time':									
					$html .= '<input type="'.$field_data['type'].'" name="'.esc_attr( $field_name ).'" id="'.esc_attr( $field_id ).'" value="'.esc_attr( $field_value ).'"'.$field_attrs.'>';
					break;
					
				case 'checkbox':
					$checked = $field_value == $field_params['value'];
							
					$input = '<input type="checkbox" name="'.esc_attr( $field_name ).'" id="'.esc_attr( $field_id ).'" value="'.esc_attr( $field_params['value'] ).'"'.( $checked  ? ' checked' : '' ).$field_attrs.' />';
					
					if ( isset( $field_params['wrap_label'] ) && false !== $field_params['wrap_label'] ) {
						$input = '<label for="'.esc_attr( $field_name.'_'.$value ).'">'.$input.$label.'</label> ';
					}
					
					$html .= $input;
					 
					break;

				case 'checkbox_multi':
					if (is_array( $field_params ) && isset( $field_params['options'] ) && !empty( $field_params['options'] ) ) {
						foreach ($field_params['options'] as $value => $label) {
							$checked = false;
							
							$checked = is_array( $field_value ) && in_array( $value, $field_value );
							
							$input = '<input type="checkbox" '.checked( $checked, true, false ).' name="'.esc_attr( $field_name ).'[]" value="'.esc_attr( $k ).'" id="'.esc_attr( $field_id.'_'.$value ).'"'.$field_attrs.' />';
							
							if ( $field_params['wrap_label'] ) {
								$input = '<label for="'.esc_attr( $field_name.'_'.$value ).'">'.$input.$label.'</label> ';
							}
							
							$html .= $input;
						}
					}
					break;

				case 'radio':
					if ( is_array( $field_params ) && isset( $field_params['options'] ) && !empty( $field_params['options'] ) ) {
						foreach ( $field_params['options'] as $value => $label )  {
							$checked = false;
							
							$checked = $value == $field_value;
							
							$input = '<input type="radio" '.checked( $checked, true, false ).' name="'.esc_attr( $field_name ).'" value="'.esc_attr( $value ).'" id="'.esc_attr( $field_id.'_'.$value ).'"'.$field_attrs.' />';
							
							if ( $field_params['wrap_label'] ) {
								$input = '<label for="'.esc_attr( $field_name.'_'.$value ).'">'.$input.$label.'</label> ';
							}
						
							$html .= $input;
						}
					}
					break;
					
				case 'select':
					$is_multiple = false;
					$select_name = esc_attr( $field_name );
					
					if ( is_array( $field_params ) && isset( $field_params['multiple'] ) && $field_params['multiple'] !== false ) {
						$is_multiple = true;
						
						$select_name .= '[]';
					}
					
					$html .= '<select name="'.$select_name.'" id="'.esc_attr( $field_id ).'"'.$field_attrs;
					
					if ($is_multiple) {
						$html .= ' multiple="multiple"';
					}
					
					$html .= '>'; 
					 
					if ( is_array( $field_params ) && isset( $field_params['options'] ) && !empty( $field_params['options'] ) ) {
						$html .= $this->select_choices( $field_params['options'], $field_value );
					}
						
					$html .= '</select>';
					break;

				case 'textarea':
						$html .= '<textarea id="'.esc_attr( $field_id ).'" rows="'.$field_params['rows'].'" cols="'.$field_params['rows'].'" name="'.esc_attr( $field_name ).'"'.$field_attrs.'>'.$field_value.'</textarea>';
						break;

				case 'image':
					$image_thumb = '';
					
					if ($field_value) {
						$image_thumb = wp_get_attachment_thumb_url( $field_value );
					}
					
					$html .= '<img id="'.esc_attr( $field_name ).'_preview" class="image_preview" src="'.$image_thumb.'" /><br/>';
					$html .= '<input id="'.esc_attr( $field_name ).'_button" type="button" data-uploader_title="'.__( 'Upload an image', $this->plugin_name ).'" data-uploader_button_text="'.__( 'Use image', $this->plugin_name ).'" class="image_upload_button button" value="'.__('Upload new image', $this->plugin_name).'" />';
					$html .= '<input id="'.esc_attr( $field_name ).'_delete" type="button" class="image_delete_button button" value="'.__( 'Remove image', $this->plugin_name) . '" />';
					$html .= '<input id="'.esc_attr( $field_name ).'" class="image_data_field" type="hidden" name="'.esc_attr( $field_name ).'" value="'.$field_value.'"/>';
					break;
			}
			 
			if ( is_array( $field_params) && !empty( $field_params ) ) {
				if ( isset( $field_params['hint']) && !empty( $field_params['hint'] ) ) {
					$html .= '<p class="description" id="'.$this->plugin_name.'-'.esc_attr( $field_name ).'-description">'.$field_params['hint'].'</p>'; 
				}
			}
		}
     
    	echo $html;	

	}


	/**
	 * Create select options
	 *
	 * @since  1.0.0
	 * @return 	string
	 */
	function select_choices( $choices, $values ) {	

		$choices_html  = '';
			
		if ( empty( $choices ) ) return $choices_html;	
		
		foreach ($choices as $id => $value ) {			
			if ( is_array( $value ) ) {
				$choices_html .= '<optgroup label="'.esc_attr( $id ).'">';				

				$choices_html .= $this->select_choices( $value, $values );		
				
				$choices_html .= '</optgroup>';	

				continue;				
			}		

			$option_value = html_entity_decode( $id );
			
			if ( is_array( $values ) ) {			
				$is_selected = array_search( $option_value, $values );
			} else {
				$is_selected = $option_value == $values;
			}	
			
			$choices_html .= '<option value="'.esc_attr( $id ).'"';				
			
			if ( $is_selected !== false ) {				
				$choices_html .= ' data-i="'.(int) $is_selected.'" selected';					
			}			
			
			$choices_html .= '>'.$value.'</option>';			
		}	
		
		return $choices_html;	

	}


	/**
	 * Settings section callback
	 *
	 * @since  1.0.0
	 * @return 	string
	 */
	
	function settings_section_callback( $args ) {
		echo wpautop( __( 'Settings for Plugins Stats Dashboard plugin.', $this->plugin_name ) );
	}


	/**
	 * Create settings
	 *
	 * @since  1.0.0
	 * @return 	string
	 */
	function settings_init() {	
		$this->settings_sections['Settings'] = array(
			'name' => __( 'Settings', $this->plugin_name ),
			'description' => __( 'Settings for Plugins Stats Dashboard plugin.', $this->plugin_name ),
			'id' => 'settings',
			'fields' => array(				
				'author' => array(
					'key' => 'author',
					'label' => __( 'Author', $this->plugin_name ),
					'type' => 'text',
					'default' => array(),
					'required' => true,
					'args' => array(
						'validator' => '',
						'hint' => __( 'WordPress.org username for the plugin author.', $this->plugin_name ),
					)
				),
				'update_frequency' => array(
					'key' => 'update_frequency',
					'label' => __( 'Update Frequency', $this->plugin_name ),
					'type' => 'select',
					'default' => '',
					'args' => array(
						'multiple' => false,
						'options' => $this->update_frequency_select_options(),
						'validator' => '',
						'hint' => __( 'How often should stats from WordPress.org be refreshed?.', $this->plugin_name ),
						'class' => array(
							'select2',
						),
					)
				)
			),
		); 
		
		$sections = apply_filters( 'de/'.$this->plugin_name.'/alter_settings_sections', $this->settings_sections ) ;
		
		register_setting( $this->plugin_name, $this->plugin_name );
	
		foreach ( $this->settings_sections as $section ) {
			// Section
			// id: String for use in the 'id' attribute of tags.
			// title: Title of the section.
			// callback: Function that fills the section with the desired content. 
			// page: The menu page on which to display this section. 
			$section_id = $section['id'];
			$section_callback = $section['id'].'_section_callback';

			add_settings_section(
				$section_id,
				__( $section['name'], $this->plugin_name ),
				array( $this, $section_callback ),
				$this->plugin_name
			);

			if ( $section['fields'] ) {
				foreach ( $section['fields'] as $field_key => $field_data ) {
					// id: String for use in the 'id' attribute of tags.
					// title: Title of the field.
					// callback: Function that fills the field with the desired inputs as part of the larger form. Passed $args array. 
					// page: The menu page on which to display this field. 
					// section: The section of the settings page in which to show the box (default or section added above)
					// args: Additional arguments that are passed to the $callback function
					$field_id = $field_key;
					
					// Default field args
					$field_data['label_for'] = $field_id;
					
					add_settings_field( 
						$field_id,
						__( $field_data['label'], $this->plugin_name ),
						array( $this, 'settings_field_render' ),
						$this->plugin_name,
						$section_id,
						$field_data
					);
				}
			}
		}
	}

	protected function current_stat_select_options() {

		$options_array = array(
			'downloaded' => __( 'Downloads', $this->plugin_name ),
			'active_installs' => __( 'Active Installs', $this->plugin_name ),
			'version' => __( 'Version', $this->plugin_name ),
		);

		return apply_filters( 'de/'.$this->plugin_name.'/current_stat_select_options', $options_array );

	}

	protected function update_frequency_select_options() {

		$options_array = array(
			'5m' => __( '5 Minutes', $this->plugin_name ),
			'15m' => __( '15 Minutes', $this->plugin_name ),
			'30m' => __( '30 Minutes', $this->plugin_name ),
			'1h' => __( '1 Hour', $this->plugin_name ),
			'6h' => __( '6 Hours', $this->plugin_name ),
			'12h' => __( '12 Hours', $this->plugin_name ),
			'24h' => __( '24 Hours', $this->plugin_name ),		
			'7d' => __( '7 Days', $this->plugin_name ),			
		);

		return apply_filters( 'de/'.$this->plugin_name.'/update_frequency_select_options', $options_array );

	}

	protected function update_frequency_text() {

		preg_match( "/^(\d+)(m|h|d)$/i", $this->options['update_frequency'], $matches );

		$update_frequency_text = __( '*Updated every ', $this->plugin_name );

		switch ( $matches[2] ) {
			case 'm':
				$update_frequency_text .= sprintf( _n( 'minute', '%s minutes', $matches[1], $this->plugin_name ), $matches[1] );
				break;
			case 'h':
				$update_frequency_text .= sprintf( _n( 'hour', '%s hours', $matches[1], $this->plugin_name ), $matches[1] );
				break;
			case 'd':
				$update_frequency_text .= sprintf( _n( 'day', '%s days', $matches[1], $this->plugin_name ), $matches[1] );
				break;
		}

		return apply_filters( 'de/'.$this->plugin_name.'/update_frequency_text', $update_frequency_text );

	}

	protected function results_transient_length() {

		preg_match( "/^(\d+)(m|h|d)$/i", $this->options['update_frequency'], $matches );

		switch ( $matches[2] ) {
			case 'm':
				$results_transient_length = MINUTE_IN_SECONDS*intval($matches[1]);
				break;
			case 'h':
				$results_transient_length = HOUR_IN_SECONDS*intval($matches[1]);
				break;
			case 'd':
				$results_transient_length = DAY_IN_SECONDS*intval($matches[1]);
				break;
		}

		return apply_filters( 'de/'.$this->plugin_name.'/results_transient_length', $results_transient_length );

	}
}
