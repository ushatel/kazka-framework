<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");    
  
  include_once("includes/DatabaseClasses/Db.MaterialsTbl.php");
  include_once("includes/DatabaseClasses/Db.StepsTbl.php");
  include_once("includes/DatabaseClasses/Db.CompaniesTbl.php");
  include_once("includes/DatabaseClasses/Db.DivisibilityTbl.php");
  include_once("includes/DatabaseClasses/Db.MaterialsGroupsTbl.php");
  include_once("includes/DatabaseClasses/Db.MaterialsQuantityTbl.php");
  include_once("includes/DatabaseClasses/Db.MaterialsSuppliersStockTbl.php");
  include_once("includes/DatabaseClasses/Db.ProjectsMaterialsQuantityTbl.php");
  include_once("includes/DatabaseClasses/Db.CountriesTbl.php");

/**
 * The Package collects the materials related classes etc
 *
 * @package Materials.pkg
 */

/**
 * class Materials
 *
 * The main class of the Materials. Used for the operating with materials, contractors and so on.
 * 
 * @package Materials.pkg
 */
class Materials extends CommonClass
{
	private $materialsTblObj = NULL;
	private $projectsTblObj = NULL;
	private $companiesTblObj = NULL;
	private $materialsGroupsTblObj = NULL;
	private $divisibilityTblObj = NULL;
	private $materialsQuantityTblObj = NULL;
	private $materialsSuppliersStockTblObj = NULL;
	private $countriesTblObj = NULL;	
	
	public $materials_id = 0;
	public $materials_guid = '';
	public $materials_groups_id = 0;
	
	public $name = "";
	public $comment = "";
	
	public $ord = 0;
	
	public $is_public = false; 
	public $is_approved = false;
	
	public $divisibility_id = 0;
	public $original_countries_id = 0;
	
	public $vendor_companies_id = 0;
	public $vendor_text = "";
	
	public $common_www = "";
	
	public $unique_name_identifier = "";
	
	public $user_materials_unique_name = "";
	public $user_comment = "";
	public $user_divisibility_id = 0;
	public $user_quantity = 0;
	public $user_supplier_companies_id = 0;
	public $user_supplier_companies_name = "";
	public $user_materials_quantity_id = 0;
	public $user_materials_id = 0;
	
	public $groups_id = 0;
	public $groups_name = 0;
	
	public $groups_unique_name = '';
		
	public $groups_parent = 0;
	public $groups_parent1 = 0;
	public $groups_parent2 = 0;
	public $groups_parent3 = 0;
	public $groups_parent4 = 0;
	
	public $projects_id = 0;
	public $projects_companies_id = 0;
	public $projects_materials_id = 0;
	public $projects_divisibility_id = 0;
	public $projects_projects_id = 0;
	public $projects_steps_id = 0;
	public $projects_request_time = 0;
	public $projects_fact_time = 0;
	public $projects_quantity = 0;
	public $projects_ord = 0;
	public $projects_materials_unique_name = "";
	public $projects_materials_comment = "";
	
	public $divisbility_name = "";
	public $country_name = "";
	
	function __construct()
	{
		$this->materialsTblObj = new MaterialsTbl();
		$this->projectsTblObj = new ProjectsTbl();
		$this->companiesTblObj = new CompaniesTbl();
		$this->divisibilityTblObj = new DivisibilityTbl();
		$this->materialsQuantityTblObj = new MaterialsQuantityTbl();
		$this->materialsSuppliersStockTblObj = new MaterialsSuppliersStockTbl();
		$this->countriesTblObj = new CountriesTbl();
		$this->materialsGroupsTblObj = new MaterialsGroupsTbl();
	
		parent::__construct();
	}
	
	/**
	 * Повертає перелік матеріалів
	 */
	public function LoadMaterials($name = "", $limit = 0, $offset = 0, $order_by = "`UPDATED_TIME`")
	{
		$result = NULL;
		$this->isLoaded = false;
		
		$this->ParseFieldToDataRow();

		$result = $this->materialsTblObj->LoadMaterials($name, $limit, $offset, $order_by);
		
		return $result;
	}
	
