<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Model Class
 */
class MY_Model extends CI_Model {

	/**
	 * Constructor
	 */
	public function __construct()
	{
		// Construct the parent class
		parent::__construct();
	}

	// --------------------------------------------------------------------

	/**
	 * Check if table exists
	 *
	 * @param  string $table
	 * @return boolean
	 */
	public function table_exists($table)
	{
		return $this->db->table_exists($table);
	}

	// --------------------------------------------------------------------

	/**
	 * Check if field exists in the table
	 *
	 * @param  string $table
	 * @param  string $field
	 * @return boolean
	 */
	public function field_exists($table, $field)
	{
		return in_array($field, $this->db->list_fields($table));
	}

	//--------------------------------------------------------------------------

	/**
	 * Check if entry (field/value) exists in the table
	 *
	 * @param  string $table
	 * @param  string $field
	 * @param  mixed  $value
	 * @return boolean
	 */
	public function entry_exists($table, $field, $value)
	{
		if ($this->field_exists($table, 'deleted'))
		{
			$this->db->where('deleted', 0);
		}

		return (bool) $this->db
			->from($table)
			->where($field, $value)
			->count_all_results()
		;
	}

	//--------------------------------------------------------------------------

	/**
	 * Select specific field value by filters
	 *
	 * @param  string $table
	 * @param  string $field
	 * @param  array  $filters
	 * @return mixed
	 */
	public function get_field_value($table, $field, $filters = [])
	{
		if ($filters)
		{
			$this->db
				->select($field)
				->from($table)
				->limit(1)
			;

			foreach ($filters as $key => $value)
			{
				$this->db->where_by($table, $key, $value);
			}

			$query = $this->db->get();

			if ($query->num_rows() > 0)
			{
				$field = $this->get_field_name($field);
				return xss_clean($query->row()->{$field});
			}
		}

		return FALSE;
	}

	//--------------------------------------------------------------------------

	/**
	 * Get field name to use as property of sql builder
	 *
	 * @param  string $field
	 * @return string
	 */
	private function get_field_name($field)
	{
		$field = str_replace('`', '', $field);
		$field = str_replace(' as ', ' AS ', $field);

		// explode by 'AS' special word
		$field = explode(' AS ', $field);
		if ( ! isset($field[1]))
		{
			// explode by dot, if table was specified
			$field = explode('.', $field);
			$field = (isset($field[1])) ? $field[1] : $field[0];
		}
		else
		{
			$field = $field[1];
		}

		return $field;
	}

}
