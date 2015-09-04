<?php
class Admin_Model_DbTable_Countries extends Zend_Db_Table
{
	 protected $_name=TBL_COUNTRY;
	 protected $_continent=TBL_CONTINENT;
	 protected $_currency="pclive_currency";
	 
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
	 
	public function isNameExist($pWhere)
	{
		$retVal=false;
		if(isset($pWhere) && $pWhere!="")
		{
			$result=$this->fetchRow($pWhere);
			if($result && $result!=null)
			{
				$retVal=true;
			}
		}
		return $retVal;
	}
	
	public function getCurrencyInfo($countryid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_name.' where id="'.$countryid.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		
		//echo ">>>>>".$retVal['currency_id'];
		
		$retVal1=array();  
		$select1='SELECT * from '.$this->_currency.' where currency_id="'.$retVal['currency_id'].'"';
		$retVal1=$this->getAdapter()->fetchRow($select1); 
		
		return $retVal1['currency_sign'];
		//return $retVal;
	}
	function currencyconverter($from_Currency,$to_Currency,$amount)
	{
		
		if($from_Currency !='NGN' && strtolower($from_Currency)!='naira')
		{
		 $url="http://www.google.com/finance/converter?a=$amount&from=$from_Currency&to=$to_Currency";		 
		 $ch = curl_init();
		 $timeout = 0;
		 curl_setopt ($ch, CURLOPT_URL, $url);
		 curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
		 curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
		 curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
		 curl_setopt ($ch, CURLOPT_TRANSFERTEXT, TRUE);
		 $rawdata = curl_exec($ch);
		 curl_close($ch);			  
		 $data = strip_tags($rawdata);
		 $data = trim($data);
		 $findme1   = '=';
		 $pos1 = strpos($data, $findme1);		 
		 $findme2   = 'NGN';
		 $pos2 = strpos($data, $findme2);
		 
		 if(($pos1 !== false) && ($pos2 !== false))
		 {
		    $subString = substr($data, $pos1, $pos2);
		    $remainingString = explode("=",$subString);			
		  
		    if($remainingString[1]!="" && $remainingString[1]>0)
		    {
		       $remainingString = explode("NGN",$remainingString[1]);
			  return trim($remainingString[0]);
		    }		  
		 }
		}
		else
		{
			return $amount;
		}
	}
	public function getCurrencyCode($countryid)
	{   
		$retVal=array();  
		$select='SELECT * from '.$this->_name.' where id="'.$countryid.'"';
		$retVal=$this->getAdapter()->fetchRow($select); 
		
		//echo ">>>>>".$retVal['currency_id'];
		
		$retVal1=array();  
		$select1='SELECT * from '.$this->_currency.' where currency_id="'.$retVal['currency_id'].'"';
		$retVal1=$this->getAdapter()->fetchRow($select1); 
		
		return $retVal1['currency_name'];
		//return $retVal;
	}
	
}
?>