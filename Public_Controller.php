<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Public Class - used for all public pages
 */
class Public_Controller extends MY_Controller {

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		if (is_null($this->session->userdata('unique_id')))
		{
			$this->session->set_userdata('unique_id', gen_rand_string_crypto(32));
		}

		// prepare theme name
		$this->settings->theme = strtolower($this->config->item('public_theme'));

		// set left + right error delimiters
		$this->form_validation->set_error_delimiters(
			$this->config->item('error_delimeter_left'), $this->config->item('error_delimeter_right')
		);

		// set global header data - can be merged with or overwritten in controllers
		$this
			->add_external_css([
				// load external css
				// for ex.: /themes/core/css/core.min.css
			])
			->add_external_js([
				// load external js
				// for ex.: /themes/core/js/jquery/jquery.min.js
			])
			->add_css_theme([
				// load public theme's css
				// for ex.: theme.min.css
			])
			->add_js_theme([
				// load public theme's js
				// for ex.: theme.min.js
			])
		;

		// declare main template
		$this->set_template("template.php");

		// enable the profiler?
		$this->output->enable_profiler($this->config->item('profiler'));
	}

	// --------------------------------------------------------------------

	/**
	 * 403 error
	 */
	public function show_403($page = '', $log_error = TRUE)
	{
		$heading = lang('core_error_forbidden');
		$message = lang('core_error_forbidden_desc');

		// By default we log this, but allow a dev to skip it
		if ($log_error)
		{
			log_message('error', $heading.': '.$page);
		}

		$this->show_error($heading, $message, 'error_403', 403);
	}

	// --------------------------------------------------------------------

	/**
	 * 404 error
	 */
	public function show_404($page = '', $log_error = TRUE)
	{
		$heading = lang('core_error_page_not_found');
		$message = lang('core_error_page_not_found_desc');

		// By default we log this, but allow a dev to skip it
		if ($log_error)
		{
			log_message('error', $heading.': '.$page);
		}

		$this->show_error($heading, $message, 'error_404', 404);
	}

	// --------------------------------------------------------------------

	/**
	 * 501 error
	 */
	public function show_501($page = '', $log_error = TRUE)
	{
		$heading = lang('core_error_not_implemented');
		$message = lang('core_error_not_implemented_desc');

		// By default we log this, but allow a dev to skip it
		if ($log_error)
		{
			log_message('error', $heading.': '.$page);
		}

		$this->show_error($heading, $message, 'error_501', 501);
	}

	// --------------------------------------------------------------------

	/**
	 * General Error Page
	 */
	private function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		$templates_path = $this->config->item('error_views_path');
		if (empty($templates_path))
		{
			$templates_path = 'errors'.DIRECTORY_SEPARATOR;
		}

		// Setting response header as 404
		set_status_header($status_code);

		$this->set_title($heading);

		$data = [
			'heading'		=> $heading,
			'message'		=> $message,
			'status_code'	=> $status_code,
			'show_error'	=> TRUE,
		];

		$this->render($templates_path.$template, $data);
	}

}
