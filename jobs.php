<?php
    require_once(LIB_PATH.DS."database.php");
    
    class Jobs {
        private static $table_name = "jobs";
        protected static $db_fields = array('id', 'business_string', 'company_name', 'job_string', 'job_title', 'job_type', 'location', 'salary', 'job_overview', 'job_benefit', 'status', 'created_at', 'updated_at');
        public $id;
        public $business_string;
        public $job_string;
        public $job_title;
		public $company_name;
        public $job_type;
        public $location;
        public $salary;
        public $job_overview;
        public $job_benefit;
		public $status;
        public $created_at;
        public $updated_at;
        
        public $errors = array();
        
        private function check_errors(){
            if(empty($this->job_title)){
                $this->errors[] = "job title must be provided";
            } 
            if(empty($this->job_type)){
                $this->errors[] = "job type has to be provided";
            }
			if(empty($this->business_string)){
				$this->errors[] = "business string must be provided";
			}
        }
        
        public function insert(){
            $this->check_errors();
            if(empty($this->errors)){
                $time = time();
                if(empty($this->created_at)){
                    $this->created_at = $time;
                }
                $this->updated_at = $time;
                if($this->save()){
                    if(empty($this->job_string)){
                        $string = $this->id.$time;
                        $this->job_string = sha1($string);
                        $this->save();
                    }
                    // if(empty($this->filename)){
                    //     $this->filename = "LOGO_".$this->id.$time;
                    //     $this->save();
                    // }
                    return true;
                } else {
                    $this->errors[] = "Job details was not saved";
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
					$object->$attribute = formatString(html_entity_decode($value));
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
		
		public static function find_by_user($user){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE user='{$user}' AND activation=1 ORDER BY datecreated");
		}
		
		public static function find_by_business_string($string){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE business_string='{$string}'");
		}

		public static function find_by_id($id=0) {
			global $database;
			$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE id={$id} LIMIT 1");
			return !empty($result_array) ? array_shift($result_array) : false;
		}

		public static function delete_filepath($id=0) {
			global $database;
			$result_array = self::find_by_sql("UPDATE ".self::$table_name." SET filepath = '' WHERE id={$id}");
			return !empty($result_array) ? array_shift($result_array) : false;
		}

		// UPDATE `businessphotos` SET `filepath` = '' WHERE `businessphotos`.`id` = 25
		
		public static function find_by_job_string($string="") {
			global $database;
			$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE job_string='{$string}' LIMIT 1");
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