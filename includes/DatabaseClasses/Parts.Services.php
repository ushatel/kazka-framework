<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");    
  
  include_once("includes/DatabaseClasses/Db.ServicesTbl.php");
  include_once("includes/DatabaseClasses/Db.StepsTbl.php");
  include_once("includes/DatabaseClasses/Db.CompaniesTbl.php");
  include_once("includes/DatabaseClasses/Db.DivisibilityTbl.php");
  include_once("includes/DatabaseClasses/Db.MaterialsGroupsTbl.php");
  include_once("includes/DatabaseClasses/Db.MaterialsQuantityTbl.php");
  include_once("includes/DatabaseClasses/Db.MaterialsSuppliersStockTbl.php");
  include_once("includes/DatabaseClasses/Db.ProjectsMaterialsQuantityTbl.php");
  include_once("includes/DatabaseClasses/Db.CountriesTbl.php");

/**
 * The Package collects the services related classes etc
 *
 * @package Services.pkg
 */

/**
 * class Services
 *
 * The main class of the Services. Used for the operating with services, contractors and so on.
 * 
 * @package Services.pkg
 */
class Services extends CommonClass
{
	private $servicesTblObj = NULL;

/*	private $projectsTblObj = NULL;
	private $companiesTblObj = NULL;
	private $materialsGroupsTblObj = NULL;
	private $divisibilityTblObj = NULL;
	private $materialsQuantityTblObj = NULL;
	private $materialsSuppliersStockTblObj = NULL;
	private $countriesTblObj = NULL;	
	*/
	
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
		/*
		$this->materialsTblObj = new MaterialsTbl();
		$this->projectsTblObj = new ProjectsTbl();
		$this->companiesTblObj = new CompaniesTbl();
		$this->divisibilityTblObj = new DivisibilityTbl();
		$this->materialsQuantityTblObj = new MaterialsQuantityTbl();
		$this->materialsSuppliersStockTblObj = new MaterialsSuppliersStockTbl();
		$this->countriesTblObj = new CountriesTbl();
		$this->materialsGroupsTblObj = new MaterialsGroupsTbl();
		*/

		$this->servicesTblObj = new ServiceTbl();

		parent::__construct();
	}
	
	public function LoadServices()
	{
		$result = NULL;
		$this->isLoaded = false;
		
		$this->ParseFieldToDataRow();
		
		$result = $this->serviceTblObj->LoadService();
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
	
	public function CreateService()
	{
		$result = NULL;
		
		

		return $result;
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

		$this->dataRow = $this->servicesTblObj->ValidateUniqueIdentifier();
		
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
				$this->servicesTblObj->dataRow = $this->dataRow;
				$this->ParseDataRow();
			}
		}

		return $result;
	}
	
	private function ParseDataRow()
	{
  
		$this->services_id = (float)$this->servicesTblObj->dataRow['MATERIALS_ID'];
		$this->services_guid = $this->servicesTblObj->dataRow['GUID'];
		$this->title = substr($this->servicesTblObj->dataRow['TITLE'], 0, 200);
		$this->bidask = (int)$this->servicesTblObj->dataRow['BIDASK'];
		$this->hands_up = (float)$this->servicesTblObj->dataRow['HANDS_UP'];//HANDS_UP
		$this->hands_down = (float)$this->servicesTblObj->dataRow['HANDS_DOWN'];
		$this->phone = substr($this->servicesTblObj->dataRow['PHONE'], 0, 45);
		$this->price = (float)$this->servicesTblObj->dataRow['PRICE'];
		$this->email = substr($this->servicesTblObj->dataRow['EMAIL'], 0, 200);
		$this->user_agent = substr($this->servicesTblObj->dataRow['AGENT'], 0, 200);
		$this->ip = substr($this->servicesTblObj->dataRow['IP'], 0, 45);
		$this->unique_name_identifier = substr($this->servicesTblObj->dataRow['UNIQUE_NAME_IDENTIFIER'], 0, 200);
		$this->original_countries_id = (float)$this->servicesTblObj->dataRow['ORIGINAL_COUNTRIES_ID'];
		$this->location = substr($this->servicesTblObj->dataRow['LOCATION'], 0, 300);
		$this->description = substr($this->servicesTblObj->dataRow['DESCRIPTION'], 0, 5000);
		
	}
	
	private function ParseFieldToDataRow()
	{
		
		$this->servicesTblObj->dataRow['SERVICES_ID'] = (float)$this->services_id;
	    $this->servicesTblObj->dataRow['GUID'] = $this->services_guid;
	    $this->servicesTblObj->dataRow['TITLE'] = substr($this->title, 0, 200);
		$this->servicesTblObj->dataRow['HANDS_UP'] = (int)$this->hands_up;
		$this->servicesTblObj->dataRow['HANDS_DOWN'] = (int)$this->hands_down;
		$this->servicesTblObj->dataRow['PHONE'] = substr($this->phone,0, 45);
		$this->servicesTblObj->dataRow['PRICE'] = (float)$this->price;
		$this->servicesTblObj->dataRow['EMAIL'] = substr($this->email, 0, 200);
		$this->servicesTblObj->dataRow['AGENT'] = substr($this->user_agent, 0, 200);
		$this->servicesTblObj->dataRow['IP'] = substr($this->ip, 0, 45);
		$this->servicesTblObj->dataRow['UNIQUE_NAME_IDENTIFIER'] = substr($this->unique_name_identifier, 0, 200);
		$this->servicesTblObj->dataRow['ORIGINAL_COUNTRIES_ID'] = (float)$this->original_countries_id;
		$this->servicesTblObj->dataRow['LOCATION'] = substr($this->location, 0, 300);
		$this->servicesTblObj->dataRow['DESCRIPTION'] = substr($this->description, 0, 5000);

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