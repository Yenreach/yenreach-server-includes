<?php
    require_once(LIB_PATH.DS.'database.php');
    
    class MailPasswords {
        private static $table_name = 'mailpasswords';
        protected static $db_fields = array('id', 'user_type', 'user_string', 'verify_string', 'email', 'password', 'incoming_server', 'outgoing_server', 'smtp_port', 'pop3_port', 'imap_port', 'created', 'last_updated');
        public $id;
        public $user_type;
        public $user_string;
        public $verify_string;
        public $email;
        public $password;
        public $incoming_server;
        public $outgoing_server;
        public $smtp_port;
        public $pop3_port;
        public $imap_port;
        public $created;
        public $last_updated;
        
        public $errors = array();
        
        private function check_errors($action){
            if(empty($this->email)){
                $this->errors[] = "Email must be provided";
            }
            if(empty($this->password)){
                $this->errors[] = "Password must be provided!";
            }
            if(empty($this->incoming_server)){
                $this->errors[] = "Incoming Server must be provided!";
            }
            if(empty($this->outgoing_server)){
                $this->errors[] = "Outgoing Server must be provided!";
            }
            if(empty($this->smtp_port)){
                $this->errors[] = "SMTP Port must be provided";
            }
            if(empty($this->pop3_port)){
                $this->errors[] = "POP3 Port must be provided!";
            }
            if(empty($this->imap_port)){
                $this->errors[] = "IMAP Port must be provided!";
            }
            if(!empty($this->username)){
                if($action == "create"){
                    $sql = "SELECT * FROM ".self::$table_name." WHERE email='{$this->email}'";
                    $emails = self::find_by_sql($sql);
                    if(!empty($emails)){
                        $this->errors[] = "Email not available!";
                    }
                } elseif($action == "update"){
                    $sql = "SELECT * FROM ".self::$table_name." WHERE email='{$this->email}' AND verify_string='{$this->verify_string}'";
                    $emails = self::find_by_sql($sql);
                    if(!empty($emails)){
                        $this->errors[] = "Email not available!";
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
                        $this->save();
                    }
                    return true;
                } else {
                    $this->errors[] = "The Mail details did not get saved!";
                    return false;
                }
            } else {
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
			return self::find_by_sql("SELECT * FROM ".self::$table_name);
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
		
		public static function find_by_email($string="") {
			global $database;
			$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE email='{$string}' LIMIT 1");
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