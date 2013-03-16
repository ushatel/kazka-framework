<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");    
  include_once("includes/DatabaseClasses/Db.UsersTbl.php");
  include_once("includes/DatabaseClasses/Db.UsersFriendsTbl.php");
  include_once("includes/DatabaseClasses/Db.ContractorsTbl.php");

/**
 * The Package collects the Users related classes etc
 *
 * @package Users
 */

/**
 * class Users
 *
 * The main class of the Users. Used for the operating with the users
 * 
 * @package Users.pkg
 */
class Users extends CommonClass
{

	public $login = "";
	public $password = "";
	public $email = "";
	public $loginAttempts = 0;
	public $lastLoginTime = 0;
	public $lockTime = 0;
	public $active = false;
	public $confirmDate = 0;
	public $confirmText = "";
	public $usersId = 0;
	public $SID = "";
	public $companiesId = 0;
	public $countryId = 0;
	public $cityId = 0;
	public $firstName = "";
	public $secondName = "";
	public $thirdName = "";
	public $email2 = "";
	public $public_email = "";
	public $companyName = "";
	public $description = "";
	public $address = "";
	public $phone = "";
	public $phone2 = "";
	
	public $ip_current = '';
	public $ip_last = '';
		
	public $friends_primary_id = 0;
	public $friends_secondary_id = 0;
	public $friends_primary_company_id = 0;
	public $friends_secondary_company_id = 0;
	
	public $dataRow = array();
	
	/**
	 * Драйвери доступу до даних
	 * Має реалізувати інтерфейс - IDataConnector
	 */
	private $usersTblObj = NULL;
	private $contractorsTblObj = NULL;
	private $friendsTblObj = NULL;
	
	public $friendsArray = array();
	