	/**
	 * Видалити матеріал
	 */
	public function DeleteMaterial()
	{
		$result = NULL;
		
		$this->ParseFieldToDataRow();
		
		$result = $this->materialsTblObj->DeleteMaterial();
		
		return $result;
	}
	
	public function RowsCount()
	{
	
		$result = NULL;
		
		$this->ParseFieldToDataRow();
		$result = $this->materialsTblObj->RowsCount();
		
		return $result;	
	}
		
	public function LoadGroupsName()
	{
		if($this->isLoaded)
		{
			$this->materialsGroupsTblObj->dataRow['MATERIALS_GROUPS_ID'] = (float)$this->materials_groups_id;
			$this->materialsGroupsTblObj->GetGroupById();
			$this->groups_name = $this->materialsGroupsTblObj->dataRow['GROUPS_NAME'];
		}
		
		return $this->groups_name;
	}
	
	public function LoadDivisibilityName()
	{
		if($this->isLoaded)
		{
			$this->divisibilityTblObj->dataRow['DIVISIBILITY_ID'] = (float)$this->divisibility_id;
			$this->divisibilityTblObj->GetDivisibilityById();
			$this->divisibility_name = $this->divisibilityTblObj->dataRow['NAME'];
		}
		
		return $this->divisibility_name;
	}
	
	public function LoadCountryName()
	{
		if($this->isLoaded)
		{
			$this->countriesTblObj->dataRow['COUNTRIES_ID'] = (float)$this->original_countries_id;
			$this->countriesTblObj->LoadCountryById();
			$this->country_name = $this->countriesTblObj->dataRow['NAME'];	
		}
		
		return $this->country_name;
	}

	public function GetDivisibilitiesList()
	{
		$result = NULL;

		$this->isLoaded = false;

		$result = $this->divisibilityTblObj->LoadDivisibilityList();

		return $result;
	}
	
	public function GetMaterialsGroupsList()
	{
		$result = NULL; 
		
		$this->isLoaded = false;
		
		$this->materialsGroupsTblObj = new MaterialsGroupsTbl();
		
		$result = $this->materialsGroupsTblObj->LoadGroups();
		
		return $result;
	}
	
	public function SaveMaterialsGroup()
	{
		$result = NULL;
		
		$this->isLoaded = false;
		
		$this->materialsGroupsTblObj = new MaterialsGroupsTbl();
		$this->ParseFieldToDataRow();
		
		$result = $this->materialsGroupsTblObj->SaveGroup();
		
		return $result;
	}
	
	/** 
	 * Повертає постачальників матеріалів
	 */
	public function LoadMaterialsSuppliersOld($materialId)
	{
		$result = NULL;
		
		$result = $this->materialsSuppliersStockTblObj->LoadSuppliersForMaterial($materialId);
	
		if($result != NULL)
		{
			$this->companiesTblObj = new CompaniesTbl();
			$result = $this->companiesTblObj->LoadCompaniesByIds($result);
		}
		
		return $result;
	}
	
	public function DeleteSupplier()
	{
		$result = false;
		
		if($this->user_materials_quantity_id > 0 || $this->user_suppliers_companies_id > 0 && $this->user_materials_id > 0)
		{
			$this->ParseFieldToDataRow();
			$result = $this->materialsQuantityTblObj->DeleteSupplier();
		}
		
		return $result;
	}
	
	public function LoadSupplier($materialsId, $companiesId)
	{
		$result = NULL;
		
		$result = $this->materialsQuantityTblObj->LoadSupplier($materialsId, $companiesId);
		if( $result != NULL)
		{	//echo " aaaa ".$row['COMMENT'];
			$this->ParseDataRow();
			$this->isLoaded = true;
		}
		
		return $result;
	}

	public function LoadMaterialsSuppliers($materialId)
	{
		$result = NULL;

		$result = $this->materialsQuantityTblObj->LoadSuppliers($materialId);
	
		if($result != NULL)
		{
			$this->companiesTblObj = new CompaniesTbl();
			$result = $this->companiesTblObj->LoadCompaniesByIds($result);
		}

		return $result;
	}
	
