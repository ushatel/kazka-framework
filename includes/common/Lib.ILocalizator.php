<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

/**
 * The Package collects the common user classes
 *
 * @package Library.pkg
 */

/**
 * interface ILocalizator
 *
 * The main class of the Locals. Every class with locals should implement it
 * 
 * @package Library.pkg
 */


interface ILocalizator
{

	/**
	 *  Returns the value for the localizable property
	 */
	public function GetGlobalValue($property, $lang = "");

	public function GetLocalValue($property, $lang = "");
	
}

?>