<?php
    require_once(LIB_PATH.DS."database.php");
    
    class PageVisits {
        private static $table_name = "pagevisits";
        protected static $db_fields = array('id', 'business_string', 'categories', 'user_string', 'day', 'month', 'year', 'frequency', 'created', 'last_updated');
        public $id;
        public $business_string;
        public $categories;
        public $user_string;
        public $day;
        public $month;
        public $year;
        public $frequency;
        public $created;
        public $last_updated;
        
        public $errors = array();
        
        private function check_errors(){
            if(empty($this->business_string)){
                $this->errors[] = "Business must be provided";
            }
            if(empty($this->user_string)){
                $this->errors[] = "User must be provided";
            }
            if(empty($this->day)){
                $this->errors[] = "Visit Date must be provided";
            }
            if(empty($this->month)){
                $this->errors[] = "Visit Month must be provided";
            }
            if(empty($this->year)){
                $this->errors[] = "Visit Year must be provided";
            }
            if(empty($this->frequency)){
                $this->errors[] = "Visit Frequency must be provided";
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
                    return true;
                } else {
                    $this->errors[] = "Page Visit not Uploaded";
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
			return self::find_by_sql("SELECT * FROM ".self::$table_name." ORDER BY name ASC");
		}
		
		public static function find_by_id($id=0) {
			global $database;
			$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE id={$id} LIMIT 1");
			return !empty($result_array) ? array_shift($result_array) : false;
		}
		
		public static function find_previous_visit($business, $user, $day, $month, $year){
		    $result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE business_string='{$business}' AND user_string='{$user}' AND day='{$day}' AND month='{$month}' AND year='{$year}' LIMIT 1");
		    return !empty($result_array) ? array_shift($result_array) : false;
		}
		
		public static function find_by_business_string($string){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE business_string='{$string}' ORDER BY created DESC");
		}

		public static function count_by_business_string($string){
			global $database;
			$sql = "SELECT COUNT(*) FROM ".self::$table_name." WHERE business_string='{$string}'";
			$result_set = $database->query($sql);
			$row = $database->fetch_array($result_set);
			return array_shift($row);
		}
		
		public static function find_business_day_visit($business, $day, $month, $year){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE business_string='{$business}' AND day='{$day}' AND month='{$month}' AND year='{$year}' ORDER BY created DESC");
		}
		
		public static function find_business_limit($business, $date_from, $date_to){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE business_string='{$business}' AND created>='{$date_from}' AND last_updated<='{$date_to}' ORDER BY last_updated DESC");
		}
		
		public static function find_by_user_string($string){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE user_string='{$string}' ORDER BY created DESC");
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
			$database->query($sql);
			return ($database->affected_rows() == 1) ? true : false;
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