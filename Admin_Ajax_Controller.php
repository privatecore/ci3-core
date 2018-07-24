<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Private AJAX Class - used for all admin ajax requests
 */
class Admin_Ajax_Controller extends MY_Controller {

	/**
	 * Constructor
	 */
	function __construct()
	{
		// Construct the parent class
		parent::__construct();

		// must be logged in
		if ( ! isset($this->user->id))
		{
			return $this->json_response([
				'status'	=> FALSE,
				'message'	=> lang('core_error_auth_required'),
			]);
		}

		// must use AJAX request
		if ($this->input->is_ajax_request() === FALSE)
		{
			return $this->json_response([
				'status'	=> FALSE,
				'message'	=> lang('core_error_ajax_only'),
			]);
		}

		// load the admin language file
		$this->lang->load('admin');
	}

}
