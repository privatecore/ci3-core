<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Public AJAX Class - used for all public ajax requests
 */
class Public_Ajax_Controller extends MY_Controller {

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

		// must use AJAX request
		if ($this->input->is_ajax_request() === FALSE)
		{
			return $this->json_response([
				'status'	=> FALSE,
				'message'	=> lang('core_error_ajax_only'),
			]);
		}
	}

}
