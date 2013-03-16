<?php
  if (@preg_match("/db./i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");
    
//Error_Reporting(E_ALL & ~E_NOTICE);

/**
 * The Package collects the database related classes etc
 *
 * @package Database.pkg
 */

/**
 * class StaticDatabase
 *
 * The main class of the Database connector for the database data access
 * 
 * @package Database.pkg
 */
class StaticDatabase 
{
	private static $link 		 = "";
	private static $queryCount 	 = 0;

	const HOST 			= "localhost";
	const USERNAME  		= "root";
	const PASSWORD  		= "123";
	const DBNAME 			= "build";
	
	public static $PREFIX	= "";
	
	/**
	 * Clean the incomming fields
	 */
	public static function CleanupTheField($valueToCleanUp) 
	{
		return mysql_real_escape_string(Security::StripPostVar($valueToCleanUp));
	}
	
	public static function EncodeFieldsData($value)
	{
		return Security::EncodeUrlData($value);
	}
	
	public static function DecodeFieldsData($value)
	{
		return Security::DecodeUrlData($value);
	}
	
	/**
	 * Connects to the SQL hosts with the user pass
	 */	
	public static function SqlConnectCredentials($host, $username, $password, $dbname, $prefix) 
	{
		self::$PREFIX = $prefix;
		self::$link = @mysql_pconnect($host, $username, $password);

		if((self::$link == NULL) || !@mysql_select_db($dbname) ) 
		{
	/*!!! SHOULD RISE SYSTEM ERROR !!!*/
			echo "Error! Connection to db. ";
		}

		$query = "set character set utf8";
		@mysql_query($query) or trigger_error(mysql_error());
	
		@mysql_query('SET NAMES utf8');
		@mysql_query('set session character_set_server=utf8');
		@mysql_query('set session character_set_database=utf8');
		@mysql_query('set session character_set_connection=utf8');
		@mysql_query('set session character_set_results=utf8');
		@mysql_query('set session character_set_client=utf8');

		return self::$link;
	}

	/**
	* Connection method 
	*/
	public static function SqlConnect () 
	{
		self::$link = self::SqlConnectCredentials(self::HOST, self::USERNAME, self::PASSWORD, self::DBNAME, StaticDatabase::$PREFIX);
		
		return self::$link;
	}
	
	/**
	* Send SQL query
	*/
	public function SqlQuery($query) 
	{
		if(Session::IsSuperAdmin())
		{
			self::PlusOne();
		}
		return mysql_query(preg_replace("/<%prefix%>/i", self::$PREFIX, $query), self::$link);
	}
	
	/**
	* Send static SQL query
	*/
	public static function SqlStaticQuery($query) 
	{
		if(Session::IsSuperAdmin())
		{
			self::PlusOne();
		}
		return mysql_query(preg_replace("/<%prefix%>/i", self::$PREFIX, $query), self::$link);
	}
	
	public static function SaveGuidColumn() 
	{
		//echo "UUID()";
	}
	
	public static function PlusOne() 
	{
		return self::$queryCount = self::$queryCount + 1;
	}
	
	public static function GetQueryCount() 
	{
		return self::$queryCount;
	}
}

?>