<?php
class CommonFunctions
{
	/**
	 * 
	 * function to format date by the given format
	 * 
	 */
	public static function formatDate($pDate,$pFormat='d/m/Y')
	{
		$retVal='';
		
		if(isset($pDate) && $pDate!="")
		{
			$retVal=date($pFormat,strtotime($pDate));
		}
		return $retVal;
	}
	/**
	 * 
	 * function to check wheter the date is valid or not
	 * 
	 */
	public function isValidDateFormat($pDate)
	{
		//YYYY-MM-DD
		if(preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/',$pDate))
		//if(preg_match('/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/',$pDate))
		{ 
			return true;
		}
		else
		{ 
			return false;
		}
	}
	/**
	 * 
	 * 
	 * 
	 */
	public static function generateShortPassword()
	{
		return uniqid();
	}
	/**
	 * 
	 * function to generate six digit random number
	 * 
	 */
	public static function generateRandomNumber()
	{
		return mt_rand(100000,999999);
	}
	/**
	 * 
	 * function to generate Unique id.
	 * 
	 */
	public static function generateUniqueId()
	{
		return uniqid();
	}
	/**
	 * 
	 * function to generate 32-digit GUID.
	 * 
	 */
	public static function generateGUID($prefix = '')  
	{
	    $chars = md5(uniqid(mt_rand(), true));
	    $uuid  = substr($chars,0,8) . '-';
	    $uuid .= substr($chars,8,4) . '-';
	    $uuid .= substr($chars,12,4) . '-';
	    $uuid .= substr($chars,16,4) . '-';
	    $uuid .= substr($chars,20,12);
	
	    return $prefix . $uuid;
	}
	/**
	 * 
	 * function to format amount with currency code
	 * 
	 */	
	public static function formatCurrency($amount,$code=null)
	{
		$retValue="";
		$currencyCode="$";
		
		if($code!=null && $code!="")
		{
			//use select case to provide currency symbol
			switch(strtoupper($code))
			{
				case "USD":
				$currencyCode="$";
					break;
				case "EUR":
				$currencyCode="€";
					break;
				case "GBP":
				$currencyCode="£";
					break;
				//case "INR":
				//currForm.currencySymbol=""
				 default:
				 $currencyCode=$code;
				break;
			}
		}
		//echo gettype($amount);
		$retValue=$currencyCode.number_format(floatval($amount),'2');
		
		
		return $retValue;
	}
	/**
	 * 
	 * function to convert datetime into time ago format.
	 * 
	 */	
	public static function convertDateTime_To_Ago($pDate)
	{
		$periods= array("second", "minute", "hour", "day", "week", "month", "year", "decade"); 
		$lengths= array("60","60","24","7","4.35","12","10");  
		$now= time(); 
		$unix_date= strtotime($pDate);
		     
		// check validity of date 
		if(empty($unix_date)) 
		{        
			return "N/A"; 
		}  
		// is it future date or past date 
		if($now > $unix_date) 
		{        
			$difference= $now - $unix_date;     
			$tense= "ago";  
		} 
		else 
		{
			$difference = $unix_date - $now;     
			$tense= "from now"; 
		}  
		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) 
		{     
			$difference /= $lengths[$j]; 
		} 
		 
		$difference = round($difference); 
		 
		if($difference != 1) 
		{     
			$periods[$j].= "s"; 
		}  
		
		return "$difference $periods[$j] {$tense}";
		
	}
	/**
	 * 
	 * function convert array to zend framework url
	 * 
	 */
	public static function convertQueryArrayToZendUrl($pFixedUrl,$pArray)
	{
		$retVal='';
		$retVal.=$pFixedUrl;
		
		if(is_array($pArray) && count($pArray)>0)
		{
			foreach($pArray as $key=>$val)
			{
				$retVal.='/'.$key.'/'.$val;
			}
		}
		return $retVal;
	}

