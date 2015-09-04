<?php
class Admin_Model_DbTable_ModulesMenus extends Zend_Db_Table
{
	 
	 protected $_name=TBL_ADMIN_MODULE_MENUS;
	 protected $_modulename=TBL_ADMIN_MODULES;
	 
	 public function isExist($pId)
	 {
		$retVal=false;
		if(isset($pId) && $pId>0)
		{
			$result=$this->fetchRow('id='.$pId);
			if($result && $result!=null)
			{
				$retVal=true;
			}
		}
		return $retVal;
	 }
	 
	 
	 public function getList()
	 {
	 
		$list=array();

		$query=$this->select();
		$query->from(array('u'=>$this->_name),array('u.*'));	
		$query->where('u.account_type=1 OR u.account_type=2');	
		$query->order('u.id ASC');
		
		$result=$this->fetchAll($query);
		if($result && $result!=null)
		{
			$list=$result;
		}
		
		return $list;
	 }
	 
	public function getModuleMenusList($user_menu_ids=0)
	{   
		$list=array();
		
		$query=$this->select();
		$query->setIntegrityCheck(false);
		
		$query->from(array("menu"=>$this->_name),array("GROUP_CONCAT(menu.id,'%^') as menu_ids, GROUP_CONCAT(menu.menu_name,'%^') as menu_names, GROUP_CONCAT(menu.controller,'%^') as controllers, GROUP_CONCAT(menu.action,'%^') as actions"));
		
		$query->join(array("module"=>$this->_modulename), "menu.module_id = module.id", array('module.id','module.modulename'));
		
		if(!empty($user_menu_ids))
		{
			$query->where('menu.id IN('.$user_menu_ids.')');
		}
		
		$query->order('module.id');
		$query->group('menu.module_id');
		
		$retVal=$this->getAdapter()->FetchAll($query); 
		if(isset($retVal) && $retVal!="")
		{
			$list=$retVal;
		}
		
		if(is_array($list))
		{
			foreach($list as $key=>$array)
			{
				$list[$key]['menu_ids']=explode("%^,",rtrim($array['menu_ids'],'%^'));
				$list[$key]['menu_names']=explode("%^,",rtrim($array['menu_names'],'%^'));
				$list[$key]['controllers']=explode("%^,",rtrim($array['controllers'],'%^'));
				$list[$key]['actions']=explode("%^,",rtrim($array['actions'],'%^'));
				
			}
		
		/*echo"<pre>";
		print_r($list);
		echo"</pre>";
		//exit;
		*/
		
		}
		return $list;
	}
}
?>