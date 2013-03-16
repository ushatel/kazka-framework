<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

/**
 * class CommonClass
 *
 * Загальний клас для класів роботи із даними, якто Parts.Users.php
 *
 * The common class for the database tables classes. 
 * 
 * @package Database.pkg
 */

class CommonClass 
{

	/**
	 * Вказує, чи запит може бути кешовано
	 */
	private $isCachable = false;

	public function __construct ()
	{
		
	}
} 

class CommonItem 
{
}

?>