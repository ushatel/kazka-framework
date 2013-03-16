<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");
    
 	require_once("includes/common/Main.Database.php");
 	require_once("includes/common/Main.Session.php");

	/**
	 * The Package collects the Users related classes etc
	 *
	 * @package Database.pkg
	 */

	/**
	 * class Users
	 *
	 * The main class of the Users. Used for the operating with the users
	 * 
	 * @package Database.pkg
	 */

	class CommonTable 
	{
		private $databaseClass = NULL;

		protected $commonCredentials = array("host" => "localhost", "username" => "root", 
											 "pass" => "123", "dbname" => "build", "prefix" => "");
		
		
		// Row with the data from the table
		public $dataRow = NULL;
		
		/**
		 * Вказує, чи були завантаженні дані із таблиці до змінної $dataRow
		 */
		private $isLoaded = false;
	
		public $createdTimeValue = NULL;
		public $updatedTimeValue = NULL;
		
		public $createdTime = NULL;
		
		
		
		/**
		 *  By default 0 - System user
		 */
		public $updatedLogin = 0;
	
		public $tableName = "";
		
		public function __construct() 
		{
			$this->createdTime = time();
			$this->updatedTimeValue = $this->createdTimeValue = "FROM_UNIXTIME(".$this->createdTime.")";
			$this->updatedLogin = Session::GetUserId();
		}
		
	}

?>