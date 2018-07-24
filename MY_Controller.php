<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Root Class
 */
class MY_Controller extends CI_Controller {

	/**
	 * Common data
	 */
	public $user;
	public $city;
	public $settings;
	public $includes;
	public $theme;
	public $template;
	public $error;

	// --------------------------------------------------------------------

	/**
	 * Class constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();

		// get settings
		$settings = $this->settings_model->get_settings();
		$this->settings = new stdClass();
		foreach ($settings as $setting)
		{
			$this->settings->{$setting['name']} = (@unserialize($setting['value']) !== FALSE)
				? unserialize($setting['value'])
				: $setting['value']
			;
		}

		// get current uri
		$this->current_uri = uri_string();

		// set the time zone
		$timezones = $this->config->item('timezones');
		if (function_exists('date_default_timezone_set'))
		{
			date_default_timezone_set($timezones[$this->settings->timezones]);
		}

		// @todo user data, city data and all the rest session related data should be somehow
		//       organized within $_SESSION scope!
		// get current user
		$this->user = (object) $this->session->userdata('logged_in');
		// get current city
		$this->city = (object) $this->session->userdata('city');
		if ( ! isset($this->city->id))
		{
			$this->session->first_time = TRUE;
		}
		else
		{
			$this->session->unset_userdata('first_time');
		}

		// load the core language file
		$this->lang->load('core');
	}

	// --------------------------------------------------------------------

	/**
	 * Add CSS from external source or outside folder theme
	 *
	 * This function used to easily add css files to be included in a template.
	 * with this function, we can just add css name as parameter and their external path,
	 * or add css complete with path. See example.
	 *
	 * We can add one or more css files as parameter, either as string or array.
	 * If using parameter as string, it must use comma separator between css file name.
	 * -----------------------------------
	 * Example:
	 * -----------------------------------
	 * 1. Using string as first parameter
	 *    $this->add_external_css( "global.css, color.css", "http://example.com/assets/css/" );
	 *      or
	 *    $this->add_external_css(  "http://example.com/assets/css/global.css, http://example.com/assets/css/color.css" );
	 *
	 * 2. Using array as first parameter
	 *    $this->add_external_css( array( "global.css", "color.css" ),  "http://example.com/assets/css/" );
	 *      or
	 *    $this->add_external_css(  array( "http://example.com/assets/css/global.css", "http://example.com/assets/css/color.css") );
	 *
	 * @param   mixed
	 * @param   string, default = NULL
	 * @return  chained object
	 */
	public function add_external_css($css_files, $path = NULL)
	{
		// make sure that $this->includes has array value
		if ( ! is_array( $this->includes ) )
		{
			$this->includes = [];
		}

		// if $css_files is string, then convert into array
		$css_files = is_array( $css_files ) ? $css_files : explode( ",", $css_files );

		foreach ( $css_files as $css )
		{
			// remove white space if any
			$css = trim( $css );

			// go to next when passing empty space
			if ( empty( $css ) ) continue;

			// using sha1( $css ) as a key to prevent duplicate css to be included
			$this->includes[ 'css_files' ][ sha1( $css ) ] = is_null( $path ) ? $css : $path . $css;
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Add JS from external source or outside folder theme
	 *
	 * This function used to easily add js files to be included in a template.
	 * with this function, we can just add js name as parameter and their external path,
	 * or add js complete with path. See example.
	 *
	 * We can add one or more js files as parameter, either as string or array.
	 * If using parameter as string, it must use comma separator between js file name.
	 * -----------------------------------
	 * Example:
	 * -----------------------------------
	 * 1. Using string as first parameter
	 *    $this->add_external_js( "global.js, color.js", "http://example.com/assets/js/" );
	 *      or
	 *    $this->add_external_js(  "http://example.com/assets/js/global.js, http://example.com/assets/js/color.js" );
	 *
	 * 2. Using array as first parameter
	 *    $this->add_external_js( array( "global.js", "color.js" ),  "http://example.com/assets/js/" );
	 *      or
	 *    $this->add_external_js(  array( "http://example.com/assets/js/global.js", "http://example.com/assets/js/color.js") );
	 *
	 * @param   mixed
	 * @param   string, default = NULL
	 * @return  chained object
	 */
	public function add_external_js( $js_files, $path = NULL )
	{
		// make sure that $this->includes has array value
		if ( ! is_array( $this->includes ) )
		{
			$this->includes = [];
		}

		// if $js_files is string, then convert into array
		$js_files = is_array( $js_files ) ? $js_files : explode( ",", $js_files );

		foreach ( $js_files as $js )
		{
			// remove white space if any
			$js = trim( $js );

			// go to next when passing empty space
			if ( empty( $js ) ) continue;

			// using sha1( $css ) as a key to prevent duplicate css to be included
			$this->includes[ 'js_files' ][ sha1( $js ) ] = is_null( $path ) ? $js : $path . $js;
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Add CSS from Active Theme Folder
	 *
	 * This function used to easily add css files to be included in a template.
	 * with this function, we can just add css name as parameter
	 * and it will use default css path in active theme.
	 *
	 * We can add one or more css files as parameter, either as string or array.
	 * If using parameter as string, it must use comma separator between css file name.
	 * -----------------------------------
	 * Example:
	 * -----------------------------------
	 * 1. Using string as parameter
	 *	 $this->add_css_theme( "bootstrap.min.css, style.css, admin.css" );
	 *
	 * 2. Using array as parameter
	 *	 $this->add_css_theme( array( "bootstrap.min.css", "style.css", "admin.css" ) );
	 *
	 * @param   mixed
	 * @return  chained object
	 */
	public function add_css_theme( $css_files )
	{
		// make sure that $this->includes has array value
		if ( ! is_array( $this->includes ) )
		{
			$this->includes = [];
		}

		// if $css_files is string, then convert into array
		$css_files = is_array( $css_files ) ? $css_files : explode( ",", $css_files );

		foreach ( $css_files as $css )
		{
			// remove white space if any
			$css = trim( $css );

			// go to next when passing empty space
			if ( empty( $css ) ) continue;

			$css_path = "/themes/{$this->settings->theme}/css/{$css}";
			$css_time = @filemtime($this->input->server("DOCUMENT_ROOT") . $css_path);

			// using sha1( $css ) as a key to prevent duplicate css to be included
			$this->includes[ 'css_files' ][ sha1( $css ) ] = $css_path . "?t={$css_time}";
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Add JS from Active Theme Folder
	 *
	 * This function used to easily add js files to be included in a template.
	 * with this function, we can just add js name as parameter
	 * and it will use default js path in active theme.
	 *
	 * We can add one or more js files as parameter, either as string or array.
	 * If using parameter as string, it must use comma separator between js file name.
	 *
	 * The second parameter is used to determine wether js file is support internationalization or not.
	 * Default is FALSE
	 * -----------------------------------
	 * Example:
	 * -----------------------------------
	 * 1. Using string as parameter
	 *	 $this->add_js_theme( "jquery-1.11.1.min.js, bootstrap.min.js, another.js" );
	 *
	 * 2. Using array as parameter
	 *	 $this->add_js_theme( array( "jquery-1.11.1.min.js", "bootstrap.min.js,", "another.js" ) );
	 *
	 * @param   mixed
	 * @param   boolean
	 * @return  chained object
	 */
	public function add_js_theme( $js_files, $is_i18n = FALSE )
	{
		if ( $is_i18n )
		{
			return $this->add_jsi18n_theme( $js_files );
		}
		// make sure that $this->includes has array value
		if ( ! is_array( $this->includes ) )
		{
			$this->includes = [];
		}

		// if $css_files is string, then convert into array
		$js_files = is_array( $js_files ) ? $js_files : explode( ",", $js_files );

		foreach ( $js_files as $js )
		{
			// remove white space if any
			$js = trim( $js );

			// go to next when passing empty space
			if ( empty( $js ) ) continue;

			$js_path = "/themes/{$this->settings->theme}/js/{$js}";
			$js_time = @filemtime($this->input->server("DOCUMENT_ROOT") . $js_path);

			// using sha1( $js ) as a key to prevent duplicate js to be included
			$this->includes[ 'js_files' ][ sha1( $js ) ] = $js_path . "?t={$js_time}";
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Add JSi18n files from Active Theme Folder
	 *
	 * This function used to easily add jsi18n files to be included in a template.
	 * with this function, we can just add jsi18n name as parameter
	 * and it will use default js path in active theme.
	 *
	 * We can add one or more jsi18n files as parameter, either as string or array.
	 * If using parameter as string, it must use comma separator between jsi18n file name.
	 * -----------------------------------
	 * Example:
	 * -----------------------------------
	 * 1. Using string as parameter
	 *     $this->add_jsi18n_theme( "dahboard_i18n.js, contact_i18n.js" );
	 *
	 * 2. Using array as parameter
	 *     $this->add_jsi18n_theme( array( "dahboard_i18n.js", "contact_i18n.js" ) );
	 *
	 * 3. Or we can use add_js_theme function, and add TRUE for second parameter
	 *     $this->add_js_theme( "dahboard_i18n.js, contact_i18n.js", TRUE );
	 *      or
	 *     $this->add_js_theme( array( "dahboard_i18n.js", "contact_i18n.js" ), TRUE );
	 * --------------------------------------
	 *
	 * @param   mixed
	 * @return  chained object
	 */
	public function add_jsi18n_theme( $js_files )
	{
		// make sure that $this->includes has array value
		if ( ! is_array( $this->includes ) )
		{
			$this->includes = [];
		}

		// if $css_files is string, then convert into array
		$js_files = is_array( $js_files ) ? $js_files : explode( ",", $js_files );

		foreach ( $js_files as $js )
		{
			// remove white space if any
			$js = trim( $js );

			// go to next when passing empty space
			if ( empty( $js ) ) continue;

			// using sha1( $js ) as a key to prevent duplicate js to be included
			$this->includes[ 'js_files_i18n' ][ sha1( $js ) ] = $this->jsi18n->translate( "/themes/{$this->settings->theme}/js/{$js}" );
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Page Title
	 *
	 * @param string
	 * @return chained object
	 */
	public function set_title( $page_title )
	{
		$this->includes[ 'page_title' ] = $page_title;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Page Description
	 *
	 * @param string
	 * @return chained object
	 */
	public function set_description( $page_description )
	{
		$this->includes[ 'page_description' ] = $page_description;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Breadcrumbs
	 *
	 * sometime, we want to have page header different from page title
	 * so, use this function
	 *
	 * @param   string
	 * @return  chained object
	 */
	public function set_breadcrumbs( $breadcrumbs )
	{
		$this->includes[ 'breadcrumbs' ] = $breadcrumbs;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Page Header
	 *
	 * sometime, we want to have page header different from page title
	 * so, use this function
	 *
	 * @param   string
	 * @return  chained object
	 */
	public function set_heading( $page_heading )
	{
		$this->includes[ 'page_heading' ] = $page_heading;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Meta Keywords
	 *
	 * @param string
	 * @return chained object
	 */
	public function set_meta_keywords( $meta_keywords )
	{
		$this->includes[ 'meta_keywords' ] = $meta_keywords;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Meta Description
	 *
	 * @param string
	 * @return chained object
	 */
	public function set_meta_description( $meta_description )
	{
		$this->includes[ 'meta_description' ] = $meta_description;
		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Attributes
	 * @todo replace every set_ method here?
	 *
	 * @param string
	 * @return chained object
	 */
	public function set_attributes( $attributes )
	{
		if (is_array($attributes))
		{
			foreach ($attributes as $key => $value)
			{
				$this->includes[ $key ] = $value;
			}
		}

		return $this;
	}

	// --------------------------------------------------------------------

	/**
	 * Set Template
	 *
	 * sometime, we want to use different template for different page
	 * for example, 404 template, login template, full-width template, sidebar template, etc.
	 * so, use this function
	 *
	 * @param   string, template file name
	 * @return  chained object
	 */
	public function set_template( $template_file = 'template.php' )
	{
		// make sure that $template_file has .php extension
		$template_file = substr( $template_file, -4 ) == '.php' ? $template_file : ( $template_file . ".php" );

		$this->template = "../../themes/{$this->settings->theme}/{$template_file}";
	}

	// --------------------------------------------------------------------

	/**
	 * Render Page
	 *
	 * @param  string $view
	 * @param  array  $data
	 * @param  bool   $xss_clean
	 * @return void
	 */
	public function render( $view, $data = [], $xss_clean = FALSE )
	{
		// clean data before proceed
		// @tip a largely unknown rule about XSS cleaning is that it should only be applied
		//      to output, as opposed to input data
		//
		// @todo review later and find more proper way to sanitize output data
		if ($xss_clean)
		{
			$data = xss_clean($data);
		}

		$this->includes['content'] = $this->load->view($view, $data, TRUE);
		$this->load->view($this->template, $this->includes);
	}

	// --------------------------------------------------------------------

	/**
	 * JSON Encoded Response
	 *
	 * @param  array $data
	 * @return void
	 */
	public function json_response( $data = [] )
	{
		header('Content-Type: application/json; charset=utf-8');
		echo json_encode($data);
		exit;
	}

}
