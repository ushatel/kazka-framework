<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");
  
  include_once("includes/DatabaseClasses/Db.StepsTbl.php");
  
/**
 * The Steps collects the projects related classes etc
 *
 * @package Steps.pkg
 */

/**
 * class Steps
 *
 * The main class of the Steps. Used for the operating with the projects, its steps, its materials and contractors 
 * 
 * @package Steps.pkg
 */
class Steps extends CommonClass
{
	public $stepsTblObj = NULL;
	
	public $projects_steps_id = 0;
	
	public $start_date = 0;
	public $end_date = 0;
	
	public $steps_type_id = 0;
	public $steps_type_name = "";
	
	public $projects_id = 0;
	
	public $step_name = "";
	public $comment = "";
	
	public $is_finished = false;
	
	public function __construct()
	{
		$this->stepsTblObj = new StepsTbl();
	}
	
	public function CreateStep()
	{
		$insert_id = 0;
		
		$this->ParseFieldToDataRow();
		$insert_id = $this->stepsTblObj->CreateStep();
		
		if($insert_id < 1)
		{
			trigger_error(Logger::LOG_EVENT_CONST." Could not create the step for the project id = ".$this->projects_id, E_USER_WARNING);
		}
		
		$this->ParseDataRow();
		
		return $this->projects_steps_id;
	}
	
	public function GetStepById()
	{
		$this->isLoaded = false;
		
		if($this->projects_steps_id > 0) {
			$this->ParseFieldToDataRow();
			$this->dataRow = $this->stepsTblObj->GetStepById();
			
			if($this->dataRow != NULL)
			{
				$this->ParseDataRow();
				
				$this->isLoaded = true;
			}						
		}
		
		return $this->isLoaded;
	}
	
	public function FinishStep()
	{
		$result = false;
		
		$this->ParseFieldToDataRow();
		
		$result = $this->stepsTblObj->FinishStep();
		
		if(!$result)
		{
			trigger_error(Logget::LOG_EVENT_CONST." Error saving the steps id='".$this->projects_steps_id."'");
		}
		
		return $result;
	}
	
	public function SaveStep()
	{
		$this->isLoaded = false;
		$result = false;
		
		$this->ParseFieldToDataRow();

		$result = $this->stepsTblObj->SaveStep();
		if(!$result)
		{
			trigger_error(Logger::LOG_EVENT_CONST." Error saving the steps id='".$this->projects_steps_id."'");
		}
		
		$this->ParseDataRow();
		
		return $result;
	}
	
	public function LoadProjectsSteps()
	{
		$this->ParseFieldToDataRow();

		return $this->stepsTblObj->LoadProjectsSteps();
	}
	
	private function ParseDataRow()
	{
		$this->projects_steps_id = $this->stepsTblObj->dataRow['PROJECTS_STEPS_ID'];
		$this->projects_id = $this->stepsTblObj->dataRow['PROJECTS_ID'];
		$this->sdate = strtotime($this->stepsTblObj->dataRow['START_TIME']);
		$this->edate = strtotime($this->stepsTblObj->dataRow['END_TIME']);
		$this->step_name = $this->stepsTblObj->dataRow['STEP_NAME'];
		$this->comment = $this->stepsTblObj->dataRow['COMMENT'];
		$this->is_public = (bool)$this->stepsTblObj->dataRow['IS_PUBLIC'];
		$this->steps_types_id = $this->stepsTblObj->dataRow['STEPS_TYPES_ID'];
		$this->steps_types_name = $this->stepsTblObj->dataRow['STEPS_TYPES_NAME'];
		$this->is_finished = (bool)$this->stepsTblObj->dataRow['IS_FINISHED'];
	}
	
	private function ParseFieldToDataRow()
	{
		$this->stepsTblObj->dataRow['PROJECTS_STEPS_ID'] = (float)$this->projects_steps_id;
		$this->stepsTblObj->dataRow['PROJECTS_ID'] = (float)$this->projects_id;
		$this->stepsTblObj->dataRow['START_TIME'] = (float)$this->sdate;
		$this->stepsTblObj->dataRow['END_TIME'] = (float)$this->edate;
		$this->stepsTblObj->dataRow['STEP_NAME'] = substr($this->step_name, 0, 200);
		$this->stepsTblObj->dataRow['COMMENT'] = substr($this->comment, 0, 5000);
		$this->stepsTblObj->dataRow['IS_PUBLIC'] = (int)$this->is_public;
		$this->stepsTblObj->dataRow['STEPS_TYPES_ID'] = (float)$this->steps_types_id;
		$this->stepsTblObj->dataRow['STEPS_TYPES_NAME'] = substr($this->steps_types_name, 0, 200);
		$this->stepsTblObj->dataRow['IS_FINISHED'] = (int)$this->is_finished;
	}
}

/*
CREATE  TABLE IF NOT EXISTS `INNODB_TABLES`.`PROJECTS_STEPS` (
  `PROJECTS_STEPS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT NULL ,
  `PROJECTS_ID` BIGINT UNSIGNED NULL ,
  `START_DATE` TIMESTAMP NULL ,
  `END_DATE` TIMESTAMP NULL ,
  `STEP_TYPES_ID` BIGINT NULL ,
  `STEP_TYPES_NAME` VARCHAR(200) NULL COMMENT '&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533;' ,
  `STEP_NAME` VARCHAR(200) NULL ,
*/

?>