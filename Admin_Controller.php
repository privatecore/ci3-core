<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Base Admin Class - used for all administration pages
 */
class Admin_Controller extends MY_Controller {

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// user must be logged in
		if ( ! isset($this->user->id))
		{
			if ($this->current_uri != 'admin/login')
			{
				if (current_url() != base_url())
				{
					// store requested URL to session - will load once logged in
					$data = ['redirect' => current_url()];
					$this->session->set_userdata($data);
				}

				redirect('admin/login');
			}
		}

		// load the admin language file
		$this->lang->load('admin');

		// prepare theme name
		$this->settings->theme = strtolower($this->config->item('admin_theme'));

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
				// load admin theme's css
				// for ex.: theme.min.css
			])
			->add_js_theme([
				// load admin theme's js
				// for ex.: theme.min.js
			])
		;

		// declare main template
		$this->set_template("template.php");

		// enable the profiler?
		$this->output->enable_profiler($this->config->item('profiler'));
	}

}
