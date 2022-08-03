<?php
    require_once(LIB_PATH.DS."database.php");
    
    class BillboardApplications {
        private static $table_name = "billboardapplications";
        protected static $db_fields = array('id', 'verify_string', 'code', 'user_string', 'filename', 'title', 'text', 'call_to_action_type', 'call_to_action_link', 'advert_type', 'proposed_start_date', 'start_date', 'end_date', 'stage', 'remarks', 'agent_type', 'agent_string', 'created', 'last_updated');
        public $id; 
        public $verify_string;
        public $code;
        public $user_string;
        public $filename;
        public $title;
        public $text;
        public $call_to_action_type;
        public $call_to_action_link; 
        public $advert_type; 
        public $proposed_start_date;
        public $start_date;
        public $end_date; 
        public $stage;
        public $remarks;
        public $agent_type;
        public $agent_string;
        public $created;
        public $last_updated;
        
        public $errors = array();
        
        private function check_errors(){
            if(empty($this->user_string)){
                $this->errors[] = "User must be provided";
            }
            if(empty($this->title)){
                $this->errors[] = "The Advert Title must be stated";
            }
            if(empty($this->text)){
                $this->errors[] = "The Advert's Text must be provided";
            }
            if(empty($this->advert_type)){
                $this->errors[] = "The Advert's Type must be selected";
            }
            if(!empty($this->call_to_action_type)){
                if(empty($this->call_to_action_link)){
                    $this->errors[] = "The Call to Action Link must be provided";
                }
            }
            if(empty($this->proposed_start_date)){
                $this->errors[] = "Proposed Starting Date for the Advert must be selected";
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
                    if(empty($this->code)){
                        $this->code = $this->id.$time;
                        $this->save();
                    }
                    if(empty($this->verify_string)){
                        $string = $this->id.$time;
                        $this->verify_string = sha1($string);
                        $this->save();
                    }
                    if(empty($this->filename)){
                        $this->filename = "BILLBOARD_".$this->id.$time;
                        $this->save();
                    }
                    return true;
                } else {
                    $this->errors[] = "The Ad Application did not get uploaded";
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
			return self::find_by_sql("SELECT * FROM ".self::$table_name." ORDER BY created DESC");
		}
		
		public static function find_period_total($day){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE start_date<='{$day}' AND end_date>='{$day}' AND stage=4 ORDER BY created ASC");
		}
		
		public static function find_pending(){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE stage=2 ORDER BY created ASC");
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
		
		public static function find_by_user_string($string=""){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE user_string='{$string}' ORDER BY created DESC");
		}
		
		public static function find_by_code($string=""){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE code='{$string}' LIMIT 1");
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