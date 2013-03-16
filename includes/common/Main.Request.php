<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


/**
 * class Request
 *
 * Головний клас запиту.
 *
 * The main request class
 * 
 * @package Common.pkg
 */


class Request 
{
	private static $request = NULL;
	
	public $StartTime = NULL;
	public $EndTime = NULL;
	public $Completed = false;
	
	public static $module = "Main";
	public static $page = "empty";
	public static $identifier = "empty";  // max 50 chars
	public static $browser = "empty";
	public static $ip = "";
	public static $agent = "";
	
	public static $url = "";
	
	public $ServerRoot = "";
	
	public $langVar = 0;
	
	private $isValidPost = false;
	private $isPost = false;

	/**
	 * Повертає першу сторінку для вказанного модуля
	 */
	private $__SCode = "empty";
	
	/**
	* Описує чи є запит AJAX типу
	*
	* Indicates weather or not request is AJAX type
	*/
	public static $IsAJAX = false;
		
	/**
	 * Встановлює флаг валідного запиту - true, якщо запит було сгенеровано із цього ж серверу
	 * 
	 * Indicates if the post was valid
	 */
	public static function SetIsValidPost($isValidPost) 
	{
		$rq = self::$request;
		
		$rq->isValidPost = $isValidPost;
	}
	
	/**
	 * Вертає значення флагу валідації запиту
	 * 
	 * Indicates if the post was valid
	 */
	public static function GetIsValidPost() 
	{
		$rq = self::$request;
		
		return $rq->isValidPost;
	}

	/**
	 * Вертає значення чи був запит типу POST чи GET
	 */
	public static function IsPostType()
	{
		$rq = self::$request;

		return $rq->isPost;
	}
	
	/**
	 * Checks if the posted data is from the same server/session and inits the flag of it
	 */
	public function InitIsValidPost() 
	{		
		$sVar = (isset($_POST['__SVar']) ? $_POST['__SVar'] : ( isset($_GET['__SVar']) ? $_GET['__SVar'] : "" ));

		$this->isValidPost = ($sVar == $_SESSION['myHiddenVar']);

		Session::SessionInit($mySessionVar);

		return $this->isValidPost;
	}
	
	
	private function InitSCode() 
	{ 
		// __SCode to be used in handler
		if (isset($_POST['__SCode']))
		{
			$this->__SCode = Security::StripPostVar($_POST['__SCode']); 
			$this->isPost = true;
		}
		elseif (isset($_GET['__SCode']))
		{
			$this->__SCode = Security::StripPostVar($_GET['__SCode']); 
			$this->isPost = false;
		}
	}
	
	public static function GetSCode()
	{
		$rq = self::$request;

		return $rq->__SCode;
	}
	
	public static function GetRoot()
	{
		$rq = self::$request;
		
		return $rq->ServerRoot;
	}
	
	private function ValidateBrowser($string)
	{
		if(preg_match("/Firefox\//", $string))
		{
			self::$browser = Enumerator::$browser['fox'];
		}
		elseif(preg_match("/Chrome/", $string))
		{
			self::$browser = Enumerator::$browser['chr'];
		}
		elseif(preg_match("/Opera/", $string))
		{
			self::$browser = Enumerator::$browser['o'];
		}
		elseif(preg_match("/MSIE/", $string))
		{
			self::$browser = Enumerator::$browser['ia'];
		}
		
		return self::$browser;
	}

	/**
	*
	* Головний конструктор 
	*
	* Main constructor
	*/
	public function __construct() 
	{
		if(!Session::IsSuperAdmin())
			ob_start(); // Buffering everything and flush just what we need!

		if(!self::$request) 
		{
			// Main request validation!!
			$this->ExtractRequestPathVars();

			$this->ValidateBrowser($_SERVER['HTTP_USER_AGENT']);

			$this->ServerRoot = "http://".$_SERVER['SERVER_NAME'];
			$this->StartTime = microtime(true);//$_SERVER['REQUEST_TIME'];
			$this->InitSCode();

			$this->InitIsValidPost();

			self::$IsAJAX = ($_POST['IS_AJAX'] == 'TRUE');
			
			self::$url = $this->ServerRoot.$_SERVER['REQUEST_URI'];
			self::$ip = $_SERVER['REMOTE_ADDR'];
			self::$agent = $_SERVER['HTTP_USER_AGENT'];
			
			self::$request = $this;			
		}
	}
	
	/**
	 * 
	 */
	private function ExtractRequestPathVars ()
	{
		if(isset($_GET['module']) && strlen($_GET['module']) < 15)
		{
			///!!!! MOST IMPORTANT VALIDATION !!!! LATER THIS DATA USED IN EVAL!!!
			$module = Security::StripPostVar($_GET['module']);
			$page = preg_replace("/\//", "", substr(Security::StripPostVar($_GET['page']), 0, 75));
 			self::$page = Security::PageIsAllowed($module,  $page) ;
			self::$identifier = preg_replace("/\//", "", substr(Security::StripPostVar($_GET['identifier']), 0, 75) );
			
			if(self::$page != "") 
			{
				self::$module = ucfirst(strtolower(Security::StripPostVar($_GET['module'])));
			}
			elseif((strlen($page) > 0 && strlen(self::$identifier) == 0) || self::$page != "")
			{
				try 
				{
					self::$identifier = Security::DecodeUrlData($page);
				}
				catch(Exception $ex)
				{
					self::$identifier = $page;
				}

				if(strlen(self::$identifier) == 0)
				{
					self::$identifier = $page;
				}

				self::$module = ucfirst(strtolower(Security::StripPostVar($_GET['module'])));
				self::$page = Security::PageIsAllowed($module);
			}
			else // Draw Main page
			{
				trigger_error(Logger::LOG_EVENT_CONST." Wrong page requested module=".Security::StripPostVar($_GET['module'])." and page=".preg_replace("/\//", "",Security::StripPostVar($_GET['page'])), E_USER_WARNING);
	
				self::$module = "Main";
				self::$page = "empty";
			}
		}
	}
	
	/**
	*
	* Операції по завершенню обробки запиту
	*
	* Finalize the request
	*/
	public function EndRequest() 
	{
		$this->EndTime = microtime(true);
		
		$this->Completed = true;
	}
	
	/**
	*
	* Визначення часу обробки запиту 
	*
	* Calculate the request time 
	*/
	public function ComputeRequestTime() 
	{
		$rq = self::$request;
	
		//Finalize the request if request was not completed before
		if(!$rq->Completed) 
		{
			$rq->EndRequest();
		}

		return (float)($rq->EndTime - $rq->StartTime);
	}
	
}
?>