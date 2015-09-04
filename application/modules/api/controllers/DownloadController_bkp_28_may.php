<?php
class Api_DownloadController extends Zend_Controller_Action
{
	var $modelApiKey=null;

	public function init()
    {
	   $this->_helper->layout->disableLayout();       
	   $this->_helper->viewRenderer->setNoRender(true);
	}
	  

   /* public function indexAction()
    {
		$db = Zend_Registry::get('db');
       
		$parameters = $this->_request->getParams();
		
		$apicall = $this->_request->getParam("apicall");		
		$bookid = $this->_request->getParam("bookid");
		$apikey = $this->_request->getParam("apikey");
		
		$sql = 'SELECT * FROM pclive_apikeys where apikey="'.$apikey.'"';
		$result = $db->query($sql);
		$record1 = $result->FetchAll();
 
		 
		if($apicall=='')
		{
				echo"{'error':'Api method missing!'}";
		}
		
		else if($bookid=='')
		{
			echo"{'error':'Book id missing!'}";
		}
		else if($apikey=='')
		{
			echo"{'error':'Api key missing!'}";
		}
		else if($apikey!=$record1[0]['apikey'])
		{
			echo"{'error':'Api key not correct!'}";
		}
		else
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
			
		 
						
			$sql = "select * from pclive_products where id='$bookid'";
			$result = $db->query($sql);
			$records = $result->FetchAll();
		 
			$filename = $records[0]["file_name"];
			
			 
			
			$file = '/mnt/target02/343621/424985/www.miprojects.com/web/content/projects/evendor/public/uploads/epubfile/'.$filename;




			if (file_exists($file)) {
		 
			header('Content-Description: File Transfer');
			header('Content-Type: application/epub');
			header('Content-Disposition: attachment; filename='.basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();
			flush();
			readfile($file);
			exit;
			}		
		} 

     }*/
	 
	 
	 public function indexAction() 
	 { 
		$speed = 100;
		
		//First, see if the file exists  
		
		$db = Zend_Registry::get('db');
       
		$parameters = $this->_request->getParams();
		
		$apicall = $this->_request->getParam("apicall");		
		$bookid = $this->_request->getParam("bookid");
		$apikey = $this->_request->getParam("apikey");
		
		$sql = 'SELECT * FROM pclive_apikeys where apikey="'.$apikey.'"';
		$result = $db->query($sql);
		$record1 = $result->FetchAll();
 
		 
		if($apicall=='')
		{
				echo"{'error':'Api method missing!'}";
		}
		
		else if($bookid=='')
		{
			echo"{'error':'Book id missing!'}";
		}
		else if($apikey=='')
		{
			echo"{'error':'Api key missing!'}";
		}
		else if($apikey!=$record1[0]['apikey'])
		{
			echo"{'error':'Api key not correct!'}";
		}
		else
		{
			$this->_helper->layout()->disableLayout();
			$this->_helper->viewRenderer->setNoRender();
		
		 
				$sql = "select * from pclive_products where id='$bookid'";
				$result = $db->query($sql);
				$records = $result->FetchAll();

				$filename = $records[0]["file_name"];

				 
				$file = '/mnt/target02/343621/424985/www.miprojects.com/web/content/projects/evendor/public/uploads/epubfile/'.$filename;
				
				
				if (!is_file($file)) {
					die("<b>404 File not found!</b>");
				}  
				//Gather relevent info about file
				$filename = basename($file);
				$file_extension = strtolower(substr(strrchr($filename,"."),1));
				
				 
				// This will set the Content-Type to the appropriate setting for the file
				switch( $file_extension ) {
					case "exe":
						$ctype="application/octet-stream";
						break;
					case "pdf":
						$ctype="application/pdf";
						break;	
					case "epub":
						$ctype="application/epub";
						break;		
					case "zip":
						$ctype="application/zip";
						break;
					case "mp3":
						$ctype="audio/mpeg";
						break;
					case "mpg":
						$ctype="video/mpeg";
						break;
					case "avi":
						$ctype="video/x-msvideo";
						break;
			 
					//  The following are for extensions that shouldn't be downloaded
					// (sensitive stuff, like php files)
					case "php":
					case "htm":
					case "html":
					case "txt":
						die("<b>Cannot be used for ". $file_extension ." files!</b>");
						break;
					default:
						$ctype="application/force-download";
				}
			 
				//  Begin writing headers
				header("Cache-Control:");
				header("Cache-Control: public");
				header("Content-Type: $ctype");
			 
				$filespaces = str_replace("_", " ", $filename);
				// if your filename contains underscores, replace them with spaces
			 
				$header='Content-Disposition: attachment; filename='.$filespaces;
				header($header);
				header("Accept-Ranges: bytes");
			 
				$size = filesize($file);  
				 
			
				//  check if http_range is sent by browser (or download manager)  
				if(isset($_SERVER['HTTP_RANGE'])) {
					// if yes, download missing part     
			 
					$seek_range = substr($_SERVER['HTTP_RANGE'] , 6);
					$range = explode( '-', $seek_range);
					if($range[0] > 0) { $seek_start = intval($range[0]); }
					if($range[1] > 0) { $seek_end  =  intval($range[1]); }
			 
					header("HTTP/1.1 206 Partial Content");
					header("Content-Length: " . ($seek_end - $seek_start + 1));
					header("Content-Range: bytes $seek_start-$seek_end/$size");
					//open the file
					$fp = fopen("$file","rb");

					//seek to start of missing part  
					fseek($fp,$seek_start);

					//start buffered download
					while(!feof($fp)) {      
					//reset time limit for big files
					set_time_limit(0);      
					print(fread($fp,1024*$speed));
					flush();
					sleep(1);
					}
					fclose($fp);
					exit;
				} else {
					//header("Content-Range: bytes 0-$seek_end/$size");
					//header("Content-Length: $size");
				header('Content-Description: File Transfer');
				header('Content-Type: application/'.$ctype);
				header('Content-Disposition: attachment; filename='.basename($file));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($file));
				ob_clean();
				flush();
				readfile($file);
				exit;
					
				}  
				
	    }
	}
	
}

?>