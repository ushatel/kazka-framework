<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Parts.Users.php");

/**
 * class Session
 *
 * Головний клас Сесії 
 *
 * The main session class
 * 
 * @package Common.pkg
 */ 
 
class Session
{ 
	private static $mySession = NULL;
	
	private static $Language = "ua";
	
	public $Session = "";
	
	public static $mainSessionVariable = "";
	
	public static $mainSID = "";
	
	/** 
	 * 0 - системний пользователь. Для одержання авторизованого користувача, використовуйте Session::GetUserId()
	 */
	public $userId = 0;
	public $userLogin = "system";
	
	/**
	* Індікатор AJAX запиту 
	*
	* Indicates weather or not request is AJAX type
	*/
	public static $IsAJAX = false;

	/**
	* Головний конструктор
	*
	* Main constructor
	*/
	public function __construct() 
	{
		if(!self::$mySession) 
		{
			if (isset($_POST["PHPSESSID"])) 
			{
				session_id($_POST["PHPSESSID"]);
			} 
			elseif (isset($_GET["PHPSESSID"])) 
			{
				session_id($_GET["PHPSESSID"]);
			}
		
			session_start(); 

			$this->Session = $_SESSION;
			self::$mySession = $this;
			
			if(in_array($_SESSION["LANG"], array("en", "ua", "ru")))
			{
				self::$Language = $_SESSION["LANG"];
			}
			
		}
	}
	
	
	public function SessionInit() 
	{
		/* THIS PROTECTION CLOSES THE MOST XSS */
		$myTime = microtime(true);	
		$mySessionVar = Security::CommonHash($myTime+"_&#65533;&#65533;&#65533;&#65533;_&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;_&#65533;&#65533;&#65533;&#65533;&#65533;".$_SERVER['REMOTE_ADDR']); // $_SERVER['REQUEST_URI'];
		
		$_SESSION['myHiddenVar'] = $mySessionVar;
		$_SESSION['myMicrotime'] = $myTime;
		
		Session::$mainSessionVariable = $mySessionVar;
		
		self::UserInit();
	}
	
	public static function UserInit()
	{
		$SID = "";
		//!!! If Session is abandoned, system then try to setup new with the SID passed with the cookie
		
		if((Session::GetUserId() < 1) && ($SID = self::GetCookieSID()) != "" ) // COOKIE COULD BE SET OFF
		{
			$user = new Users();
			
			$user->login = "";
			$user->SID = $SID;
			
			$user->LoadUserBySID($SID);
			
			self::SetupSessionCookie($user->SID);
			self::SetUserId($user->usersId);
			self::SetUserLogin($user->login);
		}
		else 
		{
			//self::LogOut();
		}
	}
	
	public static function GetLang()
	{
		return self::$Language;
	}
	
	public static function SetLang($lang)
	{
		$_SESSION["LANG"] = $lang;
		self::$Language = $lang;
	}
	
	public static function IncErrorCount()
	{
		if(!isset($_SESSION['myErrCount']))
		{
			$_SESSION['myErrCount'] = 0;
		}
		else {
			$_SESSION['myErrCount'] = (int)$_SESSION['myErrCount'] + 1;
		}
		
		return $_SESSION['myErrCount'];
	}
	
	public static function ClearErrorCount()
	{
		$_SESSION['myErrCount'] = 0;
		return $_SESSION['myErrCount'];
	}
	
	public static function SetUserId($userId)
	{
		return (int)$_SESSION['myUserId'] = $userId;
	}
	
	public static function SetUserLogin($login)
	{
		return $_SESSION['myUserLogin'] = $login;
	}
	
	public static function GetUserLogin()
	{
		return $_SESSION['myUserLogin'];
	}
	
	public static function GetUserId()
	{
		return isset($_SESSION['myUserId']) ? $_SESSION['myUserId'] : 0;
	}
	
	public static function SetCompaniesId($companiesId)
	{
		$_SESSION['myCompaniesId'] = $companiesId;
	}
	
	public static function GetCompaniesId()
	{
		return (float)$_SESSION['myCompaniesId'];
	}
	
	public static function SetProjectsId($projectsId)
	{
		$_SESSION['myProjectsId'] = $projectsId;
	}
	
	public static function GetProjectsId()
	{
		return (float)$_SESSION['myProjectsId'];
	}
	
	public static function IsSuperAdmin()
	{
	///!!! FOR THE DEBUG MODE !!!
		$result = false;
		
		if(self::GetUserId() == 2)
		{
			$result = true;
		}
		
		return $result;
	}
	
	/**
 	 * Очищення усіх змінних сессії
	 *
	 * Cleanup all the session vars
	 */
	public function ClearSessionVars() 
	{
		// Clear my vars
		/*
		$_SESSION['myHiddenVar'] = NULL;
		$_SESSION['myMicrotime'] = NULL;
		$_SESSION['myErrCount'] = 0;
		
		$_SESSION['myPrevURI'] = 0;
		
		$_SESSION['myUserId'] = 0; 			 // System user has 0 code
		$_SESSION['myUserLogin'] = "system";
		*/
		
		self::LogOut();
	}
	
	/**
	 * Виконання аутентифікації користувача
	 */
	public static function Authenticate($login, $userId)
	{
		self::SetUserId($userId);
		self::SetUserLogin($login);
		
		$svar = Security::CreateSessionVar($login);

		$user = new Users();
		
		$user->login = $login;
		$user->SID = $svar;

		// !!! Мабудь, цей запит зайвий! Цю операцію можна було б виконати коли вводили пароль
		$user->SetUsersSIDByLogin();

		self::SetupSessionCookie($svar);		
		return $svar;
	}
	
	public static function SetupSessionCookie($SID)
	{
		// !!! NEED TO CHECK WHY DONT WORK THE DOMAIN RELATED COOKIE
		setcookie("SID", $SID, time(true) + 35 * 3600/*, "/", ".".Security::domain*/);
	}
	
	public static function LogOut()
	{
		$user = new Users();
		
		$user->login = self::GetUserLogin();
		self::$mainSID = $user->SID = "";
		
		$user->SetUsersSIDByLogin();
		
		session_unset();
		session_destroy();
		$_SESSION = array();
		
		@setcookie("SID", "", time(true) - 3600);
	}
	
	public static function GetCookieSID()
	{
		$result = "";
		if(isset($_COOKIE['SID']))
		{
			$result = self::$mainSID = Security::StripPostVar($_COOKIE['SID']);
		}
		
		return $result;
	}
}

?>