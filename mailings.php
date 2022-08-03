<?php
    require_once(LIB_PATH.DS.'database.php');
    require_once(LIB_PATH.DS.'mailpasswords.php');
    require_once(LIB_PATH.DS."mailattachments.php");
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
    
    class Mailings {
        private static $table_name = "mailings";
        protected static $db_fields = array('id', 'verify_string', 'ticket_id', 'movement', 'from_name', 'from_mail', 'recipient_name', 'recipient_mail', 'recipient_cc_name', 'recipient_cc', 'recipient_bcc_name', 'recipient_bcc', 'subject', 'content', 'alt_content', 'reciever', 'reply_name', 'reply_mail', 'created', 'received_created', 'status');
        public $id;
        public $verify_string;
        public $ticket_id;
        public $movement;
        public $from_name;
        public $from_mail;
        public $recipient_name;
        public $recipient_mail;
        public $recipient_cc_name;
        public $recipient_cc;
        public $recipient_bcc_name;
        public $recipient_bcc;
        public $subject;
        public $content;
        public $alt_content;
        public $reciever;
        public $reply_name;
        public $reply_mail;
        public $created;
        public $received_created;
        public $status;
        
        public $attachments;
        
        public $errors = array();
        
        public function send_mail(){
            $time = time();
            if(empty($this->created)){
                $this->created = $time;
            }
            if($this->save()){
                $string = $this->id.$time;
                $this->verify_string = sha1($string);
                if($this->save()){
                    $mailpassword = MailPasswords::find_by_email($this->from_mail);
                    if(!empty($mailpassword)){
                        $mail = new PHPMailer();
                        
                        //Server settings
                        $mail->SMTPDebug = 2;                                       // Enable verbose debug output
                        $mail->isSMTP();                                            // Set mailer to use SMTP
                        $mail->Host       = $mailpassword->outgoing_server;  // Specify main and backup SMTP servers
                        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
                        $mail->Username   = $mailpassword->email;                     // SMTP username
                        $mail->Password   = $mailpassword->password;                               // SMTP password
                        $mail->SMTPSecure = 'ssl';                                  // Enable TLS encryption, `ssl` also accepted
                        $mail->Port       = $mailpassword->smtp_port;                                    // TCP port to connect to
                        
                        //Recipients
                        $mail->setFrom($this->from_mail, $this->from_name);
                        $recievers = explode(',', $this->recipient_mail);
                        foreach($recievers as $reciever){
                            $recieve = trim($reciever);
                            $mail->addAddress($recieve);
                        }
                        if(!empty($this->recipient_cc)){
                            $copiers_array = explode(',', $this->recipient_cc);
                            foreach($copiers_array as $copiers){
                                $copier = trim($copiers);
                                $mail->addCC($copier);
                            }
                        }
                        if(!empty($this->recipient_bcc)){
                            $bcopiers_array = explode(',', $this->recipient_bcc);
                            foreach($bcopiers_array as $bcopiers){
                                $bcopier = trim($bcopiers);
                                $mail->addBCC($bcopier);
                            }
                        }
                        //$mail->addAddress($this->recipient_mail, $this->recipient_name);     // Add a recipient
                        $mail->addReplyTo($this->reply_mail, $this->reply_name);
                        if(!empty($this->attachments)){
                            foreach($this->attachments as $attached){
                                $time = time();
                                $mailattach = new MailAttachments();
                                $mailattach->mail_string = $this->verify_string;
                                $mailattach->link = $attached->file;
                                $mailattach->name = $attached->name;
                                $mailattach->created = $time;
                                $mailattach->save();
                                $mail->addAttachment($attached->file, $attached->name);
                            }
                        }
                        
                        // Content
                        $mail->isHTML(true);                                  // Set email format to HTML
                        $mail->Subject = $this->subject;
                        $mail->Body    = $this->content;
                        $mail->AltBody = $this->alt_content;
                        //print_r($mail);
                        
                        $mail->send();
                        
                        return true;
                    } else {
                        return false;
                    }
                } else {
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
		
		public static function find_mails_by_address($email){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE from_mail='{$email}' OR reciever='{$email}' ORDER BY created DESC");
		}
		
		public static function find_sent_mails($email){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE from_mail='{$email}' AND movement='outgoing' ORDER BY created DESC");
		}
		
		public static function find_recieved_mails($email){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE reciever='{$email}' AND movement='incoming' ORDER BY created DESC");
		}
		
		public static function find_by_ticket_email($ticket_id, $email){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE ticket_id='{$ticket_id}' AND (from_mail='{$email}' OR reciever='{$email}') ORDER BY created DESC");
		}
		
		public static function find_by_sender($string, $movement){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE from_mail='{$string}' AND movement='{$movement}' ORDER BY created DESC");
		}
		
		public static function find_all_by_ticket($ticket_id){
		    return self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE ticket_id='{$ticket_id}' ORDER BY created DESC");
		}
		
		public static function find_by_verify_string($string=""){
		    global $database;
			$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE verify_string='{$string}' LIMIT 1");
			return !empty($result_array) ? array_shift($result_array) : false;
		}
		
		public static function find_by_id($id=0) {
			global $database;
			$result_array = self::find_by_sql("SELECT * FROM ".self::$table_name." WHERE id={$id} LIMIT 1");
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