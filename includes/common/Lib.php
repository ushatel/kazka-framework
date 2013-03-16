<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	/** 
	 *	Library is the package with the common library functionality
	 *
	 *  @package Library.pkg
	 */

	include_once("includes/common/Tag/Lib.Tag.php");
	include_once("includes/common/Tag/Lib.Anchor.php");
	include_once("includes/common/Tag/Lib.Form.php");
	include_once("includes/common/Tag/Lib.Hidden.php");
	include_once("includes/common/Tag/Lib.Button.php");

	include_once("includes/common/Lib.Mailer.php");
	include_once("includes/common/Lib.GlobalLocals.php");
	include_once("includes/common/Lib.ILocalizator.php");

?>