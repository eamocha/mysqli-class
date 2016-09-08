<?php
class Dbase {

	private $_host = DB_HOST;
	private $_user = DB_USER;
	private $_password = DB_PASS;
	private $_name = DB_NAME;
	
	private $_conndb = false;
	public $_last_query = null;
	public $_affected_rows = 0;
	
	public $_insert_keys = array();
	public $_insert_values = array();
	public $_update_sets = array();
	
	public $_id;
	
	
	public function __construct() {
		$this->connect();
	}
	
	private function connect() {
		$this->_conndb = mysqli_connect($this->_host, $this->_user, $this->_password, $this->_name);
	}
	
	
	public function close() {
		if (!mysqli_close($this->_conndb)) {
			die("Closing connection failed.");
		}
	}
	
	
	public function escape($value) {
		if(function_exists("mysqli_real_escape_string")) {
			if (get_magic_quotes_gpc()) {
				$value = stripslashes($value);
			}
			$value = mysqli_real_escape_string($this->_conndb , $value);
		} else {
			if(!get_magic_quotes_gpc()) {
				$value = addcslashes($value);
			}
		}
		return $value;
	}
	
	
	public function query($sql) {
		$this->_last_query = $sql;
		$result = mysqli_query($this->_conndb ,$sql);
		$this->displayQuery($result);
		return $result;
	}


	
	
	public function displayQuery($result) {
		if(!$result) {
			$output  = "Database query failed: ". mysqli_error() . "<br />";
			$output .= "Last SQL query was: ".$this->_last_query;
			die($output);
		} else {
			$this->_affected_rows = mysqli_affected_rows($this->_conndb);
		}
	}
	
	
	public function fetchAll($sql) {
		$result = $this->query($sql);
		$out = array();
		while($row = mysqli_fetch_assoc($result)) {
			$out[] = $row;
		}
		mysqli_free_result($result);
		return $out;
	}
	
	public function fetchOne($sql) {
		$out = $this->fetchAll($sql);
		return array_shift($out);
	}
	
	public function lastId() {
		return mysqli_insert_id($this->_conndb);
	}
	
	public function count($result){
		$out = count($result);
		return $out;
	}
	
	
	
	
	
	
	
	
	public function prepareInsert($array = null) {
		if (!empty($array)) {
			foreach($array as $key => $value) {
				$this->_insert_keys[] = $key;
				$this->_insert_values[] = $this->escape($value);
			}
		}
	}
	
	
	
	
	
	
	
	
	public function insert($table = null) {
		
		if (
			!empty($table) && 
			!empty($this->_insert_keys) && 
			!empty($this->_insert_values)
		) {
		
			$sql  = "INSERT INTO `{$table}` (`";
			$sql .= implode("`, `", $this->_insert_keys);
			$sql .= "`) VALUES ('";
			$sql .= implode("', '", $this->_insert_values);
			$sql .= "')";
			
			if ($this->query($sql)) {
				$this->_id = $this->lastId();
				return true;
			}
			return false;
		
		}
		
	}
	
	
	
	
	
	
	public function prepareUpdate($array = null) {
		if (!empty($array)) {
			foreach($array as $key => $value) {
				$this->_update_sets[] = "`{$key}` = '".$this->escape($value)."'";
			}
		}
	}
	
	
	
	
	
	
	
	
	public function update($table = null, $id = null) {
		if (!empty($table) && !empty($id) && !empty($this->_update_sets)) {
			$sql  = "UPDATE `{$table}` SET ";
			$sql .= implode(", ", $this->_update_sets);
			$sql .= " WHERE `id` = '".$this->escape($id)."'";
			return $this->query($sql);
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	

}