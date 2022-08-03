<?php
    require_once(LIB_PATH.DS.'database.php');
    
    class Admins {
        private static $table_name = "admins";
        protected static $db_fields = array('id', 'verify_string', 'name', 'username', 'password', 'timer', 'personal_email', 'official_email', 'phone', 'activation', 'autho_level', 'created', 'last_updated');
        public $id;
        public $verify_string;
        public $name;
        public $username;
        public $password;
        public $timer;
        public $personal_email;
        public $official_email;
        public $phone;
        public $activation;
        public $autho_level;
        public $created;
        public $last_updated;
        
        public $errors = array();
        
        private function check_errors($action){
            if(empty($this->name)){
                $this->errors[] = "The Name of the Admin must be provided";
            }
            if(empty($this->username)){
                $this->errors[] = "The Username must be provided!";
            }
            if(empty($this->personal_email)){
                $this->errors[] = "The Personal Mail must be given!";
            }
            if(empty($this->phone)){
                $this->errors[] = "Phone must be provided!";
            }
            
            if(!empty($this->username)){
                if($action == "create"){
                    $sql = "SELECT * FROM ".self::$table_name." WHERE username='{$this->username}' AND activation!=0";
                    $users = self::find_by_sql($sql);
                    if(!empty($users)){
                        $this->errors[] = "This Username is not available";
                    }
                } elseif($action == "update"){
                    $sql = "SELECT * FROM ".self::$table_name." WHERE username='{$this->username}' AND verify_string!='{$this->verify_string}' AND activation!=0";
                    $users = self::find_by_sql($sql);
                    if(!empty($users)){
                        $this->errors[] = "This Username is not available";
                    }
                }
            }
            if(!empty($this->personal_email)){
                if($action == "create"){
                    $sql = "SELECT * FROM ".self::$table_name." WHERE personal_email='{$this->personal_email}' AND activation!=0";
                    $users = self::find_by_sql($sql);
                    if(!empty($users)){
                        $this->errors[] = "This Email belongs to another Admin";
                    }
                } elseif($action == "update"){
                    $sql = "SELECT * FROM ".self::$table_name." WHERE personal_email='{$this->personal_email}' AND verify_string!='{$this->verify_string}' AND activation!=0";
                    $users = self::find_by_sql($sql);
                    if(!empty($users)){
                        $this->errors[] = "This Email belongs to another Admin";
                    }
                }
            }
            if(!empty($this->phone)){
                if($action == "create"){
                    $sql = "SELECT * FROM ".self::$table_name." WHERE phone='{$this->phone}' AND activation!=0";
                    $users = self::find_by_sql($sql);
                    if(!empty($users)){
                        $this->errors[] = "This Phone belongs to another Admin";
                    }
                } elseif($action == "update"){
                    $sql = "SELECT * FROM ".self::$table_name." WHERE phone='{$this->phone}' AND verify_string!='{$this->verify_string}' AND activation!=0";
                    $users = self::find_by_sql($sql);
                    if(!empty($users)){
                        $this->errors[] = "This Phone belongs to another Admin";
                    }
                }
            }
        }
        
        public function insert($action){
            $this->check_errors($action);
            if(empty($this->errors)){
                $time = time();
                $this->last_updated = $time;
                if($this->save()){
                    if($action == "create"){
                        $this->created = $time;
                        $string = $this->id.$time;
                        $this->verify_string = sha1($string);
                        $this->activation = 1;
                        $this->save();
                    }
                    return true;
                } else {
                    $this->errors[] = "The Admin was not saved!";
                    return false;
                }
            } else {
                return false;
            }
        }
        
        public function encrypt_value($timer, $value){
			if((!empty($timer)) && (!empty($value))){
				$timed = dechex($timer);
				$pass = "Yenreach".$timed.$value."Dordirian";
				$encrypted = sha1($pass);
				return $encrypted;
			} else {
				$encrypted = "";
				return $encrypted;
			}
		}
		
		public function authenticate($username, $password){
		    if(!empty($username) && !empty($password)){
		        $user = self::find_login_username($username);
		        if(!empty($user)){
		            if($user->activation == 2){
		                $pword = $user->encrypt_value($user->timer, $password);
		                if($user->password == $pword){
		                    return $user;
		                } else {
		                    $this->errors[] = "Wrong Password";
		                    return false;
		                }
		            } else {
		                if($user->activation == 0){
		                    $this->errors[] = "Account Deleted";
		                } elseif($user->activation == 1){
		                    $this->errors[] = "Account Deactivated";
		                }
		                return false;
		            }
		        } else {
		            $this->errors[] = "Wrong Username";
		            return false;
		        }
		    } else {
		        $this->errors[] = "Username and Password must be entered";
		        return false;
		    }
		}
        
        // Common Database Methods
		protected function attributes() {
			// return an array of attributes and their values
			$attributes = array();
			foreach(self::$db_fields as $field) {
				if(property_exists($this, $field)) {
					$attributes[$field] = $this->$field;
				}
			}
			return $attributes;
		}
		
		protected function sanitized_attributes() {
			global $database;
			$clean_attributes = array();
			// sanitize the value before submitting
			// Note: does not alter the actual value of each attribute
			foreach($this->attributes() as $key=>$value) {
				$clean_attributes[$key] = $database->escape_value($value);
			}
			return $clean_attributes;
		}
		
		private function has_attribute($attribute) {
			$object_vars = $this->attributes();
			return array_key_exists($attribute, $object_vars);
		}
		
		private static function instantiate($record) {
			$object = new self;
			
			foreach($record as $attribute=>$value) {
				if($object->has_attribute($attribute)) {
					$object->$attribute = $value;
				}
			}
			return $object;
		}
		
		public static function find_by_sql($sql="") {
			global $database;
			$result_set = $database->query($sql);
			$object_array = array();
			while($row = $database->fetch_array($result_set)) {
				$object_array[] = self::instantiate($row);
			}
			return $object_array;
		}
		
		public static function find_all() {
			return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE activation>0 AND autho_level>1");
		}
		
		public static function find_by_id($id=0) {
			global $database;
			$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE id={$id} LIMIT 1");
			return !empty($result_array) ? array_shift($result_array) : false;
		}
		
		public static function find_by_verify_string($string="") {
			global $database;
			$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE verify_string='{$string}' LIMIT 1");
			return !empty($result_array) ? array_shift($result_array) : false;
		}
		
		public static function find_by_username($string="") {
			global $database;
			$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE username='{$string}' LIMIT 1");
			return !empty($result_array) ? array_shift($result_array) : false;
		}
		
		public static function find_login_username($string="") {
			global $database;
			$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE username='{$string}' AND activation!=0 LIMIT 1");
			return !empty($result_array) ? array_shift($result_array) : false;
		}
		
		public static function find_by_identity($identity=""){
		    global $database;
		    $result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE (username='{$identity}' OR email='{$identity}' OR phone='{$identity}') AND activation!=0 LIMIT 1");
		    return !empty($result_array) ? array_shift($result_array) : false;
		}
		
		public static function count_all() {
			global $database;
			$sql = "SELECT COUNT(*) FROM ".self::$table_name;
			$result_set = $database->query($sql);
			$row = $database->fetch_array($result_set);
			return array_shift($row);
		}
		
		public function create() {
			global $database;
			$attributes = $this->sanitized_attributes();
			$sql = "INSERT INTO ".self::$table_name." (";
			$sql .= join(", ", array_keys($attributes));
			$sql .= ") VALUES ('";
			$sql .= join("', '", array_values($attributes));
			$sql .= "')";
			if($database->query($sql)) {
				$this->id = $database->insert_id();
				return true;
			} else {
				return false;
			}
		}
		
		public function update() {
			global $database;
			$attributes = $this->sanitized_attributes();
			$attribute_pairs = array();
			foreach($attributes as $key => $value) {
				$attribute_pairs[] = "{$key}='{$value}'";
			}
			$sql = "UPDATE ".self::$table_name." SET ";
			$sql .= join(", ", $attribute_pairs);
			$sql .= " WHERE id=". $database->escape_value($this->id);
			$sql .= " LIMIT 1";
			$result = $database->query($sql);
			//return ($database->affected_rows() == 1) ? true : false;
			return $result ? true : false;
		}
		
		public function save() {
			return isset($this->id) ? $this->update() : $this->create();
		}
		
		public function delete() {
			global $database;
			$sql = "DELETE FROM ".self::$table_name;
			$sql .= " WHERE id=". $database->escape_value($this->id);
			$sql .=" LIMIT 1";
			$database->query($sql);
			return ($database->affected_rows() == 1) ? true : false;
		}
    }
?>