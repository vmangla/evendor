<?php
/**************PATH**********************************/
//define("HTTP_HOST","http://$_SERVER[HTTP_HOST]");
//define("SITE_URL","http://$_SERVER[HTTP_HOST]/OnOfflineNewsstand");

define("SITE_DIR",$_SERVER['DOCUMENT_ROOT']);

define("USER_UPLOAD_DIR","public/uploads/users/");
define("COMPANY_UPLOAD_DIR","public/uploads/companies");
define("EPUB_UPLOAD_DIR","public/uploads/epubfile/");
define("GOOGLE_API_KEY", "AIzaSyB2M1WeqcZzhi0V5TJ1Y8QzIZe95I21gRo");
//define("SVN_URL","http://php.delivery-projects.com:81/OnOfflineNewsstand/");


/**************SITE CONSTANT *****************************/
if($_SERVER['HTTP_HOST']=='localhost')
{
	$dbhost = "localhost";
	$dbusername ="root";
	$dbpassword ="";
	$dbname	= "onoffnewsdb";	
}
else
{
	$dbhost = "mysql51-022.wc1.ord1.stabletransit.com";
	$dbusername ="424985_evmiusr";
	$dbpassword ="EvenDo786Ak";
	$dbname	= "424985_evnmidb";	
}

$dbconn=mysql_connect($dbhost,$dbusername,$dbpassword);
mysql_select_db($dbname,$dbconn);

$constant_query="SELECT * FROM pclive_site_settings";
$constant_result=mysql_query($constant_query);
if(mysql_num_rows($constant_result)>0)
{
	while($fetchConstant = mysql_fetch_assoc($constant_result))
	{
		define($fetchConstant['site_constant'],$fetchConstant['constant_value']);
	}
}

/*define("SMTP_SERVER","mail.magnoninternational.com");
define("SMTP_USERNAME","developers@magnoninternational.com");
define("SMTP_PASSWORD","developers");
define("SMTP_SSL","tls");
define("SMTP_PORT","25");
define("SMTP_AUTH","login");
define("SETFROM","amar.bhanu@magnoninternational.com");
define("SETNAME","Administrator");
*/

/***************EXTRA*********************************/

/*define("ADMIN_EMAIL","navneet.kumar@magnoninternational.com");
define("ADMIN_NAME","Navneet Kumar");
define("ADMIN_PAGING_SIZE", 50);
define("PUBLISHER_PAGING_SIZE",10);
*/
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
?>