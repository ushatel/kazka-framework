<?php
	if(preg_match("/includes/i",$_SERVER['PHP_SELF']))
		die("You don't have permissions to access this dir!");


/**
 * class Response
 *
 * Головний клас Відповіді
 *
 * The main response class
 * 
 * @package Common.pkg
 */

class Response 
{
	private $parsedHtml = "";
	
	private static $response = NULL;
	
	private $responseType = HTML;  // HTML = the simple way to output the text
								   // AJAXJSON = the ajax json output
								   // FILE = the simple file output with the $this->contentType

	private $charset = "utf-8";
	private $contentType = "text/html";
	private $contentLength = 0;
	private $contentLanguage = "en";
	
	private $isFile = true;
	
	private $last_modified = 0;
	private $expires = 0;	
	
	/**
	*
	* Головний конструктор
	*
	* Main constructor
	*/
	public function __construct() 
	{
		$response = self::$response;
		if($response === NULL)
		{
			$this->last_modified = time(); 
			$this->expires = mktime(0,0,0,0,1,2010);
			
			self::$response = $response = $this;
		}
		
		return $response;
	}
	
	public static function SetContentType($contentType = "text/html")
	{
		$response = self::$response;

		if($response)
		{
			$response->contentType = Enumerator::ContentTypes($contentType);
		}
		
		return $response->contentType;		
	}
	
	private function SendCommonHeaders()
	{
		//headers
		if(Session::IsSuperAdmin())
		{
		    ob_clean();
		}

		header("X-Powered-By: Kazka Framework",false);

	  	header("Pragma: no-cache");
		header("Expires: " . gmdate("D, d M Y H:i:s", self::$response->expires) . " GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s", self::$response->last_modified) . " GMT"); // For static images should be UPDATED_TIME value

		header("Cache-Control: no-cache");
	    header("Cache-Control: must-revalidate, post-check=0, pre-check=0, max-age=0", false);
	    header("Cache-Control: private", false); // JUST FOR THE SINGLE USER. WE DON'T CACHE AT ALL
	    header("Content-type: ".self::$response->contentType);
	    header("Content-Length: ".self::$response->contentLength);

	}

	/**
	 * Загальні заголовки (http://www.faqs.org/rfcs/rfc2616, http://www.w3.org/Protocols/rfc2616/rfc2616.html)
	 */
	private function SendHeaders() 
	{
		$response = self::$response;
		$response->SendCommonHeaders();
		
	    switch($response->contentType)
	    {
	    	case Enumerator::ContentTypes(HTML):
	    		
	    		header("Content-Language: ".$response->contentLanguage );
   			    header("Content-type: ".$response->contentType."; charset=".$response->charset);
	    		
	    	break;
	    
	    	case Enumerator::ContentTypes(JPEG):
	    	case Enumerator::ContentTypes(GIF):
	    	case Enumerator::ContentTypes(PNG):

	    		header("Cache-Control: max-age=333000", false); // seconds

	    	break;
	    }


		if(Session::IsSuperAdmin())
		{
		    flush();
	   		ob_end_flush();	
		}
		
		// Attachment
/*		header("Content-Disposition: attachment; filename=" . urlencode($file));   
		header("Content-Type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Type: application/download");
		header("Content-Description: File Transfer");            
		header("Content-Length: " . filesize($file));
		
		If-Modified-Since	Wed, 16 Sep 2009 21:14:20 GMT
		If-None-Match	"98-473b861dea300"
		Cache-Control: max-age=2592000

		flush(); // this doesn't really matter.

		$fp = fopen($file, "r");
		while (!feof($fp))
		{
		    echo fread($fp, 65536);
		    flush(); // this is essential for large downloads
		} 
		fclose($fp);
*/

	}
	
	private static function SendAjaxHeaders()
	{
		self::$response->contentType = "application/json";
	
		self::$response->SendCommonHeaders();

		// AJAX headers		
		//header("Content-type: application/json"); // JSON Header should be passed by client-side framework

		if(Session::IsSuperAdmin())
		{
	    	flush();
	   		ob_end_flush();	
		}
	}	

	/**
	*
	* Відсилає сгенерований текст до браузера
	*
	* Send html response to the browser
	*/
	public function SendResponseToClient($html) 
	{		
		$response = $html[0];

		if(Request::$IsAJAX)
		{
			$responseObj = NULL;
			
			$clientVar = (float)substr(StaticDatabase::CleanupTheField($_POST['__ClientVar']), 0, 50);

			$clientVar = (double)log((float)$clientVar);

			$responseObj['clientVar'] = (string)$clientVar;
			$responseObj['SVar'] = Session::$mainSessionVariable;
			$responseObj['text'] = (string)$html[0];
			$responseObj['jsonObject'] = $html[2];
			$responseObj['scriptsText'] = (string)$html[1];
			
			$response = json_encode($responseObj);
			
			$resp = self::$response;
			$resp->contentLength = (float)strlen($response);

			$resp->SendAjaxHeaders();
		}
		else
		{
			$resp = self::$response;
			$resp->contentLength = (float)strlen($response);

			$resp->SendHeaders();
		}

		echo $response;
		flush();
	}

}

?>