	public function GetMaterialsById($needLoadQuantity = false)
	{
		$result = NULL;
		$this->isLoaded = false;
		
		$this->ParseFieldToDataRow();
		$dataRow = $this->materialsTblObj->GetMaterialsById();

		if($dataRow != NULL && $needLoadQuantity)
		{
			if($this->materialsQuantityTblObj->GetMaterialsQuantity())
			{
			}

			$this->ParseDataRow();
			
			$this->isLoaded = true;
		}
		elseif($dataRow != NULL)
		{
			$this->isLoaded = true;
			
			$this->ParseDataRow();
		}
		
		return $this->isLoaded;
	}
	
	public function CreateMaterial($isSupplier = false, $justSupplier = false)
	{
		$result = NULL;
		$this->isLoaded = false;
		$insert_id = 0;

		$this->ParseFieldToDataRow();
		
		if(!$justSupplier)
		{
			$insert_id = $this->materialsTblObj->CreateMaterial();
		}
		else
		{
			$insert_id = ($this->materials_id > 0 ? $this->materials_id : $this->user_materials_id);
			$this->GetMaterialsById();
		}
		
		
		if($insert_id < 1 && $this->materials_id > 0)
		{
			trigger_error(Logger::LOG_EVENT_CONST." Could not create the material ", E_USER_WARNING);
		}
		else
		{
			$this->materials_id = $insert_id;
			$this->isLoaded = true;

			if($isSupplier)
			{
				$this->user_materials_id = $this->materials_id;
				$this->user_materials_unique_name = $this->unique_name_identifier;
				$this->ParseFieldToDataRow();
			
		
				if(!$this->materialsQuantityTblObj->SaveMaterialsQuantity())
				{
					trigger_error(Logger::LOG_EVENT_CONST." Could not create the materials quantity ", E_USER_WARNING);
				}
			
			}
			
			$this->ParseDataRow();
		}
		
		return $insert_id;
	}
	
	public function EditMaterial($isSupplier = false, $justSupplier = false)
	{
		$result = NULL;
		$this->isLoaded = false;
				
		$this->ParseFieldToDataRow();

		if($this->materials_id > 0)
		{
			if(!$justSupplier)
			{
				$result = $this->materialsTblObj->SaveMaterial();
			}
			else
			{
				$result = !$justSupplier;
			}
			
			if($result && $isSupplier)
			{
				if($this->user_materials_id == 0)	
				{
					$this->user_materials_id = $this->materials_id;
				}
				
				$this->ParseFieldToDataRow();
				
				$result = $this->materialsQuantityTblObj->SaveMaterialsQuantity();
			}
		}
		
		return $result;
	}
	
	public function ValidateDivisibility()
	{
		$result = false; // not found
		
		$this->ParseFieldToDataRow();
		$this->divisibilityTblObj->dataRow['DIVISIBILITY_ID'] = (float)$this->divisibility_id;
		
		if($this->divisibilityTblObj->GetDivisibilityById() != NULL)
		{
			$result = true;
		}
		
		return $result;
	}
	
	public function ValidateUserDivisibility()
	{
		$result = false;
		
		$this->ParseFieldToDataRow();
		$this->divisibilityTblObj->dataRow['DIVISIBILITY_ID'] = (float)$this->user_divisibility_id;
		
		if($this->divisibilityTblObj->GetDivisibilityById() != NULL)
		{
			$result = true;
		}
		
		return $result;
	}

	public function ValidateUniqueIdentifier($needLoad = false, $unique_identifier = '')
	{
		$result = true; // not unique
		
		if(strlen($this->unique_name_identifier) < 1 && strlen($unique_identifier) > 0)
		{
			$this->unique_name_identifier = $unique_identifier;
		}
		elseif(strlen($this->unique_name_identifier) < 1)
		{
			return false;
		}

		$this->ParseFieldToDataRow();

		$this->dataRow = $this->materialsTblObj->ValidateUniqueIdentifier();
		
		if($this->dataRow == NULL)
		{
			$result = false;
		}
		
		if($needLoad) {
			if(!$result)
			{
				$this->isLoaded = false;
				$this->dataRow = NULL;
			}
			elseif($needLoad)
			{
				$this->isLoaded = true;
				$this->materialsTblObj->dataRow = $this->dataRow;
				$this->ParseDataRow();
			}
		}

		return $result;
	}
	
