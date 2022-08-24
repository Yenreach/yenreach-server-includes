<?php
    require_once(LIB_PATH.DS."database.php");
    
    class BlogPost {
        private static $table_name = "blogpost";
        protected static $db_fields = array('id', 'blog_string', 'admin_string', 'title', 'author', 'snippet', 'post', 'file_path', 'created_at', 'updated_at');

        public $id;
        public $blog_string;
        public $admin_string;
        public $title;
        public $author;
		public $snippet;
        public $post;
		public $file_path;
        public $created_at;
        public $updated_at;
        
        public $errors = array();
        
        private function check_errors(){
            if(empty($this->title)){
                $this->errors[] = "The Blog title must be provided";
            }
            if(empty($this->author)){
                $this->errors[] = "The Blog author must be provided";
            }
            if(empty($this->post)){
                $this->errors[] = "The Blog post must be provided";
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
                    if(empty($this->blog_string)){
                        $string = $this->id.$time;
                        $this->blog_string = sha1($string);
                        $this->save();
                    }
                    return true;
                } else {
                    $this->errors[] = "The Blog was not saved";
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
		
		public static function find_by_author($string){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE author='{$string}'");
		}
		
		public static function find_by_title($string){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE title='{$string}' ORDER BY created ASC");
		}

        public static function find_by_admin_string($string="") {
            global $database;
            $result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE admin_string='{$string}' LIMIT 1");
            return !empty($result_array) ? array_shift($result_array) : false;
        }

		public static function find_by_blog_string($string="") {
			global $database;
			$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE blog_string='{$string}' LIMIT 1");
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