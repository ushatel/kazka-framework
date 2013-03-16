<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");    
  include_once("includes/DatabaseClasses/Db.NewsTbl.php");
  include_once("includes/DatabaseClasses/Db.ContractorsTbl.php");

/**
 * The Package collects the Users related classes etc
 *
 * @package News
 */

/**
 * class News
 *
 * The main class of the News. Used for the operating with the news
 * 
 * @package News.pkg
 */
class PNews extends CommonClass
{

	public $news_id = 0;
	public $unique_name_identifier = "";
	public $news_name = "";
	public $news_short_text = "";
	public $news_text = "";
	public $companies_id = 0;
	public $objects_id = 0;
	public $entities_id = 0;
	public $langs_id = 0;
	public $is_active = false;
	public $is_public = NULL;
	public $countries_id = 0;
	public $publication_time = 0;
	public $updated_login = 0;
	
	public $dataRow = array();
	
	private $newsTblObj = NULL;
	
	/**
	 * Class Users
	 */
	public function __construct() 
	{
		$this->newsTblObj = new NewsTbl();
	}
	
	public function CreateNews()
	{
		$result = NULL;

		//$this->newsTblObj = new NewsTbl();		
		$this->ParseFieldsToDataRow();
		
		$newsId = $this->newsTblObj->SaveNews();
		if($newsId > 0)
		{
			$result = $newsId;
		}

		return $result;		
	}
	
	public function RowsCount()
	{
		$result = NULL;
		
		$result = $this->newsTblObj->RowsCount();
		
		return $result;
	}
	
	public function DeleteNews()
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		
		$result = $this->newsTblObj->DeleteNews();
		
