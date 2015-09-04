<?php
class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

	/**
	 * Initialize the application autoload
	 *
	 * @return Zend_Application_Module_Autoloader
	 */
    protected function _initAutoload()
    {
        $autoloader = new Zend_Application_Module_Autoloader(array('namespace' => '','basePath'  => APPLICATION_PATH.'/modules/default'));
        return $autoloader;
		
		/*$autoloader = Zend_Loader_Autoloader::getInstance();
         $autoloader->suppressNotFoundWarnings(false);

        $moduleLoader = new Zend_Application_Module_Autoloader(
            array('namespace'=>'','basePath'=>APPLICATION_PATH.'/modules/default/'),
             array('namespace' => 'Admin','basePath' => APPLICATION_PATH . '/modules/admin/')
			 );
         return $moduleLoader;*/
    }

    /**
     * Initialize the layout loader
     */
    protected function _initLayoutHelper()
    {
    	$this->bootstrap('frontController');
    	$layout = Zend_Controller_Action_HelperBroker::addHelper(new Talib_Controller_Action_Helper_LayoutLoader());
		
		$doctypeHelper = new Zend_View_Helper_Doctype();
		$doctypeHelper->doctype('HTML5');
    }
	
	function _initViewHelpers() 
	{
		$view = new Zend_View();
		$view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
		
		$view->headMeta()->appendHttpEquiv('X-UA-Compatible',"IE=edge,chrome=1");
		$view->headMeta()->appendHttpEquiv('PRAGMA',"NO-CACHE");
		$view->headMeta()->setCharset('UTF-8');
		
		$view->headMeta()->appendName("viewport", "width=device-width,initial-scale=1.0");
		
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer->setView($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

	}
	
	/************ Old commented on 14 March 2013 *****************************/
	/*
	function _initViewHelpers() 
	{
		$view = new Zend_View();
		$view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
		$viewRenderer->setView($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

	}
	*/
	/************ Old commented on 14 March 2013 *****************************/
	
	protected function _initRoutes() 
	{
		$this->bootstrap('frontcontroller');
		$front = Zend_Controller_Front::getInstance();
		$router = $front->getRouter();
		$myRoutes = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini', 'production');
		$router->addConfig($myRoutes, 'routes');
	}
	
	
	// Navneet Code
	protected function _initRestRoute() 
	{
	    
		// Rest Full Api Setting
		$this->bootstrap('frontController');
		$frontController = Zend_Controller_Front::getInstance();
		$restRoute = new Zend_Rest_Route($frontController, array() , array('api'));
		$frontController->getRouter()->addRoute('rest', $restRoute);
		
		// Database config
		Zend_Registry::set('db',$this->getPluginResource('db')->getDbAdapter());
	
    }
    
	protected function _initNavigation()
	{
		$this->bootstrap('layout');
		$layout = $this->getResource('layout');
		$view = $layout->getView();
		$config = new Zend_Config_Xml(APPLICATION_PATH.'/configs/navigation.xml');
	 
		$navigation = new Zend_Navigation($config);
		$view->navigation($navigation);
	}
}