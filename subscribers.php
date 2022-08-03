<?php
    require_once(LIB_PATH.DS."database.php");
    
    class Subscribers {
        private static $table_name = "subscribers";
        protected static $db_fields = array('id', 'verify_string', 'user_string', 'business_string', 'subscription_string', 'paymentplan_string', 'amount_paid', 'duration_type', 'duration', 'started', 'expired', 'true_expiry', 'status', 'payment_method', 'auto_renew', 'agent_type', 'agent_string', 'created', 'last_updated');
        public $id;
        public $verify_string;
        public $user_string;
        public $business_string;
        public $subscription_string;
        public $paymentplan_string;
        public $amount_paid;
        public $duration_type;
        public $duration;
        public $started;
        public $expired;
        public $true_expiry;
        public $status;
        public $payment_method;
        public $auto_renew;
        public $agent_type;
        public $agent_string;
        public $created;
        public $last_updated;
        
        public $errors = array();
        
        private function check_errors(){
            if(empty($this->user_string)){
                $this->errors[] = "User was not provided";
            }
            if(empty($this->business_string)){
                $this->errors[] = "Business was not provided";
            }
            if(empty($this->subscription_string)){
                $this->errors[] = "Subscription Package not provided";
            }
            if(empty($this->paymentplan_string)){
                $this->errors[] = "Payment Plan not provided";
            }
            if(empty($this->duration_type)){
                $this->errors[] = "Duration Type not provided";
            }
            if(empty($this->duration)){
                $this->errors[] = "Duration not provided";
            }
            if(empty($this->started)){
                $this->errors[] = "The Starting time of the Subscription must be stated";
            }
            if(empty($this->expired)){
                $this->errors[] = "The Expiry time of the Subscription must be stated";
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
                if($this->auto_renew == 1){
                    $added = 60 * 60 * 24 * 7;
                    $this->true_expiry = $this->expired + $added;
                } else {
                    $this->true_expiry = $this->expired;
                }
                if($this->save()){
                    if(empty($this->verify_string)){
                        $string = $this->id.$time;
                        $this->verify_string = sha1($string);
                        $this->save();
                    }
                    return true;
                } else {
                    $this->errors[] = "Subscription did not get saved";
                    return false;
                }
            } else {
                return false;
            }
        }
        
        public function subscribe(){
            if(!empty($this->duration_type) && !empty($this->duration) && !empty($this->started)){
                $fmr_subscriptions = self::find_by_business_string($this->business_string);
                if(!empty($fmr_subscriptions)){
                    foreach($fmr_subscriptions as $sub){
                        $sub->status = 0;
                        $sub->insert();
                    }
                }
                if($this->duration_type == 1){
                    $added_duration = 60 * 60 * 24 * $this->duration;
                    $this->expired = $added_duration + $this->started;
                } elseif($this->duration_type == 2){
                    $added_duration = 60 * 60 * 24 * 7 * $this->duration;
                    $this->expired = $added_duration + $this->started;
                } elseif($this->duration_type  == 4){
                    $current_year = strftime("%Y", $this->started);
                    $int_year = (int)$current_year;
                    $next_year = $int_year + $duration;
                    $month = strftime('%m', $this->started);
                    $dating = strftime('%d', $this->started);
                    $timing = strftime('%H:%M:%S', $this->started);
                    if(($month == '02') && ($dating == 29) && ($duration % 4 != 0)){
                        $new_dating = 28;
                        $new_time = strtotime($next_year.'-'.$month.'-'.$new_dating.' '.$timing);
                        $this->expired = $new_time;
                    } else {
                        $new_time = strtotime($next_year.'-'.$month.'-'.$dating.' '.$timing);
                        $this->expired = $new_time;
                    }
                } elseif($this->duration_type == 3){
                    $current_year = strftime('%Y', $this->started);
                    $int_year = (int)$current_year;
                    $current_month = strftime('%m', $this->started);
                    $int_month = (int)$current_month;
                    $dating = strftime('%d', $this->started);
                    $timing = strftime('%H:%M:%S', $this->started);
                    $int_dating = (int)$dating;
                    $proposed_month = $int_month + $this->duration;
                    if($proposed_month > 12){
                        $props_month = $proposed_month - 12;
                        $new_year = $int_year + 1;
                    } else {
                        $props_month = $proposed_month;
                        $new_year = $int_year;
                    }
                    $str_month = (string)$props_month;
                    if(strlen($str_month) < 2){
                        $new_month = '0'.$str_month;
                    } else {
                        $new_month = $str_month;
                    }
                    if($new_month == '02'){
                        if((($new_year % 4) == 0) && ($dating > 29)){
                            $new_dating = 29;
                        } elseif((($new_year % 4) != 0) && ($dating > 28)){
                            $new_dating = 28;
                        } else {
                            $new_dating = $dating;
                        }
                    } elseif((($new_month == '04') || ($new_month == '06') || ($new_month == '09') || ($new_month == '11')) && ($dating > 30)){
                        $new_dating = 30;
                    } else {
                        $new_dating = $dating;
                    }
                    $new_time = strtotime($new_year.'-'.$new_month.'-'.$new_dating.' '.$timing);
                    $this->expired = $new_time;
                }
                if($this->insert()){
                    return true;
                } else {
                    return false;
                }
            } else {
                $this->errors[] = "There is no proper Duration data";
                return false;
            }
        }
        
        
        public function renew(){
            if(!empty($this->duration_type) && !empty($this->duration) && !empty($this->started)){
                $last_subscription = self::find_business_latest_subscription($this->business_string);
                if(!empty($last_subscription)){
                    $last_subscription->status = 2;
                    $last_subscription->insert();
                }
                
                if($this->duration_type == 1){
                    $added_duration = 60 * 60 * 24 * $this->duration;
                    $this->expired = $added_duration + $this->started;
                } elseif($this->duration_type == 2){
                    $added_duration = 60 * 60 * 24 * 7 * $this->duration;
                    $this->expired = $added_duration + $this->started;
                } elseif($this->duration_type  == 4){
                    $current_year = strftime("%Y", $this->started);
                    $int_year = (int)$current_year;
                    $next_year = $int_year + $duration;
                    $month = strftime('%m', $this->started);
                    $dating = strftime('%d', $this->started);
                    $timing = strftime('%H:%M:%S', $this->started);
                    if(($month == '02') && ($dating == 29) && ($duration % 4 != 0)){
                        $new_dating = 28;
                        $new_time = strtotime($next_year.'-'.$month.'-'.$new_dating.' '.$timing);
                        $this->expired = $new_time;
                    } else {
                        $new_time = strtotime($next_year.'-'.$month.'-'.$dating.' '.$timing);
                        $this->expired = $new_time;
                    }
                } elseif($this->duration_type == 3){
                    $current_year = strftime('%Y', $this->started);
                    $int_year = (int)$current_year;
                    $current_month = strftime('%m', $this->started);
                    $int_month = (int)$current_month;
                    $dating = strftime('%d', $this->started);
                    $timing = strftime('%H:%M:%S', $this->started);
                    $int_dating = (int)$dating;
                    $proposed_month = $int_month + $this->duration;
                    if($proposed_month > 12){
                        $props_month = $proposed_month - 12;
                        $new_year = $int_year + 1;
                    } else {
                        $props_month = $proposed_month;
                        $new_year = $int_year;
                    }
                    $str_month = (string)$props_month;
                    if(strlen($str_month) < 2){
                        $new_month = '0'.$str_month;
                    } else {
                        $new_month = $str_month;
                    }
                    if($new_month == '02'){
                        if((($new_year % 4) == 0) && ($dating > 29)){
                            $new_dating = 29;
                        } elseif((($new_year % 4) != 0) && ($dating > 28)){
                            $new_dating = 28;
                        } else {
                            $new_dating = $dating;
                        }
                    } elseif((($new_month == '04') || ($new_month == '06') || ($new_month == '09') || ($new_month == '11')) && ($dating > 30)){
                        $new_dating = 30;
                    } else {
                        $new_dating = $dating;
                    }
                    $new_time = strtotime($new_year.'-'.$new_month.'-'.$new_dating.' '.$timing);
                    $this->expired = $new_time;
                }
                if($this->insert()){
                    return true;
                } else {
                    return false;
                }
            } else {
                $this->errors[] = "There is no proper Duration data";
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
		
		public static function find_by_business_string($string){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE business_string='{$string}' ORDER BY created DESC");
		}
		
		public static function find_active_by_subscription_string($string=""){
		    $time = time();
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE status=1 AND true_expiry>=$time AND subscription_string='{$string}' ORDER BY created ASC");
		}
		
		public static function find_business_latest_subscription($string){
		    global $database;
		    $result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE business_string='{$string}' ORDER BY created DESC LIMIT 1");
		    return !empty($result_array) ? array_shift($result_array) : false;
		}
		
		public static function check_active_business_package_subscription($business_string, $subscription_string){
		    $time = time();
		    $result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE business_string='{$business_string}' AND subscription_string='{$subscription_string}' AND status=1 AND true_expiry>={$time} LIMIT 1");
		    return !empty($result_array) ? array_shift($result_array) : false;
		}
		
		public static function find_active_subscribers(){
		    $time = time();
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE status=1 AND true_expiry>={$time} ORDER BY created ASC");
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