	/**
	 * 
	 * function return full day name by shor day name
	 * 
	 */
	 public static function getFullDayNameByShortName($pShortName)
	 {
		$retVal='';
		
		if(isset($pShortName) && $pShortName!="")
		{
			switch(strtolower($pShortName))
			{
				case 'mon':
					$retVal='Monday';
				break;
				case 'tue':
					$retVal='Tuesday';
				break;
				case 'wed':
					$retVal='Wednesday';
				break;
				case 'thu':
					$retVal='Thursday';
				break;
				case 'fri':
					$retVal='Friday';
				break;
				case 'sat':
					$retVal='Saturday';
				break;
				case 'sun':
					$retVal='Sunday';
				break;
			}
		}
		return $retVal;
	 }
	/**
	 * 
	 * function return full month name by shor month name
	 * 
	 */
	public static function getFullMonthNameByShortName($pShortName)
	{
		$retVal='';
		
		if(isset($pShortName) && $pShortName!="")
		{
			switch(strtolower($pShortName))
			{
				case 'jan':
					$retVal='January';
				break;
				case 'feb':
					$retVal='February';
				break;
				case 'mar':
					$retVal='March';
				break;
				case 'apr':
					$retVal='April';
				break;
				case 'may':
					$retVal='May';
				break;
				case 'jun':
					$retVal='June';
				break;
				case 'jul':
					$retVal='July';
				break;
				case 'aug':
					$retVal='August';
				break;
				case 'sep':
					$retVal='September';
				break;
				case 'oct':
					$retVal='October';
				break;
				case 'nov':
					$retVal='November';
				break;
				case 'dec':
					$retVal='December';
				break;
			}
		}
		return $retVal;
	 }
	 /**
	 * 
	 * function return full month name by shor month name
	 * 
	 */
	 public static function getMonthByMonthName($pMonthName)
	 {
		$retVal=00;
		
		if(isset($pMonthName) && $pMonthName!="")
		{
			switch(strtolower($pMonthName))
			{
				case 'january':
				case 'jan':
					$retVal=01;
				break;
				case 'february':
				case 'feb':
					$retVal=02;
				break;
				case 'march':
				case 'mar':
					$retVal=03;
				break;
				case 'april':
				case 'apr':
					$retVal=04;
				break;
				case 'may':
				case 'may':
					$retVal=05;
				break;
				case 'june':
				case 'jun':
					$retVal=06;
				break;
				case 'july':
				case 'jul':
					$retVal=07;
				break;
				case 'august':
				case 'aug':
					$retVal=08;
				break;
				case 'september':
				case 'sep':
					$retVal=09;
				break;
				case 'october':
				case 'oct':
					$retVal=10;
				break;
				case 'november':
				case 'nov':
					$retVal=11;
				break;
				case 'december':
				case 'dec':
					$retVal=12;
				break;
			}
		}
		return $retVal;
	 }

	public static function showfiledstars($val="",$baseurl){	   
	   $return = "";
	   switch($val)
	   {
		case 0;
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		break;
		case 1;
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		break;
		case 2;
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		break;
		case 3;
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		break;
		case 4;
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_off.png'/>";
		break;
		case 5;
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		$return .= "<img src='".$baseurl."/images/star_on.png'/>";
		break;
	   }
	  return $return;
	
	}
	public static function showtotalrating($tval="",$tuser){
	 $ret = 0;
	 if($tval ==1){
	 $ret = 1;
	 }
	 if(isset($tval) && $tval!="" && isset($tuser) && $tuser!="")
	 {
		$ret=round($tval/$tuser);
	}
	 return $ret;
    }

	/**
	 * 
	 * function return distance (miles,km,m) based on latitude longitude
	 * 
	 */
	public static function getDistance($lat1,$lon1,$lat2,$lon2,$u='1') 
	{
		$u=strtolower($u);
		if ($u == 'k') { $u=1.609344; } // kilometers
		elseif ($u == 'n') { $u=0.8684; } // nautical miles
		elseif ($u == 'm') { $u=1; } // statute miles (default)
		$d=sin(deg2rad($lat1))*sin(deg2rad($lat2))+cos(deg2rad($lat1))*cos(deg2rad($lat2))*cos(deg2rad($lon1-$lon2));
		$d=rad2deg(acos($d));
		$d=$d*60*1.1515;
		$d=($d*$u); // apply unit
		$d=round($d); // optional
		return $d;
	}
	
