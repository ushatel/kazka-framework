<?php 

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

/**
 *
 * Common main includes and initializations at the beggining of the page life-cycle
 */
include_once("includes/common/Main.Logger.php");
set_error_handler("MainErrorHandler"); //???

include_once("includes/common/Main.StaticDatabase.php");
include_once("includes/common/Main.Enumerator.php");

include_once("includes/common/Main.Session.php");
$session = new Session();

include_once("includes/common/Main.Security.php");
include_once("includes/secure_admin.php");

include_once("includes/common/Main.Request.php");
$myRequest = new Request();

include_once("includes/common/Main.Response.php");
$myResponse = new Response();

// Common Database class - Usually the main database. Use the Main.Database class for the Table objects!
StaticDatabase::SqlConnect();


$loginFormText = "";

//Request::InitIsValidPost();
//Request::SetIsValidPost(IsValidPost());
//		trigger_error(Logger::LOG_EVENT_CONST."aaaa", E_USER_ERROR);

//require_once("includes/login_form.php");
?>