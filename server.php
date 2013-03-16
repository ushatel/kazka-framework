<?

/* 
	function myErrorHandler ($errno, $errstr, $errfile, $errline, $errcontext) 
	{
		echo "myError";
		echo "<pre>",debug_print_backtrace(),"</pre>";
		echo "<pre>";
		print_r($errcontext);
		echo "</pre>";
		//echo $errno," ", $errstr, " ", $errfile, " ", $errline;
		
		return true;
	}

	function myExceptionHandler($exception) 
	{
		echo "My exception = ".$exception->getMessage()."";
		echo "<pre>",debug_print_backtrace(),"</pre>";
		trigger_error();
	}	
	
	set_exception_handler(myExceptionHandler);
	set_error_handler(myErrorHandler);
	

	
	//throw new Exception("bad code");
	
	echo "halted before";
	
	$var = NULL;
	echo "__ ".(string)(bool)$var." __ ";
	
$value = 'something from somewhere';

setcookie("TestCookie", $value);
setcookie("TestCookie", $value, time()+3600);  
setcookie("TestCookie", $value, time()+3600, "/~rasmus/", ".build", 1);

// Print an individual cookie
echo $_COOKIE["TestCookie"];
echo $HTTP_COOKIE_VARS["TestCookie"];

// Another way to debug/test is to view all cookies
print_r($_COOKIE);
	

		session_unset();
		session_destroy();
		$_SESSION = array();

require_once("/includes/DatabaseClasses/Db.CommonTable.php");
require_once("/includes/common/Main.SearchOperator.php");
require_once("/includes/common/Main.Enumerator.php");
*/

/*$searchOperator = new SearchOperator();

$index_data = array("object_id" => 2, "object_guid" => "fdsfsd", "entity_id" => Enumerator::Entity('MAT')->id );
print_r($index_data);

$searchOperator->ParseDocument("test text testing testtesttt test testing testing yyyyy rrrrr uuuuuu wwwwww", $index_data);

$word = new Word();
$word->word = "text";
*/
/*
$query = "update `build`.`materials` set `VENDOR_TEXT` = 'OK_GROUP' WHERE `materials_id`=2 ORDER BY `DIVISIBILITY_ID`  LIMIT 1";
StaticDatabase::SqlConnect();
$result = StaticDatabase::SqlStaticQuery($query);
echo $result;
echo (float)mysql_num_rows($result);
*/
//require_once("/includes/common/Lib.Operations.php");

//$html_body = '&#65533;&#65533;&#65533; &#65533;&#65533;&#65533; &#65533;&#65533;&#65533; &#65533;&#65533;&#65533;              
// &#65533;&#65533;&#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533;&#65533;&#65533;';

//echo Operations::Translator($html_body)." fsdfsd";

//$html_body = preg_replace('/\s+/', ' ', $html_body);
//echo preg_replace("/(<\/?)(\w+)([^>]*>)/e", 
//             "'\\1'.MyFunc('\\2').'\\3'", 
//            $html_body);

/*$size = getimagesize();
switch($size['mime'])
{
	case :
		$im = imagecreatefromjpeg("http://www.facebook.com/photo.php?fbid=404729722800&set=a.398080447800.166146.18790602800");	
	break;
	
	case :
		$im = imagecreatefromjpeg("http://bugs.php.net/report.php?bug_type=Documentation+problem&manpage=function.imagecreatefromjpeg%23Parameters");
	break;
}
print_r($size);
*/
/*
include_once("/includes/common/Main.FileFunctions.php");

$ff = new FileFunctions();
$ff->fileName = "http://sphotos.ak.fbcdn.net/hphotos-ak-ash2/hs348.ash2/62882_455058557800_18790602800_5170186_3530992_n.jpg";

$ff->ValidateFile();

echo $ff->fileData;
*/
//	include_once("/includes/common/Main.Logger.php");
//	set_error_handler("MainErrorHandler"); 

//	trigger_error(Logger::LOG_EVENT_CONST." Sets ", E_USER_ERROR);

/*	
	if($_POST['POSTED'] === "YES") 
	{
		echo "fff - ".$_POST['POSTED'];
	}
	
	include_once("/includes/common/Main.FileFunctions.php");
	$ff = new FileFunctions();
	
	$ff->fileName = "D:/investorKit.gif";
	$ff->ValidateFile();

	if($ff->fileSize > 0)
	{
		echo $ff->fileData;
	}
*/
include_once('/includes/common/Main.Session.php');
include_once('/includes/common/Main.StaticDatabase.php');

/*
$query = "select * from `build`.`users_files` limit 1";
StaticDatabase::SqlConnect();
$result = StaticDatabase::SqlStaticQuery($query);
$row = mysql_fetch_array($result);
*/
echo "<pre>";
print_r($_ENV);
print_r($_SERVER);
echo "</pre>";


?>
