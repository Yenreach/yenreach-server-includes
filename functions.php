<?php
    
    date_default_timezone_set("Africa/Lagos");
	
	function strip_zeros_from_date($marked_string="") {
		// removing the marked zeros
		$no_zeros = str_replace('*0', '', $marked_string);
		// removing every other remaining marks
		$cleaned_string = str_replace('*', '', $no_zeros);
		return $cleaned_string;
	}
	
	function redirect_to( $location = NULL ) {
		if($location != NULL) {
			header("Location: {$location}");
			exit;
		}
	}

	function formatString($str) {
        $pattern1 = "/&nbsp;/i";
        $str = preg_replace($pattern1, " ", $str);
        return $str;
 	};
	
	function include_layout_template($template="") {
		include(HTML_ROOT.DS.'layouts'.DS.$template);
	}
	
	function datetime_to_text($datetime="") {
		$unixdatetime = strtotime($datetime);
		return strftime("%B %d, %Y at %I:%M %p", $unixdatetime);
	}
	
	function output_message($message="") {
		if(!empty($message)) {
			return "<p class=\"message\">{$message}</p>";
		} else {
			return "";
		}
	}
	
	function ask_date($day) {
		for($a=1; $a<=31; $a++) {
			echo "<option value=\"{$a}\"";
			if($a == $day) {
				echo " selected";
			}
			echo ">".$a."</option>";
		}
	}
	
	function year($year){
		$time = time();
		$year1 = 2020;
		$year2 = strftime("%Y", $time);
		for($a=$year2; $a>=$year1; $a--){
			echo "<option value=\"{$a}\"";
			if($year == $a){
				echo " selected";
			}
			echo ">".$a."</option>";
		}
	}
	
	function gallery_year($year){
		$time = time();
		$year1 = 2005;
		$year2 = strftime("%Y", $time);
		for($a=$year2; $a>=$year1; $a--){
			echo "<option value=\"{$a}\"";
			if($year == $a){
				echo " selected";
			}
			echo ">".$a."</option>";
		}
	}
	
	function month($month) {
		for($a=1; $a<=12; $a++) {
			echo "<option value=\"{$a}\"";
			if($a == $month){
				echo " selected";
			}
			echo ">".$a."</option>";
		}
	} 
	
	$months = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');
	function months($mo) {
		global $months;
		foreach($months as $month) {
			echo "<option value=\"{$month}\"";
			if($month == $mo){
				echo " selected";
			}
			echo ">".classify_month($month)."</option>";
		}
	}
	
	function classify_month($month) {
		if($month == '01') {
			return "January";
		} elseif($month == '02') {
			return "February";
		} elseif($month == '03') {
			return "March";
		} elseif($month == '04') {
			return "April";
		} elseif($month == '05') {
			return "May";
		} elseif($month == '06') {
			return "June";
		} elseif($month == '07') {
			return "July";
		} elseif($month == '08') {
			return "August";
		} elseif($month == '09') {
			return "September";
		} elseif($month == '10') {
			return "October";
		} elseif($month == '11') {
			return "November";
		} elseif($month == '12') {
			return "December";
		} else {
			return "";
		}
	}
	
	function classify_date($day) {
		if(!empty($day)){
			if($day == 1 || $day == 21 || $day == 31) {
				return $day."st";
			} elseif($day == 2 || $day == 22) {
				return $day."nd";
			} elseif($day == 3 || $day == 23) {
				return $day."rd";
			} else {
				return $day."th";
			}	
		} else {
			return "";
		}		
	}
	
	function last_day($month, $year){
	    if(($month == '04') || ($month == '06') || ($month == '09') || ($month == '11')){
	        $last_day = 30;
	    } elseif($month == '02'){
	        if($year % 4 == 0){
	            if($year % 100 != 0){
	                $last_day = 29;
	            } else {
	                if($year % 400 == 0){
	                    $last_day = 29;
	                } else {
	                    $last_day = 28;
	                }
	            }
	        } else {
	            $last_day = 28;
	        }
	    } else {
	        $last_day = 31;
	    }
	    return $last_day;
	}
	
	function autho_level($autho_level){
		if($autho_level == '1'){
			return "admin";
		} elseif($autho_level == '2'){
			return "chief_admin";
		} elseif($autho_level == '3'){
			return "gen_admin";
		} elseif($autho_level == '4'){
			return "agent";
		} else {
			return "";
		}
	}
	
	function gender($sex){
		$genders = array('Male', 'Female');
		foreach($genders as $gender){
			echo "<option value=\"{$gender}\"";
			if($sex == $gender){
				echo " selected";
			}
			echo ">".$gender."</option>";
		}
	}
	
	function classify_day($day){
		if($day == 1){
			return "Sunday";
		} elseif($day == 2){
			return "Monday";
		} elseif($day == 3){
			return "Tuesday";
		} elseif($day == 4){
			return "Wednesday";
		} elseif($day == 5){
			return "Thursday";
		} elseif($day == 6){
			return "Friday";
		} elseif($day == 7){
			return "Saturday";
		} else {
			return "";
		}
	}
	
	function weekdays($dayed){
		$days = array(1, 2, 3, 4, 5, 6, 7);
		foreach($days as $day){
			echo "<option value=\"{$day}\"";
			if($day == $dayed){
				echo " selected";
			}
			echo ">".classify_day($day)."</option>";
		}
	}
	
	function divide_bytes($bytes){
		if(!empty($bytes)){
			if($bytes/1048576 >= 1){
				$div_bytes = $bytes/1048576;
				$round_bytes = round($div_bytes, 2);
				$finished_bytes = $round_bytes."MB";
				return $finished_bytes;
			} elseif($bytes/1024 >= 1){
				$div_bytes = $bytes/1024;
				$round_bytes = round($div_bytes, 2);
				$finished_bytes = $round_bytes."KB";
				return $finished_bytes;
			} else {
				$finished_bytes = $bytes."Bytes";
				return $finished_bytes;
			}
		} else {
			return "";
		}
	}
	
	function withdrawal_stage($status){
	    if($status == 0){
	        $stage = "Failed/Cancelled";
	    } elseif($status == 1){
	        $stage = "Applied";
	    } elseif($status == 2){
	        $stage = "Pending Approval";
	    } elseif($status == 3){
	        $stage = "Suspended";
	    } elseif($status == 4){
	        $stage = "Approved";
	    } elseif($status == 5){
	        $stage = "Disbursed";
	    } else {
	        $stage = "";
	    }
	    return $stage;
	}
	
	function publickey(){
	    $testkey = 'FLWPUBK_TEST-c6308b525303ee6ebaf4e7eedd4c4e32-X';
	    $livekey = 'FLWPUBK-e535ae456a4097e6d9da992ab9db70b4-X';
	    return $livekey;
	}
	
	function secretkey(){
	    $testkey = 'FLWSECK_TEST-c8cbfc0a6377b981842cee1561065381-X';
	    $livekey = 'FLWSECK-d0c7583b3b82a8380ac81946df7a3980-X';
	    return $livekey;
	}
	
	function encryptionkey(){
	    $testkey = 'FLWSECK_TEST92a165aa7e02';
	    $livekey = 'd0c7583b3b82a50a6bd0b965';
	    return $livekey;
	}
	
	function rave_post_curl($url, $data){
	    $key = secretkey();   
        $headers = array('Content-Type: application/json', 'Authorization: Bearer '.$key);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 200);
        curl_setopt($ch, CURLOPT_TIMEOUT, 200);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        
        $request = curl_exec($ch);
        
        if($request){
            $result = json_decode($request);
            if($result){
                return $result;
            } else {
                die("Couldn't convert the recieved data to an Object");
            }
        } else {
            if(curl_error($ch)){
                die('Rave Error:' . curl_error($ch));
            }
        }
	}
	
	function rave_get_curl($url){
	    $key = secretkey();   
        $headers = array('Content-Type: application/json', 'Authorization: Bearer '.$key);
        
        $result = array();
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $request = curl_exec($ch);
        
        
        if($request){
            $result = json_decode($request);
            if($result){
                return $result;
            } else {
                die("Couldn't convert the recieved data to an Object");
            }
        } else {
            if(curl_error($ch)){
                die('Rave Error:' . curl_error($ch));
            }
        }
		curl_close($ch);
	}
	
	function perform_post_curl($url, $payload){
        $result = array();
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,'https://yenreach.omotolaniolurotimi.com/api/'.$url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));  //Post Fields
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,3);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        
        $request = curl_exec ($ch);
        
        curl_close ($ch);
        
        if ($request) {
            $result = json_decode($request);
            return $result;
        } else {
            die(var_dump($request));
            die("Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok");
        }
	}
	
	function perform_get_curl($url){
	    $result = array();
    
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://yenreach.omotolaniolurotimi.com/api/'.$url);
        curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $request = curl_exec($ch);
        curl_close($ch);
        
        if($request){
            $result = json_decode($request);
            if($result){
                return $result;
            } else {
                print_r($result);
                die("Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable."); 
            }
        } else {
            var_dump($request);
            die("Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok");
        }
	}
	
	function perform_del_curl($url){
	    $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $headers = [
          //'Authorization: ',
          'Authorization: ',
          'Content-Type: application/json'
        
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $request = curl_exec($ch);
        
        curl_close($ch);
        
        if($request){
            $result = json_decode($request, true);
            if($result){
                return $result;
            } else {
                //print_r($result);
                die("Something went wrong while trying to convert the request variable to json. Uncomment the print_r command to see what is in the result variable."); 
            }
        } else {
            //var_dump($request);
            die("Something went wrong while executing curl. Uncomment the var_dump line above this line to see what the issue is. Please check your CURL command to make sure everything is ok");
        }
	}
	
	function calculate_amount($amount){
	    $topay = (int)$amount;
	    if($topay > 0){
	        $due = ($topay + 100) / 0.985;
	        $ceil_amount = ceil($due);
	        return $ceil_amount;
	    } else {
	        return 0;
	    }
	}
	
	function income_range($income){
	    $ranges = array('Below 20,000', '20,000 - 49,999', '50,000 - 99,999', '100,000 - 149,999', '150,000 - 199,000', '200,000 - 299,999', '300,000 - 499,999', '500,000 - 1,000,000', 'Above 1,000,000');
	    foreach($ranges as $range){
	        echo "<option value=\"{$range}\"";
	        if($range == $income){
	            echo " selected";
	        }
	        echo ">{$range}</option>";
	    }
	}
	
?>