		return $result;
	}
	
	public function SaveNews()
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		
		$result = $this->newsTblObj->SaveNews();
		
		return $result;
	}
	
	public function LoadNewsById($id = 0)
	{
		$this->isLoaded = false;
		
		if($id > 0)
		{
			$this->news_id = (float)$id;
		}
		
		$this->ParseFieldsToDataRow();
		
		if($this->isLoaded = $this->newsTblObj->LoadNewsById())
		{
			$this->ParseDataRow();
		}
		
		return $this->isLoaded;
	}
	
	public function LoadNews($name = "", $limit = 0, $offset = 0, $order_by = '`PUBLICATION_TIME`')
	{
		$result = NULL;
		$this->isLoaded = false;

		$this->ParseFieldsToDataRow();
		
		$result = $this->newsTblObj->LoadNews($name, $limit, $offset, $order_by);

		return $result;
	}
	
	public function ValidateUniqueIdentifier($needLoad = false)
	{
		$result = true; //Not unique

		$this->ParseFieldsToDataRow();

		$result = $this->newsTblObj->ValidateUniqueIdentifier();

		if($needLoad)
		{
			if(!$result)
			{
				$this->isLoaded = false;
				$this->newsTblObj->dataRow = NULL;
			}
			elseif($needLoad)
			{
				$this->isLoaded = true;
				$this->ParseDataRow();
			}
		}
		
		return $result;				
	}
	
	public function CreateUser() 
	{
		$result = ""; 
		
		$newUserId = 0;
		
		$this->confirmDate = $this->SetConfirmationTime(); // Add 3 days to current date

		$this->ParseFieldsToDataRow();
		$this->usersTblObj->dataRow = $this->dataRow;
		
		$newUserId = $this->usersTblObj->InsertUser();
		if($newUserId > 0) 
		{
			$this->usersId = $this->dataRow["USERS_ID"] = $newUserId;
			$this->contractorsTblObj->dataRow = $this->dataRow;
			$this->contractorsTblObj->CreateContractor();
		}
		else 
		{
			trigger_error(Logger::LOG_EVENT_CONST." Could not create new user", E_USER_WARNING);
		}
		
		return $newUserId;
	}
	
		
	/**
	 * Sets the users SID data
	 */
	public function SetUsersSIDByLogin()
	{
		$this->ParseFieldsToDataRow();
		
		$this->usersTblObj->dataRow = $this->dataRow;
		
		return $this->usersTblObj->SetUsersSIDbyLogin();
	}

	private function ParseDataRow() 
	{

		$this->updated_login = $this->newsTblObj->dataRow['UPDATED_LOGIN'];
		$this->news_id = $this->newsTblObj->dataRow['NEWS_ID'];
		$this->guid = $this->newsTblObj->dataRow['GUID'];
		$this->unique_name_identifier = $this->newsTblObj->dataRow['UNIQUE_NAME_IDENTIFIER'];
		$this->news_name = $this->newsTblObj->dataRow['NEWS_NAME'];
		$this->news_short_text = $this->newsTblObj->dataRow['NEWS_SHORT_TEXT'];
		$this->news_text = $this->newsTblObj->dataRow['NEWS_TEXT'];
		$this->companies_id = $this->newsTblObj->dataRow['COMPANIES_ID'];
		$this->objects_id = $this->newsTblObj->dataRow['OBJECTS_ID'];
		$this->entities_id = $this->newsTblObj->dataRow['ENTITIES_ID'];
		$this->langs_id = $this->newsTblObj->dataRow['LANGS_ID'];
		$this->is_active = (bool)$this->newsTblObj->dataRow['IS_ACTIVE'];
		$this->is_public = (bool)$this->newsTblObj->dataRow['IS_PUBLIC'];
		$this->countries_id = $this->newsTblObj->dataRow['COUNTRIES_ID'];
		$this->publication_time = (is_string($this->newsTblObj->dataRow['PUBLICATION_TIME']) ? strtotime($this->newsTblObj->dataRow['PUBLICATION_TIME']) : $this->newsTblObj->dataRow['PUBLICATION_TIME']);

	}
	
	private function ParseFieldsToDataRow()
	{
	
		$this->newsTblObj->dataRow['UPDATED_LOGIN'] = (float)$this->updated_login;
		$this->newsTblObj->dataRow['NEWS_ID'] = (float)$this->news_id;
		$this->newsTblObj->dataRow['GUID'] = substr($this->guid, 0, 36);
		$this->newsTblObj->dataRow['UNIQUE_NAME_IDENTIFIER'] = substr($this->unique_name_identifier, 0, 150);
		$this->newsTblObj->dataRow['NEWS_NAME'] = substr($this->news_name, 0, 150);
		$this->newsTblObj->dataRow['NEWS_SHORT_TEXT'] = substr($this->news_short_text, 0, 500);
		$this->newsTblObj->dataRow['NEWS_TEXT'] = substr($this->news_text, 0, 5000);
		$this->newsTblObj->dataRow['COMPANIES_ID'] = (float)$this->companies_id;
		$this->newsTblObj->dataRow['OBJECTS_ID'] = (float)$this->objects_id;
		$this->newsTblObj->dataRow['ENTITIES_ID'] = (float)$this->entities_id;
		$this->newsTblObj->dataRow['LANGS_ID'] = (float)$this->langs_id;
		$this->newsTblObj->dataRow['IS_ACTIVE'] = (int)	$this->is_active;
		$this->newsTblObj->dataRow['IS_PUBLIC'] = $this->is_public;
		$this->newsTblObj->dataRow['COUNTRIES_ID'] = (float)$this->countries_id;
		$this->newsTblObj->dataRow['PUBLICATION_TIME'] = (float)$this->publication_time;
	}

	
}
//build.USERS

/*
CREATE  TABLE IF NOT EXISTS `build`.`USERS` (
  `USERS_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL ,
  `LOGIN` VARCHAR(50) NULL ,
  `PASSWORD` VARCHAR(50) NULL ,
  `EMAIL` VARCHAR(45) NULL ,
  `LOGIN_ATTEMPTS` INT NULL DEFAULT 0 ,
  `LAST_LOGIN_TIME` TIMESTAMP NULL ,
  `LOCK_TIME` TIMESTAMP NULL ,
  `ACTIVE` TINYINT(4)  NULL DEFAULT 0 ,
   `CONFIRM_DATE` TIMESTAMP NULL ,
  `CONFIRM_TEXT` VARCHAR(100) NULL ,
  PRIMARY KEY (`USERS_ID`) ,
  UNIQUE INDEX `USERS_ID_UNIQUE` (`USERS_ID` ASC) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
*/

?>