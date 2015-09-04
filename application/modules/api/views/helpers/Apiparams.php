<?php
class Zend_View_Helper_Apiparams extends Zend_Controller_Action_Helper_Abstract
{
    public $params;

    public function __construct()
    {
        $request = Zend_Controller_Front::getInstance()->getRequest();
        $this->params = $request->getParams();
    }

    public function apiparams()
    {       
        if(isset($this->params['controller']))
            unset($this->params['controller']);
        if(isset($this->params['action']))
            unset($this->params['action']);
        if (isset($this->params['module']))
            unset($this->params['module']);
        return $this->params;
    }

    public function direct()
    {
        return $this->apiparams();
    }
}
?>