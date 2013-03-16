<?php

	if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    	die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the Projects related classes etc
	 *
	 * @package Projects.pkg
	 */
	
	/**
	 * class Projects
	 *
	 * The main class of the Projects. Used for the operating with the projects
	 * 
	 * @package Projects.pkg
	 */
	 
	class ProjectsTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`PROJECTS`";

		/**
		 *
		 *
		 */		
		public function __construct() 
		{
			$this->databaseClass = new Database();
			
			$this->databaseClass->SqlConnectCredentials($this->commonCredentials["host"], 
				$this->commonCredentials["username"], $this->commonCredentials["pass"], 
				$this->commonCredentials["dbname"], $this->commonCredentials["prefix"]);
				
			parent::__construct();
		}
		
		public function GetDataRow()
		{
			return $this->dataRow;
		}
		
		public function DeleteProject()
		{
			$result = NULL;
			
			$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE `PROJECTS_ID`='".$this->dataRow['PROJECTS_ID']."' ";
			$result = $this->databaseClass->SqlQuery($query);
			
			return mysql_affected_rows();
		}
		
		public function GetProjectById()
		{
			$result = NULL;
			
			$upd = "";
			if($this->dataRow['UPDATED_ID'] !== NULL)
			{
				$upd = " OR `UPDATED_LOGIN`='".$this->dataRow['UPDATED_LOGIN']."' ";
			}
			
			$query = " SELECT * FROM <%prefix%>".$this->tableName." ".
					 " WHERE `PROJECTS_ID`='".$this->dataRow['PROJECTS_ID']."' ".$upd." LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);

			if(mysql_num_rows($result) > 0)
			{
				$this->dataRow = mysql_fetch_array($result);
			}
			
			return $this->dataRow;
		}
		
		public function LoadProjects($name = '', $limit = 0, $offset = 0, $order_by = '`NAME`')
		{
			$result = NULL;
			
			$limit_text = "";
			if($limit > 0 || $offset > 0)
			{
				$limit_text = " LIMIT ".(float)$limit;
				
				if($offset > 0)
				{
					$limit_text .= ",".(float)$offset;
				}
			}
			
			$where .= " WHERE ";
			
			if((float)$this->dataRow['OWNER_ID'] > 0)
			{
				$where .= "`OWNER_ID`= '".(float)$this->dataRow['OWNER_ID']."' ";				
			}
			
			if(strlen($this->dataRow['NAME']) > 0 || strlen($name) > 0)
			{
				if(strlen($where) > 7)
				{
					$where .= " OR ";
				}
				
				if(strlen($name) > 0)
				{
					$where .= " `NAME` = '".$this->dataRow['NAME']."' ";
				}
				else
				{
					$where .= " `NAME` = '".$this->dataRow['NAME']."' ";
				}
			}

			if($this->dataRow['IS_PUBLIC'] !== NULL)
			{
				if(strlen($where) > 7)
				{
					$where .= " OR ";
				}
				
				$where .= "`IS_PUBLIC`='".(int)$this->dataRow['IS_PUBLIC']."' ";	
			}

			if((float)$this->dataRow['COMPANIES_ID'] > 0)
			{
				if(strlen($where) > 7)
				{
					$where .= " OR ";
				}
			
				$where .= "`COMPANIES_ID` = '".(float)$this->dataRow['COMPANIES_ID']."' ";
			}

			if((float)$this->dataRow['UPDATED_LOGIN'] > 0)
			{
				if(strlen($where) > 7)
					$where .= " OR ";
			
				$where .= "`UPDATED_LOGIN`='".$this->dataRow['UPDATED_LOGIN']."' ";
			}

			$uniq_name = $this->dataRow['UNIQUE_NAME_IDENTIFIER'];

			$query = " SELECT * FROM <%prefix%>".$this->tableName." ".
					 " ".(strlen($where) > 7 ? $where : '')."  ORDER BY ".$order_by." ".$limit_text;

			$result = $this->databaseClass->SqlQuery($query);

			return $result;
		}
		
		public function LoadUsersProjects()
		{
			$result = NULL;
			
			$query = " SELECT * FROM <%prefix%>".$this->tableName." ".
					 " WHERE `OWNER_ID`='".$this->dataRow['OWNER_ID']."' ORDER BY `UPDATED_TIME`";
			
			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}
		
		public function PublicateProject()
		{
			$result = NULL;

			$query = "SELECT `IS_PUBLIC` FROM <%prefix%>".$this->tableName." WHERE `PROJECTS_ID`='".(float)$this->dataRow['PROJECTS_ID']."' LIMIT 1 ";
			$result = $this->databaseClass->SqlQuery($query);

			if(mysql_num_rows($result) > 0)
			{
				$row = mysql_fetch_array($result);

				$is_public = (float)$row['IS_PUBLIC'];
				if($is_public > 0)
					$is_public = 0;
				else
					$is_public = 1;

				$query = "UPDATE <%prefix%>".$this->tableName." SET `UPDATED_TIME`=".$this->updatedTimeValue.", `UPDATED_LOGIN`='".Session::GetUserId()."', `IS_PUBLIC`='".(int)$is_public."' WHERE `PROJECTS_ID`='".(float)$this->dataRow['PROJECTS_ID']."' LIMIT 1 ";
				$result = $this->databaseClass->SqlQuery($query);
			}

			return (mysql_affected_rows() > 0 ? $is_public : NULL);
		}
		
		public function LoadCompaniesProjects()
		{
			$result = NULL;
			
			$query = " SELECT * FROM <%prefix%>".$this->tableName." ".
					 " WHERE `COMPANIES_ID`='".$this->dataRow['COMPANIES_ID']."' ORDER BY `NAME`";

			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}
		
		public function ValidateUniqueIdentifier()
		{
			$result = NULL;

			$query = " SELECT * FROM <%prefix%>".$this->tableName." ".
					 " WHERE `UNIQUE_NAME_IDENTIFIER`='".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."' LIMIT 1 ";
					 
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL && mysql_num_rows($result) == 1)
			{
				$result = mysql_fetch_array($result);
			}
			
			return $result;
		}
		
		public function CreateProject()
		{
			$insert_id = 0;

			$query = "INSERT INTO <%prefix%>".$this->tableName." ".
					 " (`GUID`, `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, ".
					 "  `COMPANIES_ID`, `COUNTRIES_ID`, `OWNER_ID`, `START_TIME`, `END_TIME`, `NAME`, `UNIQUE_NAME_IDENTIFIER`, `COMMENT`, `IS_PUBLIC` ) VALUES ".
					 " (".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".$this->updatedTimeValue.", ".
					 " '".(float)Session::GetUserId()."', '".(float)$this->dataRow['COMPANIES_ID']."', '".(float)$this->dataRow['COUNTRIES_ID']."', '".(float)$this->dataRow['OWNER_ID']."', FROM_UNIXTIME(".$this->dataRow['START_TIME']."), FROM_UNIXTIME(".$this->dataRow['END_TIME']."), '".$this->dataRow['NAME']."', '".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."', '".$this->dataRow['COMMENT']."', '".$this->dataRow['IS_PUBLIC']."');";

			$result = $this->databaseClass->SqlQuery($query);

			$insert_id = mysql_insert_id();

			return $this->dataRow['PROJECTS_ID'] = $insert_id;
		}

		public function SaveProject()
		{
			$query = "UPDATE <%prefix%>".$this->tableName." ".
					 " SET `UPDATED_TIME`=".$this->updatedTimeValue.", `UPDATED_LOGIN`='".(float)Session::GetUserId()."', `COMPANIES_ID`='".(float)$this->dataRow['COMPANIES_ID']."', `COUNTRIES_ID`='".(float)$this->dataRow['COUNTRIES_ID']."', ".
					 "     `OWNER_ID`='".(float)$this->dataRow['OWNER_ID']."', `START_TIME`=FROM_UNIXTIME(".$this->dataRow['START_TIME']."), `END_TIME`=FROM_UNIXTIME(".$this->dataRow['END_TIME']."), `NAME`='".$this->dataRow['NAME']."', `UNIQUE_NAME_IDENTIFIER`='".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."', `COMMENT`='".$this->dataRow['COMMENT']."', `IS_PUBLIC`='".$this->dataRow['IS_PUBLIC']."' ".
					 " WHERE `PROJECTS_ID` = '".(float)$this->dataRow['PROJECTS_ID']."' LIMIT 1";
			
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL)
			{
				$result = true;
			}
			else
			{
				$result = false;
			}
			
			return $result;
		}
	}

/*
  `COMPANIES_ID` BIGINT UNSIGNED NULL ,
  `COUNTRIES_ID` BIGINT UNSIGNED NULL ,
  `START_TIME` TIMESTAMP NULL ,
  `END_TIME` TIMESTAMP NULL ,
  `NAME` VARCHAR(200) NULL ,

*/
?>