	private function ParseDataRow()
	{
		$this->materials_id = (float)$this->materialsTblObj->dataRow['MATERIALS_ID'];
		$this->materials_guid = $this->materialsTblObj->dataRow['GUID'];
		$this->materials_groups_id = (float)$this->materialsTblObj->dataRow['MATERIALS_GROUPS_ID'];
		$this->materials_groups_identifier = $this->materialsTblObj->dataRow['MATERIALS_GROUPS_IDENTIFIER'];
		$this->name = $this->materialsTblObj->dataRow['NAME'];
		$this->ord = (float)$this->materialsTblObj->dataRow['ORD'];
		$this->is_public = (bool)$this->materialsTblObj->dataRow['IS_PUBLIC'];
		$this->is_approved = (bool)$this->materialsTblObj->dataRow['IS_APPROVED'];
		$this->divisibility_id = (float)$this->materialsTblObj->dataRow['DIVISIBILITY_ID'];
		$this->original_countries_id = (float)$this->materialsTblObj->dataRow['ORIGINAL_COUNTRIES_ID'];
		$this->vendor_companies_id = (float)$this->materialsTblObj->dataRow['VENDOR_COMPANIES_ID'];
		$this->vendor_text = $this->materialsTblObj->dataRow['VENDOR_TEXT'];
		$this->common_www = $this->materialsTblObj->dataRow['COMMON_WWW'];
		$this->unique_name_identifier = $this->materialsTblObj->dataRow['UNIQUE_NAME_IDENTIFIER'];
		$this->comment = $this->materialsTblObj->dataRow['COMMENT'];
		
		//Suppliers MaterialsQuantity 
		$this->user_materials_quantity_id = $this->materialsQuantityTblObj->dataRow['MATERIALS_QUANTITY_ID'];
		$this->user_materials_id = $this->materialsQuantityTblObj->dataRow['MATERIALS_ID'];
		$this->user_supplier_companies_id = $this->materialsQuantityTblObj->dataRow['COMPANIES_ID'];
		$this->user_supplier_companies_name = $this->materialsQuantityTblObj->dataRow['COMPANIES_NAME'];
		$this->user_materials_unique_name = $this->materialsQuantityTblObj->dataRow['MATERIALS_UNIQUE_NAME'];
		$this->user_comment = $this->materialsQuantityTblObj->dataRow['COMMENT'];
		$this->user_divisibility_id = $this->materialsQuantityTblObj->dataRow['DIVISIBILITY_ID'];
		$this->user_quantity = $this->materialsQuantityTblObj->dataRow['QUANTITY'];
		
		$this->groups_id = $this->materialsGroupsTblObj->dataRow['MATERIALS_GROUPS_ID'];
		$this->groups_name = $this->materialsGroupsTblObj->dataRow['GROUPS_NAME'];
		
		$this->groups_unique_name = $this->materialsGroupsTblObj->dataRow['UNIQUE_NAME_IDENTIFIER'];
		
		$this->groups_parent = $this->materialsGroupsTblObj->dataRow['PARENT_GROUPS_ID'];
		$this->groups_parent1 = $this->materialsGroupsTblObj->dataRow['PARENT_GROUPS1_ID'];
		$this->groups_parent2 = $this->materialsGroupsTblObj->dataRow['PARENT_GROUPS2_ID'];
		$this->groups_parent3 = $this->materialsGroupsTblObj->dataRow['PARENT_GROUPS3_ID'];
		$this->groups_parent4 = $this->materialsGroupsTblObj->dataRow['PARENT_GROUPS4_ID'];
		
	}
	
