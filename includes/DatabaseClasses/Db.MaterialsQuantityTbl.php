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
	 * class MaterialsQuantityTbl
	 *
	 * The main class of the MaterialsQueantityTbl. Used for the operating with the table of materials quantity for suppliers
	 * 
	 * @package Materials.pkg
	 */
	 
	class MaterialsQuantityTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`MATERIALS_QUANTITY`";

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

		public function LoadSuppliers($materialId)
		{
			$result = NULL;

			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `MATERIALS_ID` = '".(float)$materialId."'";

			$result = $this->databaseClass->SqlQuery($query);

			return $result;
			
		}

		public function LoadSupplier($materialId, $companyId)
		{
			$result = NULL;

			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `MATERIALS_ID`='".(float)$materialId."' AND `COMPANIES_ID`='".(float)$companyId."' LIMIT 1";

			$result = $this->databaseClass->SqlQuery($query);
			
			$this->dataRow = $result = mysql_fetch_array($result);

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

			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `MATERIALS_ID`='".(float)$this->dataRow['MATERIALS_ID']."' AND `COMPANIES_ID`='".(float)$this->dataRow['COMPANIES_ID']."' ".$divisibility." LIMIT 1";
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
		
		public function SaveMaterialsQuantity()
		{
			$result = NULL;
			
			$query = "SELECT `MATERIALS_QUANTITY_ID` FROM ".$this->tableName." WHERE `MATERIALS_QUANTITY_ID`='".(float)$this->dataRow['MATERIALS_QUANTITY_ID']."' OR (`MATERIALS_ID`='".$this->dataRow['MATERIALS_ID']."' AND `COMPANIES_ID`='".(float)$this->dataRow['COMPANIES_ID']."')  LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);

			if(mysql_num_rows($result) > 0)
			{				
				$query = "UPDATE <%prefix%>".$this->tableName." SET ".
						 " `UPDATED_TIME`=".$this->updatedTimeValue.", `UPDATED_LOGIN`='".$this->updatedLogin."', `COMPANIES_ID`='".(float)$this->dataRow['COMPANIES_ID']."', `MATERIALS_ID`='".(float)$this->dataRow['MATERIALS_ID']."', ".
						 " `DIVISIBILITY_ID`='".(float)$this->dataRow['DIVISIBILITY_ID']."', `QUANTITY`='".(int)$this->dataRow['QUANTITY']."', `ORD`='".(float)$this->dataRow['ORD']."', `MATERIALS_UNIQUE_NAME`='".substr($this->dataRow['MATERIALS_UNIQUE_NAME'], 0, 200)."', `COMMENT`='".substr($this->dataRow['COMMENT'], 0, 5000)."', `COMPANIES_NAME`='".substr($this->dataRow['COMPANIES_NAME'], 0, 200)."' ".
						 " WHERE `MATERIALS_QUANTITY_ID`='".(float)$this->dataRow['MATERIALS_QUANTITY_ID']."' OR (`MATERIALS_ID`='".$this->dataRow['MATERIALS_ID']."' AND `COMPANIES_ID`='".$this->dataRow['COMPANIES_ID']."') LIMIT 1";
						 
				$result = $this->databaseClass->SqlQuery($query);
	
				if($result != NULL)
				{
					$result = true;
				}
			}
			else
			{
				// first supplier for the material
					
				$query = "INSERT <%prefix%>".$this->tableName." ".
						 " (`CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `COMPANIES_ID`, `MATERIALS_ID`, `DIVISIBILITY_ID`, `QUANTITY`, `ORD`, `MATERIALS_UNIQUE_NAME`, `COMMENT`, `COMPANIES_NAME` ) VALUES ".
						 " (".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".$this->updatedLogin."', '".(float)$this->dataRow['COMPANIES_ID']."', '".(float)$this->dataRow['MATERIALS_ID']."', '".(float)$this->dataRow['DIVISIBILITY_ID']."', '".(float)$this->dataRow['QUANTITY']."', '".(float)$this->dataRow['ORD']."', '".substr($this->dataRow['MATERIALS_UNIQUE_NAME'], 0, 200)."', '".substr($this->dataRow['COMMENT'], 0, 5000)."', '".substr($this->dataRow['COMPANIES_NAME'], 0, 200)."') "; 

				$result = $this->databaseClass->SqlQuery($query);
					
				if(mysql_insert_id() > 0)
				{
					$result = true;
				}
				else 
				{
					$result = false;
				}
			
			}
			
			return $result;
		}
		
		public function DeleteSupplier()
		{
			$result = NULL;
			$materialsQuantityId = 0;
			
			$query = "SELECT `MATERIALS_QUANTITY_ID` FROM <%prefix%>".$this->tableName." WHERE `MATERIALS_ID`='".(float)$this->dataRow['MATERIALS_ID']."' AND `COMPANIES_ID`='".(float)$this->dataRow['COMPANIES_ID']."' LIMIT 1";
			$result = $this->databaseClass->SqlQuery($query);
			
			if($result != NULL )
			{
				$row = mysql_fetch_array($result);
				$materialsQuantityId = $row['MATERIALS_QUANTITY_ID'];
				
				$query = "DELETE FROM <%prefix%>".$this->tableName." WHERE `MATERIALS_QUANTITY_ID`='".$materialsQuantityId."' LIMIT 1";
				$this->databaseClass->SqlQuery($query);
			}
			
			return $materialsQuantityId;
		}
		
		public function CreateMaterialFromOriginal()
		{
			$result = NULL;
			
			$insert_id = 0;
			
			$query = "INSERT INTO <%prefix%>".$this->tableName." ".
					 " (`CREATED_TIME`, `UPDATED_TIME`, `UPDATED_LOGIN`, `COMPANIES_ID`, `MATERIALS_ID`, `DIVISIBILITY_ID`, ".
					 "  `QUANTITY`, `ORD`, `MATERIALS_UNIQUE_NAME`, `COMMENT`, `COMPANIES_NAME`) VALUES ".
					 " (".$this->createdTimeValue.", ".$this->updatedTimeValue.", '".(float)$this->updatedLogin."', '".(float)$this->dataRow['COMPANIES_ID']."', '".(float)$this->dataRow['MATERIALS_ID']."', '".(float)$this->dataRow['DIVISIBILITY_ID']."', ".
					 "  '".(int)$this->dataRow['QUANTITY']."', '".(float)$this->dataRow['ORD']."', '".substr($this->dataRow['MATERIALS_UNIQUE_NAME'], 0, 200)."', '".substr($this->dataRow['COMMENT'], 0, 5000)."', '".$this->dataRow['COMPANIES_NAME']."')";

			$result = $this->databaseClass->SqlQuery($query);
			
			$inserted_id = mysql_insert_id();

			return $this->dataRow["MATERIALS_QUANTITY_ID"] = $inserted_id;
		}
	}

