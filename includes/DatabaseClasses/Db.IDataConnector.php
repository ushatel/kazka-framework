<?php

  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

/**
 * The Package collects the DataAccess related classes etc
 *
 * @package Database.pkg
 */

/**
 * interface IDataConnector
 *
 * The main class of the Users. Used as the main connector for the Data Tables
 * 
 * @package Database.pkg
 */


interface IDataConnector 
{
	public function GetDataRow() ;
}

?>