	private function ParseFieldToDataRow()
	{
		$this->materialsTblObj->dataRow['MATERIALS_ID'] = (float)$this->materials_id;
		$this->materialsTblObj->dataRow['GUID'] = substr($this->materials_guid, 0, 36);
		$this->materialsTblObj->dataRow['MATERIALS_GROUPS_ID'] = (float)$this->materials_groups_id;
		$this->materialsTblObj->dataRow['MATERIALS_GROUPS_IDENTIFIER'] = $this->materials_groups_identifier;
		$this->materialsTblObj->dataRow['NAME'] = substr($this->name, 0, 200);
		$this->materialsTblObj->dataRow['ORD'] = (int)$this->ord;
		$this->materialsTblObj->dataRow['IS_PUBLIC'] = (int)$this->is_public;
		$this->materialsTblObj->dataRow['IS_APPROVED'] = (int)$this->is_approved;
		$this->materialsTblObj->dataRow['DIVISIBILITY_ID'] = (float)$this->divisibility_id;
		$this->materialsTblObj->dataRow['ORIGINAL_COUNTRIES_ID'] = (float)$this->original_countries_id;
		$this->materialsTblObj->dataRow['VENDOR_COMPANIES_ID'] = (float)$this->vendor_companies_id;
		$this->materialsTblObj->dataRow['VENDOR_TEXT'] = substr($this->vendor_text, 0, 200);
		$this->materialsTblObj->dataRow['COMMON_WWW'] = substr($this->common_www, 0, 200);
		$this->materialsTblObj->dataRow['UNIQUE_NAME_IDENTIFIER'] = (strlen($this->unique_name_identifier) > 1) ? substr($this->unique_name_identifier, 0, 75) : "empty";
		$this->materialsTblObj->dataRow['COMMENT'] = substr($this->comment, 0, 5000);
		
		//Suppliers MaterialsQuantity
		$this->materialsQuantityTblObj->dataRow['MATERIALS_QUANTITY_ID'] = (float)$this->user_materials_quantity_id;
		$this->materialsQuantityTblObj->dataRow['MATERIALS_ID'] = (float)$this->user_materials_id;
		$this->materialsQuantityTblObj->dataRow['COMPANIES_ID'] = (float)$this->user_supplier_companies_id;
		$this->materialsQuantityTblObj->dataRow['COMPANIES_NAME'] = substr($this->user_supplier_companies_name, 0, 200);
		$this->materialsQuantityTblObj->dataRow['MATERIALS_UNIQUE_NAME'] = (strlen($this->user_materials_unique_name) > 0) ? substr($this->user_materials_unique_name, 0, 75) : $this->unique_name_identifier ;
		$this->materialsQuantityTblObj->dataRow['COMMENT'] = substr($this->user_comment, 0, 5000);
		$this->materialsQuantityTblObj->dataRow['DIVISIBILITY_ID'] = (float)$this->user_divisibility_id;
		$this->materialsQuantityTblObj->dataRow['QUANTITY'] = (float)$this->user_quantity;
		
		$this->materialsGroupsTblObj->dataRow['MATERIALS_GROUPS_ID'] = (float)$this->groups_id;
		$this->materialsGroupsTblObj->dataRow['GROUPS_NAME'] = substr($this->groups_name, 0, 100);
		
		$this->materialsGroupsTblObj->dataRow['UNIQUE_NAME_IDENTIFIER'] = substr($this->groups_unique_name, 0, 200);

		$this->materialsGroupsTblObj->dataRow['PARENT_GROUPS_ID'] = (float)$this->groups_parent;
		$this->materialsGroupsTblObj->dataRow['PARENT_GROUPS1_ID'] = (float)$this->groups_parent1;
		$this->materialsGroupsTblObj->dataRow['PARENT_GROUPS2_ID'] = (float)$this->groups_parent2;
		$this->materialsGroupsTblObj->dataRow['PARENT_GROUPS3_ID'] = (float)$this->groups_parent3;
		$this->materialsGroupsTblObj->dataRow['PARENT_GROUPS4_ID'] = (float)$this->groups_parent4;

	}
}

/*
CREATE  TABLE IF NOT EXISTS `INNODB_TABLES`.`MATERIALS` (
  `MATERIALS_ID` BIGINT NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `CREATED_TIME` TIMESTAMP NULL DEFAULT NOW() ,
  `UPDATED_TIME` TIMESTAMP NULL DEFAULT NOW() ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL ,
  `MATERIALS_GROUPS_ID` BIGINT UNSIGNED NULL 
  `NAME` VARCHAR(200) NULL ,
  `ORD` INT UNSIGNED NULL ,
  `IS_PUBLIC` INT(1) UNSIGNED NULL ,
*/

?>