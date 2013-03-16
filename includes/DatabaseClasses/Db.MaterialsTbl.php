<?php

	if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    	die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the Projects related classes etc
	 *
	 * @package Materials.pkg
	 */
	
	/**
	 * class MaterialsTbl
	 *
	 * The main class of the MaterialsTbl. Used for the operating with the table of materials
	 * 
	 * @package Materials.pkg
	 */
	 
	class MaterialsTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`MATERIALS`";
		public $tableGroupsName = "`build`.`MATERIALS_GROUPS`";

		/**
		 * Таблиці можуть бути на різних серверах. За замовченням, використовуються $this->commonCredentials
		 * Також із цією метою було створено додатковий класс Database
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
		
		public function LoadMaterials($name = "", $limit = 0, $offset = 0, $order_by = '`UPDATED_TIME`')
		{
			$result = NULL;
			
			$limit_text = "";
			if($limit > 0 || $offset > 0)
			{
				$limit_text = " LIMIT ".(float)$offset;
				
				if($limit > 0)
				{
					$limit_text .= ",".(float)$limit;
				}
			}

			$name_text = "";
			if(strlen($name) > 0)
			{
				$name_text = "WHERE `NAME` like '%".StaticDatabase::CleanupTheField($name)."%'";
			}

			$materials_inner_join = '';
			if(strlen($this->dataRow['MATERIALS_GROUPS_IDENTIFIER']) > 0)
			{
				$materials_inner_join = " INNER JOIN ".$this->tableGroupsName." mg ON mg.`UNIQUE_NAME_IDENTIFIER` = '".$this->dataRow['MATERIALS_GROUPS_IDENTIFIER']."' AND mg.`MATERIALS_GROUPS_ID` = mt.`MATERIALS_GROUPS_ID` ";
			}

			if((float)$this->dataRow['MATERIALS_GROUPS_ID'] > 0)
			{
				$materials_inner_join = " INNER JOIN ".$this->tableGroupsName." mg ON mg.`MATERIALS_GROUPS_ID` = '".(float)$this->dataRow['MATERIALS_GROUPS_ID']."' ";
			}

			if((float)$this->dataRow['VENDOR_COMPANIES_ID'] > 0)
			{
				if(strlen($name_text) > 0)
				{
					$name_text .= " OR ";
				}
				else
				{
					$name_text .= " WHERE ";
				}

				$name_text .= "`VENDOR_COMPANIES_ID`='".$this->dataRow['VENDOR_COMPANIES_ID']."'";
			}

			$query = "SELECT mt.* FROM <%prefix%>".$this->tableName." mt ".$materials_inner_join." ".$name_text." ORDER BY ".$order_by." ".$limit_text;
//echo $query;
			$result = $this->databaseClass->SqlQuery($query);

			return $result;
		}
		
		/**
		 * Check if materials code is unique
		 */
		public function ValidateUniqueIdentifier()
		{
			$result = NULL;

			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `UNIQUE_NAME_IDENTIFIER`='".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."' LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);

			if(mysql_num_rows($result) == 1)
			{
				$this->dataRow = NULL;
				$this->dataRow = mysql_fetch_array($result);
			}
			else
			{
				$this->dataRow = NULL;
			}
			
			return $this->dataRow;
		}
		
		public function RowsCount()
		{
			$result = NULL;

			$query = "SELECT COUNT(`MATERIALS_ID`) FROM ".$this->tableName;//." WHERE `MATERIALS_ID` = '".(float)$this->dataRow['MATERIALS_ID']."' ";
			$result = $this->databaseClass->SqlQuery($query);

			if($result != NULL)
			{
				$res = mysql_fetch_array($result);
				$result = $res[0];
			}
			
			return $result;
		}
		
		public function DeleteMaterial()
		{
			$result = NULL;
			
			if($this->dataRow['MATERIALS_ID'] > 0)
			{
				$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE `MATERIALS_ID`='".(float)$this->dataRow['MATERIALS_ID']."' LIMIT 1";
				
				$result = $this->databaseClass->SqlQuery($query);
			}
			
			return $result;
		}
		
		public function GetMaterialsById()
		{
			$result = NULL;
			
			if($this->dataRow['MATERIALS_ID'] > 0)
			{
				$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `MATERIALS_ID`='".(float)$this->dataRow['MATERIALS_ID']."' LIMIT 1";
				
				$result = $this->databaseClass->SqlQuery($query);
				
				$this->dataRow = NULL;
				if(mysql_num_rows($result) == 1)
				{
					$result = $this->dataRow = mysql_fetch_array($result);
				}
			}
			
			return $result;
		}
				
		public function CreateMaterial()
		{
			$result = NULL;
			
			$insert_id = 0;
			
			$query = "INSERT INTO <%prefix%>".$this->tableName." ".
					 " (`GUID`, `CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `MATERIALS_GROUPS_ID`, `NAME`, `ORD`, `IS_PUBLIC`, `IS_APPROVED`, `DIVISIBILITY_ID`, ".
					 " 	`ORIGINAL_COUNTRIES_ID`, `VENDOR_COMPANIES_ID`, `VENDOR_TEXT`, `COMMON_WWW`, `UNIQUE_NAME_IDENTIFIER`, `COMMENT` ) VALUES ".
					 " (".$this->databaseClass->GuidColumn().", ".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".(float)$this->updatedLogin."', ".
					 "  '".(float)$this->dataRow['MATERIALS_GROUPS_ID']."', '".$this->dataRow['NAME']."', '".(float)$this->dataRow['ORD']."', '".(int)$this->dataRow['IS_PUBLIC']."', '".(int)$this->dataRow['IS_APPROVED']."', '".$this->dataRow['DIVISIBILITY_ID']."', ".
					 "  '".$this->dataRow['ORIGINAL_COUNTRIES_ID']."', '".$this->dataRow['VENDOR_COMPANIES_ID']."', '".$this->dataRow['VENDOR_TEXT']."', '".$this->dataRow['COMMON_WWW']."', '".$this->dataRow['UNIQUE_NAME_IDENTIFIER']."', '".$this->dataRow['COMMENT']."')";
					 			
			$result = $this->databaseClass->SqlQuery($query);

			$insert_id = mysql_insert_id();
			
			return $this->dataRow['MATERIALS_ID'] = $insert_id;
		}
		
		/**
		 * Збереження матеріалу
		 */
		public function SaveMaterial()
		{
			$result = NULL;
			
			$query = "UPDATE <%prefix%>".$this->tableName." SET ".
					 " `UPDATED_TIME`=".$this->updatedTimeValue.", `UPDATED_LOGIN`='".(float)$this->updatedLogin."', ".
					 " `MATERIALS_GROUPS_ID`='".(float)$this->dataRow['MATERIALS_GROUPS_ID']."', `NAME`='".$this->dataRow['NAME']."', `ORD`='".(int)$this->dataRow['ORD']."', `IS_PUBLIC`='".(int)$this->dataRow['IS_PUBLIC']."', `IS_APPROVED`='".(int)$this->dataRow['IS_APPROVED']."', ".
					 " `DIVISIBILITY_ID`='".$this->dataRow['DIVISIBILITY_ID']."', `ORIGINAL_COUNTRIES_ID`='".$this->dataRow['ORIGINAL_COUNTRIES_ID']."', `VENDOR_COMPANIES_ID`='".$this->dataRow['VENDOR_COMPANIES_ID']."', `VENDOR_TEXT`='".substr($this->dataRow['VENDOR_TEXT'], 0, 200)."',".
					 " `COMMON_WWW`='".substr($this->dataRow['COMMON_WWW'], 0, 200)."', `UNIQUE_NAME_IDENTIFIER`='".substr($this->dataRow['UNIQUE_NAME_IDENTIFIER'], 0, 75)."', `COMMENT`='".substr($this->dataRow['COMMENT'], 0, 5000)."' WHERE `MATERIALS_ID`='".$this->dataRow['MATERIALS_ID']."' LIMIT 1";
					 
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
CREATE  TABLE IF NOT EXISTS `MYISAM_TABLES`.`MATERIALS_GROUPS` (
  `MATERIALS_GROUPS_ID` BIGINT NOT NULL AUTO_INCREMENT ,
  `CREATED_TIME` TIMESTAMP NULL DEFAULT NOW() ,
  `UPDATED_TIME` TIMESTAMP NULL DEFAULT NOW() ,
  `UPDATED_LOGIN` BIGINT NULL ,
  `GROUPS_NAME` VARCHAR(100) NULL ,
  PRIMARY KEY (`MATERIALS_GROUPS_ID`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COMMENT = 'Статична таблиця. \nВ базовій версії буде заповнятись адміністратором\n'
*/
?>