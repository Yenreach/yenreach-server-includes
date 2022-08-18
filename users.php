<?php
    require_once(LIB_PATH.DS."database.php");
    
    class Users {
        private static $table_name = "users";
        protected static $db_fields = array('id', 'verify_string', 'name', 'email', 'timer', 'password', 'image', 'listed', 'refer_method', 'activation', 'autho_level', 'created', 'last_updated', 'confirmed_email', 'email_track');
        public $id; 
        public $verify_string;
        public $name;
        public $email;
        public $timer; 
        public $password;
        public $image;
        public $listed;
        public $refer_method;
        public $activation;
        public $autho_level;
        public $created;
        public $last_updated;
        public $confirmed_email;
		public $email_track;
        
        public $errors = array();
        
        private function check_errors(){
            if(empty($this->name)){
                $this->errors[] = "Name must be provided";
            }
            if(empty($this->email)){
                $this->errors[] = "Email must be provided";
            }
            if(!empty($this->email)){
                $id = !empty($this->id) ? (int)$this->id : 0;
                $founds = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE email='{$this->email}' AND id!={$id} AND activation!=0");
                if(!empty($founds)){
                    $this->errors[] = "Email is already in use by another Customer";
                }
            }
        }
        
        public function insert(){
            $this->check_errors();
            if(empty($this->errors)){
                $time = time();
                if(empty($this->created)){
                    $this->created = $time;
                }
                $this->last_updated = $time;
                if($this->save()){
                    if(empty($this->verify_string)){
                        $string = $this->id.$time;
						$this->email_track = 1;
                        $this->verify_string = sha1($string);
                        $this->save();
                    }
                    return true;
                } else {
                    $this->errors[] = "The User's data was not saved";
                    return false;
                }
            } else {
                return false;
            }
        }
        
        public function encrypt_value($timer, $value){
			if((!empty($timer)) && (!empty($value))){
				$timed = dechex($timer);
				$pass = "Yenreach".$value.$timed."Roundyen";
				$encrypted = sha1($pass);
				return $encrypted;
			} else {
				$encrypted = "";
				return $encrypted;
			}
		}
		
		public function authenticate($username, $password){
		    if(!empty($username) && !empty($password)){
		        $user = self::find_by_email($username);
		        if(!empty($user)){
		            if($user->activation == 2){
		                $pass = $this->encrypt_value($user->timer, $password);
		                if($pass == $user->password){
		                    return $user;
		                } else {
		                    $this->errors[] = "Wrong Password";
		                    return false;
		                }
		            } else {
		                if($user->activation == 0){
		                    $this->errors[] = "Account has been deleted";
		                } elseif($user->activation == 1){
		                    $this->errors[] = "Account is deactivated";
		                }
		                return false;
		            }
		        } else {
		            $this->errors[] = "Username not provided";
		            return false;
		        }
		    } else {
		        if(empty($username)){
		            $this->errors[] = "Username must be Provided";
		        }
		        if(empty($password)){
		            $this->errors[] = "Password must be Provided";
		        }
		        //$this->errors[] = "You must provide Username and Password";
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
			return self::find_by_sql("SELECT * FROM ".self::$table_name." ORDER BY name");
		}
		
		public static function find_by_email($email=""){
		    global $database;
		    $result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE email='{$email}' LIMIT 1");
		    return !empty($result_array) ? array_shift($result_array) : false;
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