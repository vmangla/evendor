<?php
//session_set_cookie_params(0);
//error_reporting(0);
// Define path to application directory
defined('APPLICATION_PATH') || define('APPLICATION_PATH',realpath(dirname(__FILE__) .'/application'));
// Define application environment
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'production'));
// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR,array(realpath(APPLICATION_PATH.'/../library'),realpath(APPLICATION_PATH . '/../../Library'),get_include_path(),)));

require_once 'external/system_constants.php';
require_once 'external/table_constants.php';
require_once 'external/CommonFunctions.php';
require_once 'external/facebook/FBConnect.php';


/** Zend_Application */
require_once 'Zend/Application.php';  
// Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV,APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap()->run();