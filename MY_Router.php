<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Router Class
 */
class MY_Router extends CI_Router {

	/**
	 * Set default controller
	 *
	 * @return	void
	 */
	protected function _set_default_controller()
	{
		if (empty($this->default_controller))
		{
			show_error('Unable to determine what should be displayed. A default route has not been specified in the routing file.');
		}

		$default_controller = explode('/', trim($this->default_controller, '/'));

		// if sub-folder specified in the default controller
		if (count($default_controller) > 2)
		{
			// method should be specified
			$method = array_pop($default_controller);
			$class = array_pop($default_controller);

			// update default controller with values
			$this->default_controller = $class . '/' . $method;

			$directory = implode(DIRECTORY_SEPARATOR, $default_controller);

			$this->set_directory($directory);
		}

		// Is the method being specified?
		if (sscanf($this->default_controller, '%[^/]/%s', $class, $method) !== 2)
		{
			$method = 'index';
		}

		if ( ! file_exists(APPPATH.'controllers/'.$this->directory.ucfirst($class).'.php'))
		{
			// This will trigger 404 later
			return;
		}

		$this->set_class($class);
		$this->set_method($method);

		// Assign routed segments, index starting from 1
		$this->uri->rsegments = array(
			1 => $class,
			2 => $method
		);

		log_message('debug', 'No URI present. Default controller set.');
	}

}
