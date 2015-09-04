<?php
//echo ">>>>".$_SERVER['DOCUMENT_ROOT'];
//Put your device token here (without spaces):
//$deviceToken = '0f744707bebcf74f9b7c25d48e3358945f6aa01da5ddb387462c7eaf61bbad78';
//$deviceToken =   'e257f5b4077c2d260f9bbc8df1b36538f321ccd3f84b41e8bc699154e9aaac54';
$deviceToken =   '4b3b9b1b8aa4a68022f61424f00038d50b40cf3e94b16dc4f1b01b2b90e38409';

//Put your private key's passphrase here:
//$passphrase = 'pushchat';
$passphrase = 'magnon';

//Put your alert message here:
$message = 'Welcome to evendor';

////////////////////////////////////////////////////////////////////////////////

$ctx = stream_context_create();
stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

//Open a connection to the APNS server
$fp = stream_socket_client(	'ssl://gateway.sandbox.push.apple.com:2195', $err,$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

if (!$fp)
	exit("Failed to connect: $err $errstr" . PHP_EOL);

echo 'Connected to APNS' . PHP_EOL;

//Create the payload body
$body['aps'] = array(
	'alert' => $message,
	'sound' => 'default'
	);
$body['email'] = 'sksmagnon@gmail.com';
$body['userID'] = '81';



//Encode the payload as JSON
 $payload = json_encode($body);

// Build the binary notification
$msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

//Send it to the server
$result = fwrite($fp, $msg, strlen($msg));

if (!$result)
	echo 'Message not delivered' . PHP_EOL;
else
	echo 'Message successfully delivered' . PHP_EOL;

//Close the connection to the server
fclose($fp);