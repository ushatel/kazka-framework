<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");    
  
  include_once("includes/DatabaseClasses/Db.ProjectsMaterialsQuantityTbl.php");
  include_once("includes/DatabaseClasses/Db.ProjectsLandmarksTbl.php");
  include_once("includes/DatabaseClasses/Db.ProjectsTbl.php");
  include_once("includes/DatabaseClasses/Db.CompaniesTbl.php");
  include_once("includes/DatabaseClasses/Db.StepsTbl.php");

/**
 * The Package collects the projects related classes etc
 *
 * @package Projects.pkg
 */

/**
 * class Projects
 *
 * The main class of the Projects. Used for the operating with the projects, its steps, its materials and contractors 
 * 
 * @package Projects.pkg
 */
class Projects extends CommonClass
{
	public $projectsTblObj = NULL;
	public $companiesTblObj = NULL;
	public $projectsLandmarksTblObj = NULL;
	public $projectsMaterialsQuantityTblObj = NULL;

	public $projects_id = 0;

	public $companies_id = 0;
	public $companies_name = "";	
	public $companies_identifier = "";
 	public $cities_id = 0;

	public $name = "";
	public $unique_name_identifier = "";
	public $type_name = "";
	public $sdate = 0;
	public $edate = 0;
	
	public $www = "";
	public $comment = "";
	
	public $owner_id = 0;	
	public $countries_id = 0;
	
	public $is_public = NULL;
	public $is_finished = false;
	
	public $country_name = "";
	public $city_id = 0;
	

	public $landmarks_text = "";
	public $landmarks_ldate = 0;
	public $landmarks_langs_id = 0;
	public $landmarks_is_public = false;
	public $landmarks_projects_id = 0; 
	public $landmarks_steps_id = 0;
	
		
	public $prjmaterials_suppliers_id = 0;
	public $prjmaterials_materials_id = 0;
	public $prjmaterials_divisibility_id = 0;
	public $prjmaterials_projects_id = 0;
	public $prjmaterials_projects_steps_id = 0;
	public $prjmaterials_request_time = 0;
	public $prjmaterials_fact_time = 0;
	public $prjmaterials_is_used = false;
	public $prjmaterials_quantity = 0;
	public $prjmaterials_ord = 0;
	public $prjmaterials_materials_unique_name = "";
	public $prjmaterials_comment = "";

	
	public $steps = NULL;
	
	public function __construct($needInit = true)
	{
		$this->sdate = time();
		$this->edate = time();
		$this->owner_id = Session::GetUserId();
		
		$this->isLoaded = false;
	
		if($needInit) 
		{
			$this->projectsTblObj = new ProjectsTbl();
			$this->projectsLandmarksTblObj = new ProjectsLandmarksTbl();
			$this->projectsMaterialsQuantityTblObj = new ProjectsMaterialsQuantityTbl();
		}
		
		parent::__construct();
	}
	
	public function AddMaterialsToProject()
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		
		if($result = $this->projectsMaterialsQuantityTblObj->SaveMaterialsQuantity())
			$this->ParseDataRow();
			
