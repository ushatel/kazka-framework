<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

// Error reporting level & ~E_NOTICE
// error_reporting(E_ALL | E_STRICT ); 
error_reporting(E_ALL & ~E_NOTICE); 

/**
 * The Package collects the classes for the loggin the events
 *
 * @package Common.pkg
 */
 
 /** 
  *  Цей файл необхідний задля вставновлення та налаштування рівня логування повідомлень
  *  йдеться як про системні так і про повідомлення користувача
  */
  
	function MainErrorHandler ($errno, $errstr, $errfile, $errline, $errcontext) 
	{
		if(!(error_reporting() & $errno))
		{
			// we don't need to log errors with the code
			return;
		}

		if(Logger::GetLogLevel() == LOG_HIGH) 
		{		
			echo "<pre>"; debug_print_backtrace(); echo "</pre>";	
		}
		
		$regExpFilter = "/".Logger::LOG_EVENT_CONST."/i";

		if(preg_match($regExpFilter, $errstr)) // User friendly logger of any event
		{
			// open syslog, include the process ID and also send
			// the log to standard error, and use a user defined
			// logging mechanism
			openlog("BuilderAppLog", LOG_PID | LOG_PERROR, LOG_USER);

			// some code
			$access = date("Y/m/d H:i:s");
			
			/*
				Constant 	Description
				LOG_EMERG 	system is unusable
				LOG_ALERT 	action must be taken immediately
				LOG_CRIT 	critical conditions
				LOG_ERR 	error conditions
				LOG_WARNING warning conditions
				LOG_NOTICE 	normal, but significant, condition
				LOG_INFO 	informational message
				LOG_DEBUG 	debug-level message			
			*/
			$myMsg = preg_replace($regExpFilter, "", $errstr);

			$msgToLog = "$access -- $myMsg {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})\r\n".
						" Line ($errline) : $errfile ";
			$additionalInfo = "";

			switch($errno)
			{
				default:
	
				case E_USER_ERROR:

				// URGENT ACTION need immidiate activity 
					if(Logger::GetLogLevel() == LOG_HIGH && !Logger::GetIsSecureCode()) 
					{
						$additionalInfo = "(".print_r($errcontext, true)." - ".print_r(debug_backtrace(), true).")";
					}

					syslog(LOG_ALERT, "ERROR: ".$msgToLog." ".$additionalInfo);					
					
					$urgentMsg = "Bad guys are inda house!<br>\r\n".$msgToLog." ".$additionalInfo;
					error_log($urgentMsg, 1, "sysprog@martis-ua.com");
				break;
					
				case E_USER_WARNING:
				// NOTIFY OF THE NOT GOOD BEHAVIOR WITH THE GUI
									
					if(Logger::GetLogLevel() == LOG_MID || Logger::GetLogLevel() == LOG_HIGH) 
					{
						if(Logger::GetLogLevel() == LOG_HIGH && !Logger::GetIsSecureCode()) 
						{
							$additionalInfo = "(".print_r($errcontext, true)." - ".print_r(debug_backtrace(), true).")";
						}
						syslog(LOG_WARNING, "WARNING: ".$msgToLog. " ".$additionalInfo);
						
					}
				break;
					
				case E_USER_NOTICE:
				// JUST the simple EVENT NOTIFICATION, like the USER is logging in
				
					if(Logger::GetLogLevel() == LOG_HIGH)
						syslog(LOG_INFO, "NOTICE: ".$msgToLog);
				break;
			}

			closelog();
		}
		
		//return true; // Означає, що не буде викликано стандартний обролювач. False - також викличуть стандартний оброблювач
					   // false використовуємо лише для адміністративного режиму
		return true	;
	}
	
//ini_set("display_errors", 1);
/* Change the php.ini error logging settings
ini_set("")
display_errors
display_startup_errors
*/

/// !!! URGENT MAILER
//error_log("Bad guys are inda house", 1, "sysprog@martis-ua.com");

// Template 			E_USER_ERROR E_USER_WARNING E_USER_NOTICE
// trigger_error("< %CUSTOM_APP_EVENT% >", E_USER_ERROR);
// trigger_error(Logger::LOG_EVENT_CONST." Error Text", E_USER_ERROR);

/**
 * class Logger
 *
 * The main class of the Database connector
 * 
 * @package Common.pkg
 */
  class Logger 
  {
  	 /**
  	  * Вказує, де зберігати 
  	  */
  	 private $logSource 	= lFile; // lDatabase, lFile, lSubsystem
  	 
  	 private $logFilePath 	= "";
  	 
  	 const LOG_EVENT_CONST  = "<:-CUSTOM_APP_EVENT-:>";
  	 
   	 const LOG_HIGH = 2;
	 const LOG_MID  = 1;
	 const LOG_LOW  = 0;
  	 
  	 protected static $LOG_LEVEL 	= LOG_MID; 
  	 								// LOG_HIGH - log everything to everywhere
  	 								// LOG_MID  - log errors to everywhere, events just to user log
  	 								// LOG_LOW  - log just errors
	
	public static function GetLogLevel()
	{
		return self::$LOG_LEVEL;
	}
  	 
  	 /**
  	  * Функція додаткового запису повідомлення до необхідного ресурсу
  	  */
  	 public static function WriteEvent() 
  	 {
  	 	switch(self::logSource)
  	 	{
  	 		case lFile:
  	 			break;
  	 			
  	 		case lDatabase:
  	 			
  	 			break;
  	 			
  	 		case lSubsystem:
  	 			break;
  	 			
  	 		default:
  	 			return false;
  	 	}
  	 	
  	 	return true;
  	 }
  	 
  	 public static function GetIsSecureCode()
  	 {
  	 	// !!!! For such events don't use the dumping TO PROTECT THE USERS DATA (PASSWORDS so on)
  	 	$sCode = &Request::GetSCode();
  	 	
  	 	$isSecureCode = ($sCode == sha1("pRocEssTheLeGalReGistRation") || $sCode == sha1("pRocEsSTheLoGinForM"));

  	 	return $isSecureCode;
  	 }

  }

?>