<?php
	require_once("config.php");
	
	class MySQLDatabase {
		private $connection;
		public $lastquery;
		private $magic_quotes_active;
		private $real_escape_string_exists;
		
		function __construct() {
			$this->open_connection();
			//$this->magic_quotes_active = get_magic_quotes_gpc();
			$this->real_escape_string_exists = function_exists("mysqli_real_escape_string");
		}
		
		public function open_connection() {
			$this->connection = mysqli_connect(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
			if(!$this->connection) {
				die("Database connection failed: ". mysqli_error($this->connection));
			}
			mysqli_query($this->connection, "SET SESSION sql_mode = ''");
		}
		
		public function close_connection() {
			if(isset($this->connection)) {
				mysqli_close($this->connection);
				unset($this->connection);
			}
		}
		
		public function query($sql) {
			$this->last_query = $sql;
			$result = mysqli_query($this->connection, $sql);
			$this->confirm_query($result);
			return $result;
		}
		
		public function escape_value($value) {
			if($this->real_escape_string_exists) {// PHP4.3 or higher
				// undo any magic_quote effects so mysql_real_escape_string can do the work
				/*if($this->magic_quotes_active) {
					$value = stripslashes($value);
				}*/
				$value = mysqli_real_escape_string($this->connection, $value);
				$valued = htmlentities($value);
			} else {
				// if magic quotes aren't on, then add slashes manually
				if(!$this->magic_quotes_active) {
					$value = addslashes($value);
					$valued = htmlentities($value);
				}
			}
			return $valued;
		}
		
		// "database neutral" methods
		public function fetch_array($result_set) {
			return mysqli_fetch_array($result_set);
		}
		
		public function num_rows($result_set) {
			return mysqli_num_rows($result_set);
		}
		
		public function insert_id() {
			// get the last id inserted over the db connection
			return mysqli_insert_id($this->connection);
		}
		
		public function affected_rows() {
			return mysqli_affected_rows($this->connection);
		}
		
		public function confirm_query($result){
			if(!$result) {
				$output = "Database query failed: " . mysqli_error($this->connection). "<br />";
				$output .= "Last SQLquery: " . $this->last_query;
				die($output);
			}
		}
	}
	
	$database = new MySQLDatabase();
	
?>