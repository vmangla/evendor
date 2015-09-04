<?php
require 'FBConnect.php';
?>
<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>php-sdk</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h1 a {
        text-decoration: none;
        color: #3b5998;
      }
      h1 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <h1>php-sdk</h1>
<?php
$objFBConnect=new FBConnect();
$objFBConnect->call();
?>
    <?php if ($objFBConnect->_fbUser): ?>
      <a href="<?php echo $objFBConnect->_fbLogoutUrl; ?>">Logout</a>
	  <br/>

    <?php else: ?>
      <div>
        Login using OAuth 2.0 handled by the PHP SDK:
        <a href="<?php echo $objFBConnect->_fbLoginUrl; ?>">Login with Facebook</a>
      </div>
    <?php endif ?>

    <h3>PHP Session</h3>
    <pre><?php if(isset($_SESSION)){print_r($_SESSION);} ?></pre>

     <?php if ($objFBConnect->_fbUser): ?>
      <h3>You</h3>
      <img src="https://graph.facebook.com/<?php echo $objFBConnect->_fbUser; ?>/picture">

      <h3>Your User Object (/me)</h3>
      <pre><?php print_r($objFBConnect->_fbUserProfile); ?></pre>
    <?php else: ?>
      <strong><em>You are not Connected.</em></strong>
    <?php endif ?>
  </body>
</html>
