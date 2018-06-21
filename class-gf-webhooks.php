<?php

// Load Feed Add-On Framework.
GFForms::include_feed_addon_framework();

/**
 * Webhooks integration using the Add-On Framework.
 *
 * @see GFFeedAddOn
 */
class GF_Webhooks extends GFFeedAddOn {

	/**
	 * Defines the version of the Webhooks Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined in webhooks.php
	 */
	protected $_version = GF_WEBHOOKS_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '2.2';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformswebhooks';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformswebhooks/webhooks.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string
	 */
	protected $_url = 'http://www.gravityforms.com';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms Webhooks Add-On';

	/**
	 * Defines the short title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The short title of the Add-On.
	 */
	protected $_short_title = 'Webhooks';

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines if feeds can be processed asynchronously.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    bool
	 */
	protected $_async_feed_processing = true;

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_webhooks';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_webhooks';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_webhooks_uninstall';

	/**
	 * Defines the capabilities to add to roles by the Members plugin.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    array $_capabilities Capabilities to add to roles by the Members plugin.
	 */
	protected $_capabilities = array( 'gravityforms_webhooks', 'gravityforms_webhooks_uninstall' );

	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 *
	 * @return $_instance
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Enqueue needed stylesheets.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function styles() {

		$styles = array(
			array(
				'handle'  => $this->_slug . '_form_settings',
				'src'     => $this->get_base_url() . '/css/form_settings.css',
				'version' => $this->_version,
				'enqueue' => array( array( 'admin_page' => array( 'form_settings' ) ) ),
			),
		);

		return array_merge( parent::styles(), $styles );

	}





	// # FEED SETTINGS -------------------------------------------------------------------------------------------------