	/**
	*
	*
	**/
	public static function getNumDaysList()
	{
		$list=array();
		for($i=1;$i<=31;$i++)
		{
			$d=($i<10)?'0'.$i:$i;
			$list[$d]=$d;
		}
		return $list;
	}
	/**
	*
	*
	**/
	public static function getNumMonthList()
	{
		$list=array(
					'01'=>'January',
					'02'=>'February',
					'03'=>'March',
					'04'=>'April',
					'05'=>'May',
					'06'=>'June',
					'07'=>'July',
					'08'=>'August',
					'09'=>'September',
					'10'=>'October',
					'11'=>'November',
					'12'=>'December'
					);
		return $list;
	}
	/**
	*
	*
	**/
	public static function getNumYearList($pStartYear=1970)
	{
		$list=array();
		for($i=$pStartYear;$i<date('Y');$i++)
		{
			$list[$i]=$i;
		}
		return $list;
	}
	
	/**
	*
	*	function to validate email address
	**/
	public static function isValidEmail($pEmail)
	{
		return preg_match("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^",$pEmail);
	}
	
	public static function getRandomNumberPassword($password_length=8)
	{   
		$retVal = '';
		for ($i = 0; $i < $password_length; $i++) {
			$retVal.= substr(mt_rand(32, 126),0,1);
		}
	return $retVal;
	}
	
	public static function getRandomStringPassword($length=8, $strength=0) 
	{
		$vowels = 'aeuy';
		$consonants = 'bdghjmnpqrstvz';
		if ($strength & 1) {
			$consonants .= 'BDGHJLMNPQRSTVWXZ';
		}
		if ($strength & 2) {
			$vowels .= "AEUY";
		}
		if ($strength & 4) {
			$consonants .= '23456789';
		}
		if ($strength & 8) {
			$consonants .= '@#$%';
		}
	 
		$password = '';
		$alt = time() % 2;
		for ($i = 0; $i < $length; $i++) {
			if ($alt == 1) {
				$password .= $consonants[(rand() % strlen($consonants))];
				$alt = 0;
			} else {
				$password .= $vowels[(rand() % strlen($vowels))];
				$alt = 1;
			}
		}
		return $password;
	}
	
 public static function is_isbn_13_valid($n)
	{
		$check = 0;
		for ($i = 0; $i < 13; $i+=2) $check += substr($n, $i, 1);
		for ($i = 1; $i < 12; $i+=2) $check += 3 * substr($n, $i, 1);
		return $check % 10 == 0;
	}
	
	public static function is_issn_valid($ISSN) 
	{
      // strip formatting
      $ISSN = preg_replace( '{[^0-9X]}', '', $ISSN );
      // get length
      $length = strlen( $ISSN );
      // get checksum
      $checksum = ( $ISSN[( $length - 1 )] === 'X' ) ? 10 : intval( $ISSN[( $length - 1 )] );
      // calculate checksum
      if( $length === 8 ) {
        $sum = NULL;
        for( $i = 1; $i < $length; $i++ )
          $sum+= ( 8 - ( $i - 1 ) ) * $ISSN[( $i - 1 )];
        $sum = 11 - $sum % 11;
        return $sum === $checksum;
      }
      return false;
    } 
	
