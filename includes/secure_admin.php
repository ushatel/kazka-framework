<?php
if(preg_match("/includes/i",$_SERVER['PHP_SELF']))
	die("You don't have permissions to access this dir!");
	
/* REQUEST SPEED PROTECTION */
/* IF TWO REQUESTS FROM SINGLE ARE FASTER THAN 500 ms - POSIBLE HACK */
/* Session is blocked on $sessionBlockedValue */
$sessionBlockedValue = 10; //seconds
$sessionBlockMinTime = 0.35; // milli seconds
if( (is_float($_SESSION['myMicrotime']) && ( ((float)microtime(true) - (float)$_SESSION['myMicrotime']) < $sessionBlockMinTime) && 
	((string)$_SESSION['myPrevURI']) == ((string)$_SERVER['REQUEST_URI']) )
		|| ((int)$_SESSION['myErrCount'] > (int)Security::MaxErrorCount) ) 
{
	Session::ClearErrorCount();
	$previousValue = microtime(true) - (float)$_SESSION['myMicrotime'];
	if($previousValue > 0 ) {
		StaticDatabase::SqlConnect();
		SecureLogEvent("MSG: Session is blocked because of fast requests");
		$_SESSION['myMicrotime'] = microtime(true) + $sessionBlockedValue;
	}
	
	die("Requests are too fast. Server is busy. Please, try again in ".(int)(($previousValue >= 0) ? 
		$sessionBlockedValue : $previousValue * (-1) )." seconds.");
}


/* !!!!! VERY BAD EXAMPLE !!!!! SHOULD REWRITE */
	$isValidPost = 0;
	
$secvalue ="";

foreach ($_GET as $secvalue) {
    if ((preg_match("/<[^>]*script*\"?[^>]*>/i", $secvalue)) ||
	(preg_match("/<[^>]*object*\"?[^>]*>/i", $secvalue)) ||
	(preg_match("/<[^>]*iframe*\"?[^>]*>/i", $secvalue)) ||
	(preg_match("/<[^>]*applet*\"?[^>]*>/i", $secvalue)) ||
	(preg_match("/<[^>]*meta*\"?[^>]*>/i", $secvalue)) ||
	(preg_match("/<[^>]*style*\"?[^>]*>/i", $secvalue)) ||
	(preg_match("/<[^>]*form*\"?[^>]*>/i", $secvalue)) ||
	(preg_match("/<[^>]*img*\"?[^>]*>/i", $secvalue)) ||
	(preg_match("/\([^>]*\"?[^)]*\)/i", $secvalue)) ||
	(preg_match("/\"/", $secvalue))) {
	die ("I don't like you...");
    }
}

$secvalue = "";

/*!!!!REM THIS PROTECTION BECAUSE OF THE FUNCTION StripPostVar()
Are the MAGIC_QUOTES OFF?

foreach ($_POST as $secvalue) {
	$secvalue = strip_tags($secvalue);
	$secvalue = htmlspecialchars($secvalue);
	$secvalue = nl2br($secvalue);
	
	//echo strpos($secvalue, "script")." + preg = ".$secvalue;//.preg_match("/<[^>]*script*\"?[^>]*>/i", $secvalue);

    if (!is_array($secvalue) && (preg_match("/<[^>]*script*\"?[^>]*>/i", $secvalue))) {
        Header("Location: index.php");
        die();
    }
}
*/

// Password should be always hashed!!! So we hash the password from the beggining of request!
if(isset($_POST['password_field']))
{
	$_POST['password_field'] = Security::CommonHash(Security::StripPostVar($_POST['password_field']));
}

if(isset($_POST['password_confirm']))
{
	$_POST['password_confirm'] = Security::CommonHash(Security::StripPostVar($_POST['password_confirm']));
}


function StripPostVar($secvalue) 
{
	$secvalue = strip_tags($secvalue);
	$secvalue = htmlspecialchars($secvalue);
	$secvalue = nl2br($secvalue);
	
	return $secvalue;
}
 
function SecureLogEvent($msg) 
{
	$myDate = getdate();
	$query = "insert into `MARTIS_LOG_TBL` ".
			 "(`CREATED`, `HTTP_USER_AGENT`, `HTTP_REFERER`, `HTTP_COOKIE`, `SERVER_ADDR`,".
			 "  `REMOTE_ADDR`, `SCRIPT_FILENAME`, `REQUEST_URI`, `REQUEST_TIME`, `REQUEST_TEXT`) ".
			 " VALUES (".
			 " '".date("Y-m-d H:i:s",mktime($myDate[hours],$myDate[minutes],$myDate[seconds],$myDate[mon],$myDate[mday],$myDate[year]))."', ".
			 " '".mysql_real_escape_string(StripPostVar($_SERVER[HTTP_USER_AGENT])) ."', ".
			 " '".mysql_real_escape_string(StripPostVar($_SERVER[HTTP_REFERER])) ."', ".
			 " '".mysql_real_escape_string(StripPostVar($_SERVER[HTTP_COOKIE])) ."', ".
			 " '".mysql_real_escape_string(StripPostVar($_SERVER[SERVER_ADDR])) ."', ".
			 " '".mysql_real_escape_string(StripPostVar($_SERVER[REMOTE_ADDR])) ."', ".
			 " '".mysql_real_escape_string(StripPostVar($_SERVER[SCRIPT_FILENAME])) ."', ".
			 " '".mysql_real_escape_string(StripPostVar($_SERVER[REQUEST_URI])) ."', ".
			 " '".mysql_real_escape_string(StripPostVar($_SERVER[REQUEST_TIME])) ."', ".
			 " '".mysql_real_escape_string(StripPostVar((strlen($msg) > 0) ? $msg ."\r\n " : '').print_r($_REQUEST, true)) ."') ";

	$result = mysql_query($query);
}

function MyAddSlashes($arr)
{
	foreach($arr as $key => $value)
		if (is_array($value))
			$arr[$key] = MyAddSlashes($value);
		elseif (!get_magic_quotes_gpc())
			$arr[$key] = addslashes($value);
	return $arr;
} 

?>