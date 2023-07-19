<?php
    require_once(LIB_PATH.DS."database.php");
    
    class Businesses {
        private static $table_name = "businesses";
        protected static $db_fields = array('id', 'verify_string', 'name', 'description', 'user_string', 'subscription_string', 'category', 'facilities', 'address', 'town', 'lga', 'state', 'profile_img', 'cover_img', 'state_id', 'phonenumber', 'whatsapp', 'email', 'website', 'facebook_link', 'twitter_link', 'instagram_link', 'youtube_link', 'linkedin_link', 'working_hours', 'cv', 'modifiedby', 'experience', 'month_started', 'year_started', 'reg_stage', 'activation', 'filename', 'remarks', 'created', 'last_updated');
        public $id;
        public $verify_string;
        public $name;
        public $description;
        public $user_string;
        public $subscription_string;
        public $category;
        public $facilities;
        public $address;
        public $town;
        public $lga;
        public $state;
        public $state_id;
        public $phonenumber;
        public $whatsapp;
        public $email;
        public $website;
        public $facebook_link;
        public $twitter_link;
        public $instagram_link;
        public $youtube_link;
        public $linkedin_link;
        public $working_hours;
        public $cv;
        public $modifiedby;
        public $experience;
        public $month_started;
        public $year_started;
        public $reg_stage;
		public $profile_img;
		public $cover_img;
        public $activation;
        public $filename;
        public $remarks;
        public $created;
        public $last_updated;
        
        public $errors = array();
        
        private function check_errors(){
            if(empty($this->user_string)){
                $this->errors[] = "User must be provided";
            } 
            if(empty($this->name)){
                $this->errors[] = "Business Name has to be provided";
            }
            if(empty($this->address)){
                $this->errors[] = "Business Address must be provided";
            }
            /*if(empty($this->town)){
                $this->errors[] = "The Town where the Business is situated must be stated";
            }
            if(empty($this->state)){
                $this->errors[] = "State where the Business is situated must be given";
            }*/
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
                    $this->errors[] = "Business details was not saved";
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
			return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE activation=1");
		}

		// public static function count_all() {
		// 	return self::find_by_sql("SELECT COUNT(name) FROM ".self::$table_name);
		// }
		
		public static function find_by_user($user){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE user='{$user}' AND activation=1 ORDER BY datecreated");
		}
		
		public static function find_by_user_string($string){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE user_string='{$string}' AND activation=1 ORDER BY name");
		}

		public static function find_by_user_string_paginated($string, $per_page=20, $offset=0) {
			global $database;
			$sql = "SELECT * FROM ".self::$table_name." WHERE user_string='{$string}' AND activation=1 ORDER BY name ASC";
			$sql .= "LIMIT {$per_page} ";
			$sql .= "OFFSET {$offset}";
			$result_array = self::find_by_sql($sql);
			return !empty($result_array) ? $result_array : false;
		}
		
		public static function find_pending_businesses(){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE reg_stage=3 AND activation=1 ORDER BY created ASC");
		}
		
		public static function find_approved_businesses(){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE reg_stage=4 AND activation=1 ORDER BY created ASC");
		}
		
		public static function find_approved_businesses_by_state($state){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE reg_stage=4 AND state='{$state}' AND activation=1 ORDER BY created ASC");
		}

		public static function find_approved_business_paginated($per_page=20, $offset=0) {
			global $database;
			$sql = "SELECT * FROM ".self::$table_name." WHERE reg_stage=4 AND activation=1 ";
			$sql .= "LIMIT {$per_page} ";
			$sql .= "OFFSET {$offset}";
			$result_array = self::find_by_sql($sql);
			return !empty($result_array) ? $result_array : false;
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
		
		public static function find_by_verify_string($string="") {
			global $database;
			$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE verify_string='{$string}' LIMIT 1");
			return !empty($result_array) ? array_shift($result_array) : false;
		}

		public static function find_by_verify_string_paginated($string="", $per_page=20, $offset=0) {
			global $database;
			$sql = "SELECT * FROM ".self::$table_name." WHERE verify_string='{$string}' ";
			$sql .= "LIMIT {$per_page} ";
			$sql .= "OFFSET {$offset}";
			$result_array = self::find_by_sql($sql);
			return !empty($result_array) ? $result_array : false;
		}

		public static function count_approved_businesses() {
			global $database;
			$sql = "SELECT COUNT(*) FROM ".self::$table_name." WHERE reg_stage=4 AND activation=1";
			$result_set = $database->query($sql);
			$row = $database->fetch_array($result_set);
			return array_shift($row);
		}
		
		public static function count_all() {
			global $database;
			$sql = "SELECT COUNT(*) FROM ".self::$table_name." WHERE activation=1";
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