	/**
	 * Setup fields for feed settings.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GF_Webhooks::get_header_choices()
	 *
	 * @return array
	 */
	public function feed_settings_fields() {

		return array(
			array(
				'fields' => array(
					array(
						'label'          => esc_html__( 'Name', 'gravityformswebhooks' ),
						'name'           => 'feedName',
						'type'           => 'text',
						'class'          => 'medium',
						'required'       => true,
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Name', 'gravityformswebhooks' ),
							esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gravityformswebhooks' )
						),
					),
				),
			),
			array(
				'fields' => array(
					array(
						'label'          => esc_html__( 'Request URL', 'gravityformswebhooks' ),
						'name'           => 'requestURL',
						'type'           => 'text',
						'class'          => 'large merge-tag-support mt-position-right mt-hide_all_fields',
						'required'       => true,
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Request URL', 'gravityformswebhooks' ),
							esc_html__( 'Enter the URL to be used in the webhook request.', 'gravityformswebhooks' )
						),
					),
					array(
						'label'          => esc_html__( 'Request Method', 'gravityformswebhooks' ),
						'name'           => 'requestMethod',
						'type'           => 'select',
						'default_value'  => 'POST',
						'required'       => true,
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Request Method', 'gravityformswebhooks' ),
							esc_html__( 'Select the HTTP method used for the webhook request.', 'gravityformswebhooks' )
						),
						'choices'        => array(
							array(
								'label' => esc_html__( 'GET', 'gravityformswebhooks' ),
								'value' => 'GET',
							),
							array(
								'label' => esc_html__( 'POST', 'gravityformswebhooks' ),
								'value' => 'POST',
							),
							array(
								'label' => esc_html__( 'PUT', 'gravityformswebhooks' ),
								'value' => 'PUT',
							),
							array(
								'label' => esc_html__( 'PATCH', 'gravityformswebhooks' ),
								'value' => 'PATCH',
							),
							array(
								'label' => esc_html__( 'DELETE', 'gravityformswebhooks' ),
								'value' => 'DELETE',
							),
						),
					),
					array(
						'label'          => esc_html__( 'Request Format', 'gravityformswebhooks' ),
						'name'           => 'requestFormat',
						'type'           => 'select',
						'default_value'  => 'json',
						'required'       => true,
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Request Format', 'gravityformswebhooks' ),
							esc_html__( 'Select the format for the webhook request.', 'gravityformswebhooks' )
						),
						'choices'        => array(
							array(
								'label' => esc_html__( 'JSON', 'gravityformswebhooks' ),
								'value' => 'json',
							),
							array(
								'label' => esc_html__( 'FORM', 'gravityformswebhooks' ),
								'value' => 'form',
							),
						),
					),
				),
			),
			array(
				'fields' => array(
					array(
						'label'          => esc_html__( 'Request Headers', 'gravityformswebhooks' ),
						'name'           => 'requestHeaders',
						'type'           => 'generic_map',
						'required'       => false,
						'merge_tags'     => true,
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Request Headers', 'gravityformswebhooks' ),
							esc_html__( 'Setup the HTTP headers to be sent with the webhook request.', 'gravityformswebhooks' )
						),
						'key_field'      => array(
							'choices'      => $this->get_header_choices(),
							'custom_value' => true,
							'title'        => esc_html__( 'Name', 'gravityformswebhooks' ),
						),
						'value_field'    => array(
							'choices'      => 'form_fields',
							'custom_value' => true,
						),
					),
				),
			),
			array(
				'fields' => array(
					array(
						'label'          => esc_html__( 'Request Body', 'gravityformswebhooks' ),
						'name'           => 'requestBodyType',
						'type'           => 'radio',
						'default_value'  => 'all_fields',
						'horizontal'     => true,
						'required'       => true,
						'onchange'       => "jQuery(this).closest('form').submit();",
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Request Body', 'gravityformswebhooks' ),
							esc_html__( 'Select if all fields or select fields should be sent with the webhook request.', 'gravityformswebhooks' )
						),
						'choices'        => array(
							array(
								'label' => esc_html__( 'All Fields', 'gravityformswebhooks' ),
								'value' => 'all_fields',
							),
							array(
								'label' => esc_html__( 'Select Fields', 'gravityformswebhooks' ),
								'value' => 'select_fields',
							),
						),
					),
					array(
						'label'          => esc_html__( 'Field Values', 'gravityformswebhooks' ),
						'name'           => 'fieldValues',
						'type'           => 'generic_map',
						'required'       => true,
						'merge_tags'     => true,
						'dependency'     => array( 'field' => 'requestBodyType', 'values' => array( 'select_fields' ) ),
						'tooltip'        => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Field Values', 'gravityformswebhooks' ),
							esc_html__( 'Setup the fields to be sent in the webhook request.', 'gravityformswebhooks' )
						),
						'value_field'    => array(
							'choices'      => 'form_fields',
							'custom_value' => true,
						),
					),
				),
			),
			array(
				'fields' => array(
					array(
						'name'           => 'feedCondition',
						'type'           => 'feed_condition',
						'label'          => esc_html__( 'Webhook Condition', 'gravityformswebhooks' ),
						'checkbox_label' => esc_html__( 'Enable Condition', 'gravityformswebhooks' ),
						'instructions'   => esc_html__( 'Execute Webhook if', 'gravityformswebhooks' ),
					),
				),
			),
		);

	}

	/**
	 * Setup columns for feed list table.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function feed_list_columns() {

		return array(
			'feedName'   => esc_html__( 'Name', 'gravityformswebhooks' ),
			'requestURL' => esc_html__( 'Request URL', 'gravityformswebhooks' ),
		);

	}

	/**
	 * Prepares common HTTP header names as choices.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_header_choices() {

		return array(
			array(
				'label' => esc_html__( 'Select a Name', 'gravityformswebhooks' ),
				'value' => '',
			),
			array(
				'label' => 'Accept',
				'value' => 'Accept',
			),
			array(
				'label' => 'Accept-Charset',
				'value' => 'Accept-Charset',
			),
			array(
				'label' => 'Accept-Encoding',
				'value' => 'Accept-Encoding',
			),
			array(
				'label' => 'Accept-Language',
				'value' => 'Accept-Language',
			),
			array(
				'label' => 'Accept-Datetime',
				'value' => 'Accept-Datetime',
			),
			array(
				'label' => 'Authorization',
				'value' => 'Authorization',
			),
			array(
				'label' => 'Cache-Control',
				'value' => 'Cache-Control',
			),
			array(
				'label' => 'Connection',
				'value' => 'Connection',
			),
			array(
				'label' => 'Cookie',
				'value' => 'Cookie',
			),
			array(
				'label' => 'Content-Length',
				'value' => 'Content-Length',
			),
			array(
				'label' => 'Content-Type',
				'value' => 'Content-Type',
			),
			array(
				'label' => 'Date',
				'value' => 'Date',
			),
			array(
				'label' => 'Expect',
				'value' => 'Expect',
			),
			array(
				'label' => 'Forwarded',
				'value' => 'Forwarded',
			),
			array(
				'label' => 'From',
				'value' => 'From',
			),
			array(
				'label' => 'Host',
				'value' => 'Host',
			),
			array(
				'label' => 'If-Match',
				'value' => 'If-Match',
			),
			array(
				'label' => 'If-Modified-Since',
				'value' => 'If-Modified-Since',
			),
			array(
				'label' => 'If-None-Match',
				'value' => 'If-None-Match',
			),
			array(
				'label' => 'If-Range',
				'value' => 'If-Range',
			),
			array(
				'label' => 'If-Unmodified-Since',
				'value' => 'If-Unmodified-Since',
			),
			array(
				'label' => 'Max-Forwards',
				'value' => 'Max-Forwards',
			),
			array(
				'label' => 'Origin',
				'value' => 'Origin',
			),
			array(
				'label' => 'Pragma',
				'value' => 'Pragma',
			),
			array(
				'label' => 'Proxy-Authorization',
				'value' => 'Proxy-Authorization',
			),
			array(
				'label' => 'Range',
				'value' => 'Range',
			),
			array(
				'label' => 'Referer',
				'value' => 'Referer',
			),
			array(
				'label' => 'TE',
				'value' => 'TE',
			),
			array(
				'label' => 'User-Agent',
				'value' => 'User-Agent',
			),
			array(
				'label' => 'Upgrade',
				'value' => 'Upgrade',
			),
			array(
				'label' => 'Via',
				'value' => 'Via',
			),
			array(
				'label' => 'Warning',
				'value' => 'Warning',
			),
		);

	}





	// # FEED PROCESSING -----------------------------------------------------------------------------------------------

	/**
	 * Send webhook request.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $feed  The current Feed object.
	 * @param array $entry The current Entry object.
	 * @param array $form  The current Form object.
	 *
	 * @uses GFAddOn::log_debug()
	 * @uses GFAddOn::log_error()
	 * @uses GFCommon::replace_variables()
	 * @uses GFFeedAddOn::add_feed_error()
	 * @uses GF_Webhooks::get_request_data()
	 */
	public function process_feed( $feed, $entry, $form ) {

		// Get request method.
		$request_method = rgars( $feed, 'meta/requestMethod' );

		/**
		 * Modify the webhook HTTP request method.
		 *
		 * @since 1.0
		 * @param string $request_method HTTP request method.
		 * @param array  $feed           The current Feed object.
		 * @param array  $entry          The current Entry object.
		 * @param array  $form           The current Form object.
		 */
		$request_method = gf_apply_filters( array( 'gform_webhooks_request_method', $form['id'] ), $request_method, $feed, $entry, $form );

		// Convert request method to uppercase.
		$request_method = strtoupper( $request_method );

		// Get request headers.
		$request_headers = $this->get_generic_map_fields( $feed, 'requestHeaders', $form, $entry );

		// Remove request headers with undefined name.
		unset( $request_headers[ null ] );

		/**
		 * Modify the webhook HTTP request headers.
		 *
		 * @since 1.0
		 * @param array $request_headers HTTP request headers.
		 * @param array $feed           The current Feed object.
		 * @param array $entry          The current Entry object.
		 * @param array $form           The current Form object.
		 */
		$request_headers = gf_apply_filters( array( 'gform_webhooks_request_headers', $form['id'] ), $request_headers, $feed, $entry, $form );

		// Get request data.
		$request_data = $this->get_request_data( $feed, $entry, $form );

		// Get request URL and replace merge tags.
		$request_url = rgars( $feed, 'meta/requestURL' );
		$request_url = GFCommon::replace_variables( $request_url, $form, $entry, false, true, false, 'text' );

		// If this is a GET or DELETE request, add request data to request URL.
		if ( in_array( $request_method, array( 'GET', 'DELETE' ) ) && ! empty( $request_data ) ) {
			$request_url = add_query_arg( $request_data, $request_url );
		}

		// If this is a PUT or POST request, format request data.
		if ( in_array( $request_method, array( 'POST', 'PUT' ) ) && 'json' === $feed['meta']['requestFormat'] ) {

			// Add content type header.
			$request_headers['Content-Type'] = 'application/json';

			// Encode request data.
			$request_data = json_encode( $request_data );

		}

		/**
		 * Modify the webhook HTTP request URL.
		 *
		 * @since 1.0
		 * @param string $request_data HTTP request URL.
		 * @param array  $feed         The current Feed object.
		 * @param array  $entry        The current Entry object.
		 * @param array  $form         The current Form object.
		 */
		$request_url = apply_filters( 'gform_webhooks_request_url', $request_url, $feed, $entry, $form );

		// If feed URL is empty, log error and exit.
		if ( rgblank( $request_url ) ) {
			$this->add_feed_error( esc_html__( 'Webhook was not processed because request URL was empty.', 'gravityformswebhooks' ), $feed, $entry, $form );
			return;
		}

		// Prepare request arguments.
		$request_args = array(
			'body'      => ! in_array( $request_method, array( 'GET', 'DELETE' ) ) ? $request_data : null,
			'method'    => $request_method,
			'headers'   => $request_headers,
			'sslverify' => apply_filters( 'https_local_ssl_verify', true ),
		);

		/**
		 * Modify the webhook HTTP request arguments.
		 *
		 * @since 1.0
		 * @param array $request_args HTTP request arguments.
		 * @param array $feed         The current Feed object.
		 * @param array $entry        The current Entry object.
		 * @param array $form         The current Form object.
		 */
		$request_args = apply_filters( 'gform_webhooks_request_args', $request_args, $feed, $entry, $form );

		// Log request we are about to run.
		$this->log_debug( __METHOD__ . '(): Sending webhook request to ' . $request_url . '; ' . print_r( $request_args, true ) );

		// Execute request.
		$response = wp_remote_request( $request_url, $request_args );

		// Log error or success based on response.
		if ( is_wp_error( $response ) ) {
			$this->add_feed_error( sprintf( esc_html__( 'Webhook was not successfully executed. %s (%d)', 'gravityformswebhooks' ), $response->get_error_message(), $response->get_error_code() ), $feed, $entry, $form );
		} else {
			$this->log_debug( __METHOD__ . '(): Webhook successfully executed.' );
		}

	}

	/**
	 * Get data for webhook request.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $feed  The current Feed object.
	 * @param array $entry The current Entry object.
	 * @param array $form  The current Form object.
	 *
	 * @uses GFAddOn::get_generic_map_fields()
	 *
	 * @return array
	 */
	public function get_request_data( $feed, $entry, $form ) {

		// Get request data by body type.
		if ( 'all_fields' === $feed['meta']['requestBodyType'] ) {
			$request_data = $entry;
		} else {
			$request_data = $this->get_generic_map_fields( $feed, 'fieldValues', $form, $entry );
		}

		/**
		 * Modify the webhook HTTP request data.
		 *
		 * @since 1.0
		 * @param array $request_data HTTP request data.
		 * @param array $feed         The current Feed object.
		 * @param array $entry        The current Entry object.
		 * @param array $form         The current Form object.
		 */
		return gf_apply_filters( array( 'gform_webhooks_request_data', $form['id'] ), $request_data, $feed, $entry, $form );

	}

}
