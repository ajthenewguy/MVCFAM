<?php namespace MVCFAM\App\Db;
/**
 * Database table field class
 */

//use MVCFAM\App\Db\Migration;

class Dbfield {

	public $config;


	public function __construct($config = []) {
		$this->config = $config;
	}

	public function name() {
		if ($value = $this->config('field_name')) {
			return $value;
		}
	}

	public function type() {
		if ($value = $this->config('field_type')) {
			return $value;
		}
	}

	public function length() {
		if ($value = $this->config('field_length')) {
			return $value;
		}
	}

	public function is_primary_key() {
		$value = $this->config('primary_key');
		if ($value == 1) {
			return true;
		}
		return false;
	}
	
	public function create_string() {
		//$this->config;
		/*
	[field_name] => Array
        (
            [%r] => 
            [0] => ID
            [1] => Title
            [2] => CreatedTimestamp
        )

    [field_type] => Array
        (
            [%r] => INT
            [0] => INT
            [1] => VARCHAR
            [2] => TIMESTAMP
        )

    [field_length] => Array
        (
            [%r] => 
            [0] => 10
            [1] => 255
            [2] => 
        )

    [field_default_type] => Array
        (
            [%r] => NONE
            [0] => NONE
            [1] => NULL
            [2] => CURRENT_TIMESTAMP
        )

    [field_default_value] => Array
        (
            [%r] => 
            [0] => 
            [1] => 
            [2] => 
        )

    [primary_key] => 1
    [auto_increment] => Array
        (
            [0] => 1
        )

    [allow_null] => Array
        (
            [1] => 1
        )

"field_default_type":"USER_DEFINED","field_default_value":"0"


		*/
		$name = $this->name();
		$data = $this->type();
		if (isset($this->config['field_length']) && intval($this->config['field_length']) > 0) {
			$data .= '('.intval($this->config['field_length']).')';
		}
		if (isset($this->config['allow_null']) && $this->config['allow_null'] != 1) {
			$data .= ' NOT NULL';
		}
		if (isset($this->config['field_default_value']) && strlen($this->config['field_default_value']) > 0) {
			$data .= ' DEFAULT '.(is_numeric($this->config['field_default_value']) ? $this->config['field_default_value'] : "'".$this->config['field_default_value']."'");
		} elseif (isset($this->config['field_default_type']) && $this->config['field_default_type'] !== 'NONE') {
			if ($this->config['field_default_type'] == 'USER_DEFINED') {
				if (isset($this->config['field_default_value'])) {
					if ($this->config['field_default_value'] == 'NULL' || $this->config['field_default_value'] == '') {
						$data .= ' DEFAULT NULL';
					} else {
						$data .= ' DEFAULT '.(is_numeric($this->config['field_default_value']) ? $this->config['field_default_value'] : "'".$this->config['field_default_value']."'");
					}
				}
			} else {
				$data .= ' DEFAULT '.$this->config['field_default_type'];
			}
			
		}
		if(isset($this->config['auto_increment'])) {
			$data .= ' AUTO_INCREMENT';
		}
		if(isset($this->config['primary_key'])) {
			$data .= ' PRIMARY KEY';
		}
		$db_field_create_string = "\n\t\t`".$name.'` '.$data.',';

		return $db_field_create_string;
	}

	/**
	 * Get the config array or a specific element from it
	 */
	public function config($key = null) {
		if (is_null($key)) {
			return $thid->config;
		} elseif(isset($this->config[$key])) {
			return $this->config[$key];
		}
	}
}