		return $result;	
	}
	
	public function LoadProjects($name = '', $offset = 0, $limit = 0, $order_by = "`NAME`")
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		$result = $this->projectsTblObj->LoadProjects('', $offset, $limit, $order_by);

		if($result != NULL && mysql_num_rows($result) > 0)
		{
			$rArray = array();

			while($row = mysql_fetch_array($result))
			{
				$this->companiesTblObj = new CompaniesTbl();
				$this->companiesTblObj->companies_id = (float)$row['COMPANIES_ID'];
				if($this->companiesTblObj->GetCompanyById() != NULL)
				{
					$row['COMPANY_NAME'] = $this->companiesTblObj->dataRow['NAME'];
					$row['COMPANY_IDENTIFIER'] = $this->companiesTblObj->dataRow['UNIQUE_NAME_IDENTIFIER'];//companies_identifier
				}
				
				array_push($rArray, $row);
			}
			
			$result = $rArray;			
		}

		return $result;
	}
	
	public function LoadUsersProjects($userId = 0)
	{
		$result = NULL;

		if($userId > 0)
			$this->owner_id = (float)$userId;
		
		$this->ParseFieldsToDataRow();
		$result = $this->projectsTblObj->LoadUsersProjects();
		
		return $result;
	}
		
	public function LoadCompaniesProjects($companiesId = 0)
	{
		$result = NULL;

		if($companiesId > 0)
		{
			$this->companies_id = (float)$companiesId;
		}
		
		$this->ParseFieldsToDataRow();
		$result = $this->projectsTblObj->LoadCompaniesProjects();
		
		return $result;
	}
		
	public function LoadLandmarks($limit = 0, $offset = 0, $order_by = "`NAME`")
	{
		$result = NULL;
		
		$this->landmarksTblObj = new ProjectsLandmarksTbl();
		
		$this->ParseFieldsToDataRow();
		$result = $this->projectsLandmarksTblObj->GetLandmarks($limit, $offset, $order_by);

		return $result;
	}

	public function ValidateUniqueIdentifier($needLoad = false)
	{
		$result = true; // not unique
		
		$this->ParseFieldsToDataRow();
		
		$this->dataRow = $this->projectsTblObj->ValidateUniqueIdentifier();
		
		if($this->dataRow == NULL)
		{
			$result = false;
		}
		
		if($needLoad)
		{
			if(!$result)
			{
				$this->isLoaded = false;
				$this->dataRow = NULL;
			}
			elseif($needLoad)
			{
				$this->isLoaded = true;
				$this->projectsTblObj->ValidateUniqueIdentifier();
				$this->ParseDataRow();
			}
		}
		
		return $result;
	}
	
	public function LoadProjectsSteps()
	{
		$this->steps = new StepsTbl();
		$this->steps->dataRow['PROJECTS_ID'] = $this->projects_id;
		
		return $this->steps->LoadProjectsSteps();
	}
	
	public function PublicateProject()
	{
		$this->ParseFieldsToDataRow();
		
		$result = $this->projectsTblObj->PublicateProject();
		
		return $result;
	}
	
	public function CreateLandmark()
	{
		$result = NULL;

		$this->ParseFieldsToDataRow();
		
		// Id or NULL		
		$result = $this->projectsLandmarksTblObj->CreateLandmark();
				
		return $result;
	}
	
	public function GetProjectById()
	{
		$this->isLoaded = false;
		
		if($this->projects_id > 0)
		{
			$this->ParseFieldsToDataRow();
			$this->dataRow = $this->projectsTblObj->GetProjectById();
			
			if($this->dataRow != NULL)
			{
				$this->ParseDataRow();
				
				$this->isLoaded = true;
			}
		}
		
		return $this->isLoaded;
	}
	
	public function CreateProject()
	{
		$this->isLoaded = false;
		$insert_id = 0;

		$this->ParseFieldsToDataRow();
		$insert_id = $this->projectsTblObj->CreateProject();
		
		if($insert_id < 1)
		{
			trigger_error(Logger::LOG_EVENT_CONST." Could not create the project ", E_USER_WARNING);
		}
		
		$this->ParseDataRow();
		
		return $insert_id;
	}
	
	public function DeleteProject()
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		
		$this->projectsAlbums = new UsersAlbumsTbl();
		$this->projectsAlbums->dataRow['OBJECTS_ID'] = (float)$this->projects_id;
		$this->projectsAlbums->dataRow['ENTITIES_ID'] = 4;
		if(($result = $this->projectsAlbums->DeleteAlbums()) !== NULL && mysql_num_rows($result) > 0)
		{
			$this->projectsFiles = new UsersFiles();
			$this->projectsFiles->DeleteFiles($result);
		}
		
		$this->projectsLandmarksTblObj = new ProjectsLandmarksTbl();
		$this->projectsLandmarksTblObj->dataRow['PROJECTS_ID'] = (float)$this->projects_id;
		$result = $this->projectsLandmarksTblObj->DeleteProjectsLandmarks();
		
		$this->projectsMaterialsQuantityTblObj = new ProjectsMaterialsQuantityTbl();
		$this->projectsMaterialsQuantityTblObj->dataRow['PROJECTS_ID'] = (float)$this->projects_id;
		$result = $this->projectsMaterialsQuantityTblObj->DeleteMaterialsByProjectsId();

		$result = $this->projectsTblObj->DeleteProject();
		
		return $result;
	}
	
	public function EditProject()
	{
		$this->isLoaded = false;
		$result = false;
		
		$this->ParseFieldsToDataRow();
		
		$result = $this->projectsTblObj->SaveProject();
		if(!$result)
		{
			trigger_error(Logger::LOG_EVENT_CONST." Could not edit the project id='".$this->projects_id."'", E_USER_WARNING);
		}
		
		$this->ParseDataRow();
				
		return $result;
	}
	
	private function ParseDataRow()
	{
		$this->projects_id = $this->projectsTblObj->dataRow['PROJECTS_ID'];
		$this->companies_id = $this->projectsTblObj->dataRow['COMPANIES_ID'];
		$this->countries_id = $this->projectsTblObj->dataRow['COUNTRIES_ID'];
		$this->owner_id = $this->projectsTblObj->dataRow['OWNER_ID'];
		$this->sdate = strtotime($this->projectsTblObj->dataRow['START_TIME']);
		$this->edate = strtotime($this->projectsTblObj->dataRow['END_TIME']);
		$this->name = $this->projectsTblObj->dataRow['NAME'];
		$this->unique_name_identifier = $this->dataRow['UNIQUE_NAME_IDENTIFIER'];
		$this->companies_id = (float)$this->dataRow['COMPANIES_ID'];
		$this->companies_name = $this->dataRow['COMPANIES_NAME'];
		$this->comment = $this->projectsTblObj->dataRow['COMMENT'];
		$this->www = $this->projectsTblObj->dataRow['WWW'];
		$this->is_public = (bool)$this->dataRow['IS_PUBLIC'];
		$this->is_finished = (bool)$this->dataRow['IS_FINISHED'];
		$this->updated_login = (float)$this->dataRow['UPDATED_LOGIN'];

		$this->landmarks_text =  $this->projectsLandmarksTblObj->dataRow['LANDMARKS_TEXT'];
		$this->landmarks_ldate = strtotime($this->projectsLandmarksTblObj->dataRow['LDATE']);
		$this->landmarks_langs_id = (float)$this->projectsLandmarksTblObj->dataRow['LANGS_ID'];
		$this->landmarks_is_public = (bool)$this->projectsLandmarksTblObj->dataRow['IS_PUBLIC'];
		$this->landmarks_projects_id = (float)$this->projectsLandmarksTblObj->dataRow['PROJECTS_ID'];
		$this->landmarks_steps_id = (float)$this->projectsLandmarksTblObj->dataRow['PROJECTS_STEPS_ID'];

		$this->prjmaterials_suppliers_id = (float)$this->projectsMaterialsQuantityTblObj->dataRow['COMPANIES_ID'];
		$this->prjmaterials_materials_id = (float)$this->projectsMaterialsQuantityTblObj->dataRow['MATERIALS_ID'];
		$this->prjmaterials_divisibility_id = (float)$this->projectsMaterialsQuantityTblObj->dataRow['DIVISIBILITY_ID'];
		$this->prjmaterials_projects_id = (float)$this->projectsMaterialsQuantityTblObj->dataRow['PROJECTS_ID'];
		$this->prjmaterials_projects_steps_id = (float)$this->projectsMaterialsQuantityTblObj->dataRow['PROJECTS_STEPS_ID'];
		$this->prjmaterials_request_time = strtotime($this->projectsMaterialsQuantityTblObj->dataRow['REQUEST_TIME']);
		$this->prjmaterials_fact_time = strtotime($this->projectsMaterialsQuantityTblObj->dataRow['FACT_TIME']);
		$this->prjmaterials_is_used = (bool) $this->projectsMaterialsQuantityTblObj->dataRow['IS_USED'];
		$this->prjmaterials_quantity = (int) $this->projectsMaterialsQuantityTblObj->dataRow['QUANTITY'];
		$this->prjmaterials_ord = (int) $this->projectsMaterialsQuantityTblObj->dataRow['ORD'];
		$this->prjmaterials_materials_unique_name = $this->projectsMaterialsQuantityTblObj->dataRow['MATERIALS_UNIQUE_NAME'];
		$this->prjmaterials_comment = $this->projectsMaterialsQuantityTblObj->dataRow['COMMENT'];
	}
	
	private function ParseFieldsToDataRow()
	{
		$this->projectsTblObj->dataRow['PROJECTS_ID'] = (float)$this->projects_id;
		$this->projectsTblObj->dataRow['COMPANIES_ID'] = (float)$this->companies_id;
		$this->projectsTblObj->dataRow['COUNTRIES_ID'] = (float)$this->countries_id;
		$this->projectsTblObj->dataRow['OWNER_ID'] = (float)$this->owner_id;
		$this->projectsTblObj->dataRow['START_TIME'] = (float)$this->sdate;
		$this->projectsTblObj->dataRow['END_TIME'] = (float)$this->edate;
		$this->projectsTblObj->dataRow['NAME'] = substr($this->name, 0, 200);
		$this->projectsTblObj->dataRow['UNIQUE_NAME_IDENTIFIER'] = substr($this->unique_name_identifier, 0, 75);
		$this->projectsTblObj->dataRow['COMMENT'] = substr($this->comment, 0, 5000);
		$this->projectsTblObj->dataRow['WWW'] = substr($this->www, 0, 100);
		$this->projectsTblObj->dataRow['COMPANIES_NAME'] = substr($this->companies_name, 0, 200);
		$this->projectsTblObj->dataRow['COMPANIES_ID'] = (float)$this->companies_id;
		$this->projectsTblObj->dataRow['IS_PUBLIC'] = $this->is_public;
		$this->projectsTblObj->dataRow['IS_FINISHED'] = (int)$this->is_finished;
		$this->projectsTblObj->dataRow['UPDATED_LOGIN'] = (float)$this->updated_login;

		$this->projectsLandmarksTblObj->dataRow['LANDMARKS_TEXT'] = substr($this->landmarks_text,0,200);
		$this->projectsLandmarksTblObj->dataRow['LDATE'] = (float)$this->landmarks_ldate;
		$this->projectsLandmarksTblObj->dataRow['LANGS_ID'] = (float)$this->landmarks_langs_id;
		$this->projectsLandmarksTblObj->dataRow['IS_PUBLIC'] = (int) $this->landmarks_is_public;
		$this->projectsLandmarksTblObj->dataRow['PROJECTS_ID'] = (float) $this->landmarks_projects_id;

		$this->projectsLandmarksTblObj->dataRow['PROJECTS_STEPS_ID'] = (float)$this->landmarks_steps_id;

		$this->projectsMaterialsQuantityTblObj->dataRow['COMPANIES_ID'] = (float) $this->prjmaterials_suppliers_id;
		$this->projectsMaterialsQuantityTblObj->dataRow['MATERIALS_ID'] = (float) $this->prjmaterials_materials_id;
		$this->projectsMaterialsQuantityTblObj->dataRow['PROJECTS_ID'] = (float) $this->prjmaterials_projects_id;
		$this->projectsMaterialsQuantityTblObj->dataRow['DIVISIBILITY_ID'] = (float) $this->prjmaterials_divisibility_id;
		$this->projectsMaterialsQuantityTblObj->dataRow['PROJECTS_STEPS_ID'] = (float) $this->prjmaterials_projects_steps_id;
		$this->projectsMaterialsQuantityTblObj->dataRow['REQUEST_TIME'] = (float) $this->prjmaterials_request_time;
		$this->projectsMaterialsQuantityTblObj->dataRow['FACT_TIME'] = (float) $this->prjmaterials_fact_time;
		$this->projectsMaterialsQuantityTblObj->dataRow['IS_USED'] = (int)$this->prjmaterials_is_used;
		$this->projectsMaterialsQuantityTblObj->dataRow['QUANTITY'] = (int)$this->prjmaterials_quantity;
		$this->projectsMaterialsQuantityTblObj->dataRow['ORD'] = (int)$this->prjmaterials_ord;
		$this->projectsMaterialsQuantityTblObj->dataRow['MATERIALS_UNIQUE_NAME'] = substr($this->unique_name_identifier, 0, 75);
		$this->projectsMaterialsQuantityTblObj->dataRow['COMMENT'] = substr($this->prjmaterials_comment, 0, 5000);

	}
}

/*
CREATE  TABLE IF NOT EXISTS `build`.`PROJECTS` (
  `PROJECTS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `CREATED_TIME` TIMESTAMP NULL DEFAULT NOW() ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL ,
  `COMPANIES_ID` BIGINT UNSIGNED NULL ,
  `COUNTRIES_ID` BIGINT UNSIGNED NULL ,
  `START_TIME` TIMESTAMP NULL ,
  `END_TIME` TIMESTAMP NULL ,
  `NAME` VARCHAR(200) NULL ,
  PRIMARY KEY (`PROJECTS_ID`) ,
  UNIQUE INDEX `PROJECTS_ID_UNIQUE` (`PROJECTS_ID` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = '&#65533;&#65533;&#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;'
*/
?>
