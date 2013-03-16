<?php

	if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    	die ("You can't access this file directly...");

	include_once("includes/DatabaseClasses/Db.CommonTable.php");
	include_once("includes/DatabaseClasses/Db.IDataConnector.php");

	/**
	 * The Package collects the MATERIALS related classes etc 
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
	 
	class MaterialsSuppliersStockTbl extends CommonTable implements IDataConnector 
	{
		public $tableName = "`build`.`MATERIALS_SUPPLIERS_STOCK`";

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
		
		public function LoadSuppliersForMaterial($materialId)
		{
			$result = NULL;
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `MATERIALS_ID`='".(float)$materialId."' ";
			$result = $this->databaseClass->SqlQuery($query);
			
			$suppliersIds = array();
			
			if($result != NULL && mysql_num_rows($result) > 0)
			{
				while($row = mysql_fetch_array($result))
				{
					array_push($suppliersIds,$row['COMPANIES_ID']);
				}
			}
			
			return $suppliersIds;
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
				$materials_ids = "AND `MATERIALS_ID` IN (";
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
			
			$query = "SELECT * FROM <%prefix%>".$this->tableName." WHERE `COMPANIES_ID` = '".(float)$this->dataRow['COMPANIES_ID']."' ".$materials_ids." ORDER BY `ORD`";
			
			$result = $this->databaseClass->SqlQuery($query);
			
			return $result;
		}
		
	}

/*
CREATE  TABLE IF NOT EXISTS `BUILD`.`MATERIALS_SUPPLIERS` (
  `MATERIALS_SUPPLIERS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL ,
  `COMPANIES_ID` BIGINT UNSIGNED NULL ,
  `MATERIALS_ID` BIGINT UNSIGNED NULL ,
  `DIVISIBILITY_ID` BIGINT UNSIGNED NULL ,
  `QUANTITY` INT NULL ,
  PRIMARY KEY (`MATERIALS_SUPPLIERS_ID`) ,
  UNIQUE INDEX `MATERIALS_SUPPLIERS_ID_UNIQUE` (`MATERIALS_SUPPLIERS_ID` ASC) )
ENGINE = InnoDB
COMMENT = 'Постачальники матеріалів'
*/

?>