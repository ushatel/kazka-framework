<?php 

$langVar = "en";

include_once("includes/common_init.php");

echo "dsdsfsdfsd лллддлдлдл";

if($_POST['IS_AJAX'] != 'TRUE')
{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml">

<head>

<title>Building</title>

<?php include_once("includes/common_scripts.php"); ?>

</head>

<body>

<div>gf___jj</div>

<? 

if(strlen($loginFormText) > 0) 
{
	echo $loginFormText;
	
	//include_once("includes/google_conversion.php");	
}
else {
	//SessionInit();
	//SecureLogEvent();
	}
?>
</body>
</html>

<?

// END IS AJAX CHECK
 }
 
 echo Request::ComputeRequestTime().", queries: ".StaticDatabase::GetQueryCount()." <br>";
?>