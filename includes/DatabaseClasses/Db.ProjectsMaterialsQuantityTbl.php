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
	 * class ProjectsMaterialsQuantityTbl
	 *
	 * The main class of the ProjectsMaterialsQuantityTbl. Used for the operating with the table of projects materials quantity for suppliers
	 * 
	 * @package Materials.pkg
	 */
	 
	class ProjectsMaterialsQuantityTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`PROJECTS_MATERIALS_QUANTITY`";

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
		
		
		/**
		 * Повертає перелік матеріалів для вказаної компанії-постачальника
		 */
		public function LoadCompaniesMaterials($materials_array = NULL)
		{
			$result = NULL;

			$materials_ids = "";
			
			if(is_array($materials_array))
			{	
				$materials_ids = "AND MATERIALS_ID IN (";
				$first = true;
				
				foreach($materials_array as $value )
				{
					if(!$first)
						$materials_ids .= ",";
					else
						$first = false;
						
					$materials_ids .= (string)$value;
				}
				
				$materials_ids .= ")";
			}
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE COMPANIES_ID = '".(float)$this->dataRow['COMPANIES_ID']."' ".$materials_ids." ORDER BY `ORD`";
			
			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}
		
		public function GetMaterialsQuantity()
		{
			$result = NULL;
			
			$divisibility = "";
			if((float)$this->dataRow['DIVISIBILITY_ID'] > 0)
			{
				$divisibility = "AND `DIVISIBILITY_ID`='".$this->dataRow['DIVISIBILITY_ID']."'";
			}
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `PROJECTS_ID`='".(float)$this->dataRow['PROJECTS_ID']."' AND `MATERIALS_ID`='".(float)$this->dataRow['MATERIALS_ID']."' AND `COMPANIES_ID`='".(float)$this->dataRow['COMPANIES_ID']."' ".$divisibility." LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL && mysql_num_rows($result) > 0)
			{
				$this->dataRow = mysql_fetch_array($result);
				$result = true;
			}
			else
			{
				$this->dataRow = NULL;
				$result = false;
			}

			return $result;
		}
		
		public function GetMaterialsQuantityForProject()
		{
			$result = NULL;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `PROJECTS_ID`='".(float)$this->dataRow['PROJECTS_ID']."' ";
			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}
		
		public function SaveMaterialsQuantity()
		{
			$result = NULL;
			
			$query = "SELECT `PROJECTS_MATERIALS_ID` FROM ".$this->tableName." WHERE `PROJECTS_MATERIALS_ID`='".(float)$this->dataRow['PROJECTS_MATERIALS_ID']."' OR ((`PROJECTS_ID`='".(float)$this->dataRow['PROJECTS_ID']."' OR `PROJECTS_STEPS_ID`='".(float)$this->dataRow['PROJECT_STEPS_ID']."') AND `MATERIALS_ID`='".(float)$this->dataRow['MATERIALS_ID']."' AND `COMPANIES_ID`='".(float)$this->dataRow['COMPANIES_ID']."') LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);
			
			if(mysql_num_rows($result) > 0)
			{				
				$query = "UPDATE <%prefix%>".$this->tableName." SET ".
						 " `UPDATED_TIME`=".$this->updatedTimeValue.", `UPDATED_LOGIN`='".$this->updatedLogin."', `COMPANIES_ID`='".(float)$this->dataRow['COMPANIES_ID']."', `MATERIALS_ID`='".(float)$this->dataRow['MATERIALS_ID']."', ".
						 " `DIVISIBILITY_ID`='".(float)$this->dataRow['DIVISIBILITY_ID']."', `PROJECTS_ID`='".(float)$this->dataRow['PROJECTS_ID']."', `PROJECTS_STEPS_ID`='".(float)$this->dataRow['PROJECT_STEPS_ID']."', `REQUEST_TIME`=FROM_UNIXTIME(".$this->dataRow['REQUEST_TIME']."), `FACT_TIME`=FROM_UNIXTIME(".$this->dataRow['FACT_TIME']."), `QUANTITY`='".(int)$this->dataRow['QUANTITY']."', `ORD`='".(float)$this->dataRow['ORD']."', `MATERIALS_UNIQUE_NAME`='".substr($this->dataRow['MATERIALS_UNIQUE_NAME'], 0, 75)."', `COMMENT`='".substr($this->dataRow['COMMENT'], 0, 5000)."'".
						 " WHERE `PROJECTS_MATERIALS_ID`='".(float)$this->dataRow['PROJECTS_MATERIALS_ID']."' OR (`PROJECTS_ID`='".$this->dataRow['PROJECTS_ID']."' AND `PROJECTS_STEPS_ID`='".(float)$this->dataRow['PROJECT_STEPS_ID']."' AND `MATERIALS_ID`='".$this->dataRow['MATERIALS_ID']."' AND `COMPANIES_ID`='".$this->dataRow['COMPANIES_ID']."') LIMIT 1";
						 
				$result = $this->databaseClass->SqlQuery($query);

				if($result != NULL)
				{
					$result = true;
				}
			}
			else
			{
				// first supplier for the material
				$query = "INSERT INTO <%prefix%>".$this->tableName." ".
						 " (`CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `COMPANIES_ID`, `MATERIALS_ID`, `DIVISIBILITY_ID`, `PROJECTS_ID`, `PROJECTS_STEPS_ID`, `REQUEST_TIME`, `FACT_TIME`, `IS_USED`, `QUANTITY`, `ORD`, `MATERIALS_UNIQUE_NAME`, `COMMENT` ) VALUES ".
						 " (".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".$this->updatedLogin."', '".(float)$this->dataRow['COMPANIES_ID']."', '".(float)$this->dataRow['MATERIALS_ID']."', '".(float)$this->dataRow['DIVISIBILITY_ID']."', '".(float)$this->dataRow['PROJECTS_ID']."', '".(float)$this->dataRow['PROJECTS_STEPS_ID']."', FROM_UNIXTIME(".(float)$this->dataRow['REQUEST_TIME']."), FROM_UNIXTIME(".(float)$this->dataRow['FACT_TIME']."), '".(int)$this->dataRow['IS_USED']."', '".(float)$this->dataRow['QUANTITY']."', '".(float)$this->dataRow['ORD']."', '".substr($this->dataRow['MATERIALS_UNIQUE_NAME'], 0, 75)."', '".substr($this->dataRow['COMMENT'], 0, 5000)."') ";

				$result = $this->databaseClass->SqlQuery($query);

				if(mysql_insert_id() > 0)
				{
					$this->dataRow['PROJECTS_MATERIALS_ID'] = mysql_insert_id();
					
					$result = true;
				}
				else 
				{
					$result = false;
				}
			
			}
			
			return $result;
		}
		
		public function DeleteMaterialsByProjectsId()
		{
			$result = NULL;
			
			$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE `PROJECTS_ID`='".$this->dataRow['PROJECTS_ID']."' ";
			$result = $this->databaseClass->SqlQuery($query);
			
			return mysql_affected_rows();
		}
		
		public function DeleteProjectsMaterials()
		{
			$res = NULL;
		
			$query = "DELETE FROM <prefix>".$this->tableName." WHERE `PROJECTS_MATERIALS_ID`='".$this->dataRow['PROJECTS_MATERIALS_ID']."' LIMIT 1";
			$res = $this->databaseClass->SqlQuery($query);
			
			return $res;			
		}
		
		public function CreateMaterialFromOriginal()
		{
			$result = NULL;
			
			$insert_id = 0;
			
			$query = "INSERT INTO <%prefix%>".$this->tableName." ".
					 " (`CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `COMPANIES_ID`, `MATERIALS_ID`, `DIVISIBILITY_ID`, ".
					 "  `QUANTITY`, `ORD`, `MATERIALS_NAME`, `COMMENT`) VALUES ".
					 " (".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".(float)$this->updatedLogin."', '".(float)$this->dataRow['COMPANIES_ID']."', '".(float)$this->dataRow['MATERIALS_ID']."', '".(float)$this->dataRow['DIVISIBILITY_ID']."', ".
					 "  '".(int)$this->dataRow['QUANTITY']."', '".(float)$this->dataRow['ORD']."', '".substr($this->dataRow['MATERIALS_NAME'], 0, 200)."', '".substr($this->dataRow['COMMENT'], 0, 5000)."')";
					 
			$result = $this->databaseClass->SqlQuery($query);
			
			$inserted_id = mysql_insert_id();
			
			return $this->dataRow["MATERIALS_QUANTITY_ID"] = $insert_id;
		}
	}

