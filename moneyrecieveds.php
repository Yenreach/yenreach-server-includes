<?php
    require_once(LIB_PATH.DS.'database.php');
    require_once(LIB_PATH.DS.'users.php');
    
    class MoneyRecieveds {
        private static $table_name = 'recievedpayments';
        protected static $db_fields = array('id', 'verify_string', 'platform', 'tx_ref', 'user_type', 'user_string', 'reason', 'subject', 'currency', 'amount', 'response1', 'response2', 'response3', 'response4', 'response5', 'status', 'created', 'last_updated');
        public $id;
        public $verify_string;
        public $platform;
        public $tx_ref;
        public $user_type;
        public $user_string;
        public $reason;
        public $subject;
        public $currency;
        public $amount;
        public $response1;
        public $response2;
        public $response3;
        public $response4;
        public $response5;
        public $status;
        public $created;
        public $last_updated;
        
        public $errors = array();
        
        private function check_errors(){
            if(empty($this->platform)){
                $this->errors[] = 'The Payment Platform must be stipulated!';
            }
            if(empty($this->user_type)){
                $this->errors[] = "The User type must be provided!";
            }
            if(empty($this->user_string)){
                $this->errors[] = 'The User must be provided!';
            }
            if(empty($this->reason)){
                $this->errors[] = 'The Reason for the Payment must be stipulated!';
            }
            if(empty($this->subject)){
                $this->errors[] = 'The Payment subject must be stated';
            }
            if(empty($this->currency)){
                $this->errors[] = 'The Currency of Payment must be provided!';
            }
            if(empty($this->amount)){
                $this->errors[] = 'The Amount must be provided';
            }
        }
        
        public function insert(){
            $this->check_errors();
            if(empty($this->errors)){
                $time = time();
                $this->last_updated = $time;
                if(empty($this->created)){
                    $this->created = $time;
                }
                if($this->save()){
                    if(empty($this->verify_string)){
                        $string = $this->id.$time;
                        $this->verify_string = sha1($string);
                        $this->save();
                    }
                    if(empty($this->tx_ref)){
                        if($this->user_type == 'user'){
                            $user = Users::find_by_verify_string($this->user_string);
                            $id = $user->id;
                        }
                        $this->tx_ref = 'YR-'.$this->id.$time.".".$id;
                        $this->save();
                    }
                    return true;
                } else {
                    $this->errors[] = 'The Transaction was not saved';
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
		
		public static function find_by_user_string($user_type, $string){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE user_type='{$user_type}' AND user_string='{$string}' ORDER BY created DESC");
		}
		
		public static function find_by_tx_ref($tx_ref=""){
		    global $database;
		    $result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE tx_ref='{$tx_ref}' LIMIT 1");
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