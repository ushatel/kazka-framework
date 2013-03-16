<?
  if (@preg_match("includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");
    
    require_once("Main.StaticDatabase.php");
    
//Error_Reporting(E_ALL & ~E_NOTICE);

/**
 * The Package collects the database related classes etc
 *
 * @package Database.pkg
 */

/**
 * class Database
 *
 * The instance class of the Database connector for the access tables on the dedicated DB servers
 * 
 * @package Database.pkg
 */
class Database 
{
	private 		$link 	= NULL;

	const HOST 				= "localhost";
	const USERNAME  		= "root";
	const PASSWORD  		= "123";
	const DBNAME 			= "build";
	
	public 	$PREFIX			= "";
	
	/**
	 * Connects to the SQL hosts with the user pass
	 */	
	public function SqlConnectCredentials($host, $username, $password, $dbname, $prefix) 
	{
		$this->PREFIX = $prefix;
		$this->link = @mysql_pconnect($host, $username, $password);

		if(($this->link == NULL) || !@mysql_select_db($dbname) ) 
		{
	/*!!! SHOULD RISE SYSTEM ERROR !!!*/
			trigger_error( "<%CUSTOM_APP_EVENT%>Error! No Connection to db: $host - $dbname", E_USER_ERROR );
		}
		
		$query = "set character set utf8";
		@mysql_query($query) or die(mysql_error());
	
		@mysql_query('SET NAMES utf8');
		@mysql_query('set session character_set_server=utf8');
		@mysql_query('set session character_set_database=utf8');
		@mysql_query('set session character_set_connection=utf8');
		@mysql_query('set session character_set_results=utf8');
		@mysql_query('set session character_set_client=utf8');
		
		return $this->link;
	}

	/**
	* Connection method 
	*/
	public function SqlConnect () 
	{
		$this->link = 
				$this->SqlConnectCredentials(self::HOST, self::USERNAME, self::PASSWORD, self::DBNAME, $this->PREFIX);
		
		return $this->link;
	}
	
	/**
	* Send SQL query
	*/
	public function SqlQuery($query) 
	{
		$result = NULL;
		if($this->link != NULL) 
		{
			$result = mysql_query(preg_replace("/<%prefix%>/i", $this->PREFIX, $query), $this->link);

			if(Session::IsSuperAdmin())
				StaticDatabase::PlusOne();
		}
		return $result;
	}
	
	public function GuidColumn() 
	{
		return "UUID()";
	}
}

?>