	// going to perform isbn-10 check
   function checkIsbn($isbn)
{
   // going to perform isbn-10 check
   if (strlen($isbn) == 10)
   {
      $subTotal = 0;
      $mpBase = 10;
      for ($x=0; $x<=8; $x++)
      {
         $mp = $mpBase - $x;
         $subTotal += ($mp * $isbn{$x});
      }
      $rest = $subTotal % 11;
      $checkDigit = $isbn{9};
      if (strtolower($checkDigit) == "x")
         $checkDigit = 10;
      return $checkDigit == (11 - $rest);
   }
   // going to perform isbn-13 check
   elseif (strlen($isbn) == 13)
   {
      $subTotal = 0;
      for ($x=0; $x<=11; $x++)
      {
         $mp = ($x + 1) % 2 == 0 ? 3 : 1;
         $subTotal += $mp * $isbn{$x};
      }
      $rest = $subTotal % 10;
      $checkDigit = $isbn{12};
      if (strtolower($checkDigit) == "x")
         $checkDigit = 10;
      return $checkDigit == (10 - $rest);      
   }
   else
   {
      return False;
   }
} 
  	
	public static function processRequest($url, $params)
	{
		
		if(!is_array($params))
		{
		  return false;
		}
		$post_params = "";
		foreach($params as $key => $val) 
		{
			$post_params .= $post_params?"&":"";
			$post_params .= $key."=".$val;
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 0);
		curl_setopt($ch, CURLOPT_HEADER, false); // 'true', for developer testing purpose
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
		
		
		$data = curl_exec($ch);
		
		if(curl_errno($ch))
		{
		print curl_error($ch);
		}
		else
		{
		curl_close($ch);
		}
		
		return $data;
	}
	
	public static function generateApiKey($length=16)
	{
		$key = "";
		for ($i=1;$i<=$length;$i++) 
		{
		  // Alphabetical range
		  $alph_from = 65;
		  $alph_to = 90;

		  // Numeric
		  $num_from = 48;
		  $num_to = 57;

		  // Add a random num/alpha character
		  $chr = rand(0,1)?(chr(rand($alph_from,$alph_to))):(chr(rand($num_from,$num_to)));
		  if (rand(0,1)) $chr = strtolower($chr);
		  $key.=$chr;
		}
		//return $key;
		return MD5($key);
	}
	
	
	

//ProMjhyu0may786Ak
  //$con=mysqli_connect("mysql51-022.wc1.ord1.stabletransit.com","424985_evmiusr","EvenDo786Ak","424985_evnmidb");  
 // print_r();
 
    //Sending Push Notification for anroid
   function send_push_notification($registatoin_ids, $message) {
           
 
        // Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';
 
        $fields = array(
            'registration_ids' => $registatoin_ids,
            'data' => $message,
        );
 
        $headers = array(
            'Authorization: key=' . GOOGLE_API_KEY,
            'Content-Type: application/json'
        );
        //print_r($headers);
        // Open connection
        $ch = curl_init();
 
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
 
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
		 
 	
        // Close connection
        curl_close($ch);
       echo $result;
    }

 
  //Sending Push Notification for IOS
   function send_push_notification_ios($device_token, $email, $userid, $msg ) 
   {
		$deviceToken =  $device_token;

		//Put your private key's passphrase here:
		//$passphrase = 'pushchat';
		$passphrase = 'magnon';

		//Put your alert message here:
		$message = $msg;

		////////////////////////////////////////////////////////////////////////////////

		$ctx = stream_context_create();
		stream_context_set_option($ctx, 'ssl', 'local_cert', 'eVendorPush.pem');
		stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

		//Open a connection to the APNS server
		$fp = stream_socket_client(	'ssl://gateway.sandbox.push.apple.com:2195', $err,$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

		if (!$fp)
		exit("Failed to connect: $err $errstr" . PHP_EOL);

		//echo 'Connected to APNS' . PHP_EOL;

		//Create the payload body
		$body['aps'] = array(
		'alert' => $message,
		'sound' => 'default'
		);
		$body['email'] = $email;
		$body['userID'] = $userid;



		//Encode the payload as JSON
		$payload = json_encode($body);

		// Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

		//Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));

		//if (!$result)
		//echo 'Message not delivered' . PHP_EOL;
		//else
		//echo 'Message successfully delivered' . PHP_EOL;

		//Close the connection to the server
		fclose($fp);
		
		echo $result;
    }

 

	
	
}
?>