/*
CREATE  TABLE IF NOT EXISTS `BUILD`.`PROJECTS_MATERIALS_QUANTITY` 
(
  `PROJECTS_MATERIALS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `CREATED_TIME` TIMESTAMP NULL DEFAULT NOW() ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL ,
  `COMPANIES_ID` BIGINT UNSIGNED NULL COMMENT 'Компанія постачальник матеріалу' ,
  `MATERIALS_ID` BIGINT UNSIGNED NULL ,
  `DIVISIBILITY_ID` BIGINT UNSIGNED NULL ,
  `PROJECTS_ID` BIGINT UNSIGNED NULL ,
  `PROJECTS_STEPS_ID` BIGINT UNSIGNED NULL ,
  `REQUEST_TIME` TIMESTAMP NULL COMMENT 'Вказує час, коли матеріал повинен бути на об\'єкті' ,
  `FACT_TIME` TIMESTAMP NULL COMMENT 'Вказує час, коли матеріал було фактично доставлено' ,
  `IS_USED` INT(1) UNSIGNED NULL ,
  `QUANTITY` INT UNSIGNED NULL ,
  `ORD` BIGINT UNSIGNED NULL COMMENT 'Дамо можливість сортувати матеріали для користувача' ,
  `MATERIALS_UNIQUE_NAME` VARCHAR(75) NULL ,
  `COMMENT` VARCHAR(5000) NULL ,
  PRIMARY KEY (`PROJECTS_MATERIALS_ID`) ,
  UNIQUE INDEX `SUPPLIERS_MATERIALS_ID_UNIQUE` (`PROJECTS_MATERIALS_ID` ASC) ,
  INDEX `fk_COMPANIES_MATERIALS_COMPANIES1` (`COMPANIES_ID` ASC) ,
  INDEX `fk_COMPANIES_MATERIALS_MATERIALS1` (`MATERIALS_ID` ASC) ,
  INDEX `FK_DIVISIBILITY` (`DIVISIBILITY_ID` ASC) ,
  INDEX `FK_PROJECTS` (`PROJECTS_ID` ASC) ,
  INDEX `FK_PROJECTS_STEPS` (`PROJECTS_ID` ASC, `PROJECTS_STEPS_ID` ASC) 
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Перелік матеріалів із можливими постачальниками до проектів'*/


?>