	/**
	 * Class Users
	 */
	public function __construct() 
	{
		$this->usersTblObj = new UsersTbl();
		$this->contractorsTblObj = new ContractorsTbl();
		$this->friendsTblObj = new UsersFriendsTbl();
		
		$this->ip_current = Request::$ip;
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
	
	public function ValidateUser($username, $password)
	{
		$result = false;
		
		$this->login = $username;
		$this->password = $password;
		
		$this->ParseFieldsToDataRow();
		$this->usersTblObj->dataRow = $this->dataRow;
		
		$result = $this->usersTblObj->ValidateUser();
		$this->dataRow = $this->usersTblObj->dataRow;
		$this->ParseDataRow();
		
		return $result;
	}
		
	public function ActivateUser() 
	{
		$result = false;
		
		$this->active = true;
		$this->confirmDate = time();
		
		$this->ParseFieldsToDataRow();
		$this->usersTblObj->dataRow = $this->dataRow;
		
		$result = $this->usersTblObj->ActivateUser();
		
		return $result;
	}
	
	public function SetConfirmationTime()
	{
		return $this->confirmDate = time() + (3 * 24 * 60 * 60);
	}
	
	public function GetConfirmationSecurityCode()
	{
		$security_code = Security::CommonHash($this->login."<^>".$this->email."<^>".($this->confirmDate - (1 * 24 * 60 * 60)));
		return $security_code;
	}
	
	public function CheckConfirmationVariable ($__SCValue)
	{
		return $this->GetConfirmationSecurityCode() == $__SCValue;
	}
		
	/**
	 * Завантажити користувачів
	 */
	public function LoadUsers($limit = 0, $offset = 0, $order_by = '`LOGIN`')
	{
		$result = NULL;
		
		$this->ParseFieldsToDataRow();
		$result = $this->usersTblObj->LoadUsers($limit, $offset, $order_by);

		return $result;
	}	
	
	/**
	 * Load user by ID
	 */
	public function LoadUserById ($idUser) 
	{		
		$this->dataRow = $this->usersTblObj->LoadUserById($idUser);
		
		if($this->dataRow != NULL)
		{
			$this->isLoaded = true;
			$this->ParseDataRow();
		}
		else
		{
			$this->isLoaded = false;
			$this->dataRow = NULL;
		}

		return $this->dataRow;
	}

	/**
	 * Load user by Login
	 */
	public function LoadUserByLogin ($loginUser)
	{
		$this->dataRow = $this->usersTblObj->LoadUser($loginUser);

		if($this->dataRow != NULL)
		{
			$this->isLoaded = true;
			$this->ParseDataRow();
		}
		else
		{
			$this->isLoaded = false;
			$this->dataRow = NULL;
		}

		return $this->dataRow;
	}

	/**
	 * Gets the user by SID
	 */
	public function LoadUserBySID ($SID)
	{
		// !!! На майбутнє необхідно вдосконалити перевірку SID. 
		$this->dataRow = $this->usersTblObj->LoadUserBySID($SID);
		$this->ParseDataRow();

		return $this->dataRow;
	}
	
	/**
	 * Gets the friends by UsersId
	 */
	public function LoadFriends($usersId, $limit = 0, $offset = 0)
	{
		$result = NULL;
	
		$this->ParseFieldsToDataRow();		
		$result = $this->friendsTblObj->LoadPrimaryFriends();
		
		while($row = mysql_fetch_array($result))
		{
			$friend = new Users();
			$friend->LoadUserById((float)$row['SECONDARY_ID']);
			
			array_push($this->friendsArray, $friend);
		}
		
		return $result;
	}
	
	public function SaveUser()
	{
		$this->ParseFieldsToDataRow();
		$this->usersTblObj->dataRow = $this->dataRow;
		
		return $this->usersTblObj->SaveUser();
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
		$this->friends_primary_id = $this->friendsTblObj->dataRow['PRIMARY_ID'] ;
		$this->friends_secondary_id = $this->friendsTblObj->dataRow['SECONDARY_ID'] ;
		$this->friends_primary_company_id = $this->friendsTblObj->dataRow['PRIMARY_COMPANIES_ID'] ;
		$this->friends_secondary_company_id = $this->friendsTblObj->dataRow['SECONDARY_COMPANIES_ID'] ;
	
		$this->login = $this->dataRow['LOGIN'];
		$this->password = $this->dataRow['PASSWORD'];
		$this->email = $this->dataRow['EMAIL'];
		$this->loginAttempts = $this->dataRow['LOGIN_ATTEMPTS'];
		$this->lastLoginTime = $this->dataRow['LAST_LOGIN_TIME'];
		$this->lockTime = $this->dataRow['LOCK_TIME'];
		$this->active = $this->dataRow['ACTIVE'];
		$this->confirmDate = (is_string($this->dataRow['CONFIRM_DATE']) ? strtotime($this->dataRow['CONFIRM_DATE']) : $this->dataRow['CONFIRM_DATE']);
		$this->confirmText = $this->dataRow['CONFIRM_TEXT'];
		$this->SID = $this->dataRow['SID'];
		$this->ip_current = $this->dataRow['IP_CURRENT'];
		$this->ip_last = $this->dataRow['IP_LAST'];
		
		$this->usersId = $this->dataRow['USERS_ID'];
		$this->companiesId = $this->dataRow['COMPANIES_ID'];
		$this->countryId = $this->dataRow['COUNTRY_ID'];
		$this->cityId = $this->dataRow['CITY_ID'];
		$this->firstName = $this->dataRow['FIRST_NAME'];
		$this->secondName = $this->dataRow['SECOND_NAME'];
		$this->email2 = $this->dataRow['EMAIL2'];	
		$this->public_email = $this->dataRow['PUBLIC_EMAIL'];
		$this->avatar_files_id = $this->dataRow['AVATAR_FILES_ID'];
		
		$this->companyName = $this->dataRow['COMPANY_NAME'];
		$this->address = $this->dataRow['ADDRESS'];
		$this->description = $this->dataRow['DESCRIPTION'];
		$this->phone = $this->dataRow['PHONE'];
		$this->phone2 = $this->dataRow['PHONE2'];
		
	}
	
	private function ParseFieldsToDataRow()
	{
		$this->dataRow['LOGIN'] = substr($this->login, 0, 50);
		$this->dataRow['PASSWORD'] = substr($this->password, 0, 50);
		$this->dataRow['EMAIL'] = substr($this->email, 0, 45);
		$this->dataRow['LOGIN_ATTEMPTS'] = (int)$this->loginAttempts;
		$this->dataRow['LAST_LOGIN_TIME'] = (int)$this->lastLoginTime;
		$this->dataRow['LOCK_TIME'] = (int)$this->lockTime;
		$this->dataRow['ACTIVE'] = (int)$this->active;
		$this->dataRow['CONFIRM_DATE'] = (is_string($this->confirmDate) ? strtotime($this->confirmDate) : $this->confirmDate);
		$this->dataRow['CONFIRM_TEXT'] = substr($this->confirmText, 0, 100);
		$this->dataRow['SID'] = substr($this->SID, 0, 100);
		
		$this->dataRow['USERS_ID'] = $this->usersId;
		$this->dataRow['COMPANIES_ID'] = $this->companiesId;
		$this->dataRow['COUNTRY_ID'] = $this->countryId;
		$this->dataRow['CITY_ID'] = $this->cityId;
		$this->dataRow['FIRST_NAME'] = $this->firstName;
		$this->dataRow['SECOND_NAME'] = $this->secondName;
		$this->dataRow['EMAIL2'] = $this->email2;
		$this->dataRow['PUBLIC_EMAIL'] = substr($this->public_email, 0, 50);
		$this->dataRow['ADDRESS'] = substr($this->address, 0, 200);
		$this->dataRow['AVATAR_FILES_ID'] = (float)$this->avatar_files_id;
		$this->dataRow['IP_CURRENT'] = substr($this->ip_current, 0, 50);
		$this->dataRow['PHONE'] = substr($this->phone, 0, 50);
		$this->dataRow['PHONE2'] = substr($this->phone2, 0, 50);
		$this->dataRow['DESCRIPTION'] = substr($this->description, 0, 1000);
		$this->dataRow['IP_LAST'] = substr($this->ip_last, 0, 50);
		
		$this->friendsTblObj->dataRow['PRIMARY_ID'] = $this->friends_primary_id;
		$this->friendsTblObj->dataRow['SECONDARY_ID'] = $this->friends_secondary_id;
		$this->friendsTblObj->dataRow['PRIMARY_COMPANIES_ID'] = $this->friends_primary_companies_id;
		$this->friendsTblObj->dataRow['SECONDARY_COMPANIES_ID'] = $this->friends_secondary_companies_id;
				
		//$this->companyName = $this->dataRow['COMPANY_NAME'];		
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