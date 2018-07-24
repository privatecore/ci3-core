<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * File Class - used for all file requests
 */
class File_Controller extends MY_Controller {

	/**
	 * Constructor
	 */
	function __construct()
	{
		// Construct the parent class
		parent::__construct();
	}

	// --------------------------------------------------------------------

	protected function _output($file = NULL)
	{
		if (empty($file) OR ($file_content = read_file($file)) === FALSE)
		{
			set_status_header(404); // NOT_FOUND (404)
			exit(EXIT_ERROR);
		}

		$file_type = get_mime_by_extension($file);

		header('Cache-Control: no-cache');
		header('Content-Length: '.strlen($file_content));
		header('Content-Type: '.$file_type);
		header('Content-Disposition: inline; filename="'.basename($file).'";');

		exit($file_content);
	}

}
