<?php
ini_set('display_errors','On');
error_reporting(E_ALL);

$xml = '<?xml version="1.0" encoding="UTF-8"?>
	<TKKPG>
		<Request>
			<Operation>CreateOrder</Operation>
			<Language>EN</Language>
			<Order>
				<Merchant>EVENDOR</Merchant>
				<Amount>5000</Amount>
				<Currency>566</Currency>
				<Description>Payment for test</Description>
				<ApproveURL>http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/paymenttest/index.php</ApproveURL>
				<CancelURL>http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/paymenttest/index.php</CancelURL>
				<DeclineURL>http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/paymenttest/index.php</DeclineURL>
				<AddParams>
					<email>mohan.pal@magnoninternational.com</email>
					<phone>9811677543</phone>
				</AddParams>
			</Order>
		</Request>
	</TKKPG>';

//include('cert/myshop.pem');
$url = "https://196.46.20.36:5443/Exec"

$ch = curl_init();

curl_setopt( $ch, CURLOPT_URL, $url );

curl_setopt( $ch, CURLOPT_VERBOSE,1);

curl_setopt( $ch, CURLOPT_RETURNTRANSFER,1);

curl_setopt( $ch, CURLOPT_CONNECTTIMEOUT, 0 );

curl_setopt( $ch, CURLOPT_TIMEOUT,5000);

curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 0 );

curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER,'1' );

curl_setopt( $ch, CURLOPT_CAINFO,getcwd().'/cert/CAcert.crt');

curl_setopt( $ch, CURLOPT_SSLCERT,getcwd().'/cert/myshop.pem' );

curl_setopt( $ch, CURLOPT_SSLKEY,getcwd().'/cert/myshop.key' );

curl_setopt( $ch, CURLOPT_HTTPHEADER,array('Content-type:text/xml') );

curl_setopt( $ch, CURLOPT_POSTFIELDS,"http://miprojects.com.php53-16.ord1-1.websitetestlink.com/projects/paymenttest/pay.xml");
echo $response = curl_exec($ch);
//if($response = curl_exec($ch))
//{

    echo "hhhh";
    echo "<pre>";
    print_r($response);
    exit;
//}

################


?>
