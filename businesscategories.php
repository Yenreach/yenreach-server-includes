<?php
    require_once(LIB_PATH.DS."database.php");
    
    class BusinessCategories {
        private static $table_name = "businesscategories";
        protected static $db_fields = array('id', 'verify_string', 'section_string', 'category_string', 'category', 'business_string', 'created', 'last_updated');
        public $id;
        public $verify_string;
        public $section_string;
        public $category_string;
        public $category;
        public $business_string;
        public $created;
        public $last_updated;
        
        public $errors = array();
        
        private function check_errors(){
            if(empty($this->category_string)){
                $this->errors[] = "Category must be provided";
            }
            if(empty($this->business_string)){
                $this->errors[] = "Business must be provided";
            }
            if(!empty($this->business_string) && !empty($this->category_string)){
                $fsql = "SELECT * FROM ".self::$table_name." WHERE category_string='{$this->category_string}' AND business_string='{$this->business_string}'";
                $founds = self::find_by_sql($fsql);
                if(!empty($founds)){
                    $this->errors[] = "Category already added";
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
                        $this->verify_string = sha1($string);
                        $this->save();
                    }
                    return true;
                } else {
                    $this->errors[] = "Category was not added to the Business";
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
			return self::find_by_sql("SELECT * FROM ".self::$table_name." ORDER BY category ASC");
		}
		
		public static function find_by_category_string($string){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE category_string='{$string}'");
		}

		public static function find_by_category_string_paginated($string, $per_page=0, $offset=0){
		    $sql = "SELECT * FROM ".self::$table_name." WHERE category_string='{$string}'";
		    if($per_page > 0){
		        $sql .= " LIMIT {$per_page}";
		        if($offset > 0){
		            $sql .= " OFFSET {$offset}";
		        }
		    }
		    return self::find_by_sql($sql);
		}
		
		public static function find_by_section_string($string){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE section_string='{$string}'");
		}
		
		public static function find_by_business_string($string){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE business_string='{$string}' ORDER BY category ASC");
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