/*
CREATE  TABLE IF NOT EXISTS `build`.`MATERIALS_QUANTITY` (
  `SUPPLIERS_MATERIALS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL ,
  `COMPANIES_ID` BIGINT UNSIGNED NULL ,
  `MATERIALS_ID` BIGINT UNSIGNED NULL ,
  `DIVISIBILITY_ID` BIGINT UNSIGNED NULL ,
  `QUANTITY` INT UNSIGNED NULL ,
  `ORD` BIGINT UNSIGNED NULL COMMENT 'Дамо можливість сортувати матеріали для користувача' ,
  `COMMENT` VARCHAR(5000) NULL ,
  PRIMARY KEY (`SUPPLIERS_MATERIALS_ID`) ,
  UNIQUE INDEX `SUPPLIERS_MATERIALS_ID_UNIQUE` (`SUPPLIERS_MATERIALS_ID` ASC) ,
  INDEX `fk_COMPANIES_MATERIALS_COMPANIES1` (`COMPANIES_ID` ASC) ,
  INDEX `fk_COMPANIES_MATERIALS_MATERIALS1` (`MATERIALS_ID` ASC) ,
  INDEX `FK_DIVISIBILITY` (`DIVISIBILITY_ID` ASC) 
  )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Перелік матеріалів із можливими постачальниками'*/
?>