<?php

  if (@preg_match("/includes/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/DatabaseClasses/Db.CommonClass.php");    
  include_once("includes/DatabaseClasses/Db.CitiesTbl.php");
  include_once("includes/DatabaseClasses/Db.MessagesTbl.php");

/**
 * The Package collects the message related classes etc
 *
 * @package Messages.pkg
 */

/**
 * class Messages
 *
 * The main class of the Messages. Used for the operating with the messages
 * 
 * @package Messages.pkg
 */
class Messages extends CommonClass
{
	public $messagesTblObj = NULL;

	public $users_messages_id = 0;

	public $sender_id = 0;
	public $sender_name = "";
	public $sender_ip = "";
	public $receiver_id = 0;
	public $receiver_name = "";

	public $message_title = "";
	public $message_body = "";
	public $unread = true;
	
	function __construct()
	{
		$this->messagesTblObj = new MessagesTbl();
		
		$this->sender_ip = Request::$ip;
	}
	
		
	/**
	 * Створення нової компанії
	 */
	public function CreateCompany()
	{
		$newId = 0;
		
		// Validate City and Create new if not exists
		$this->citiesTblObj->dataRow['COUNTRIES_ID'] = $this->countries_id;
		$this->citiesTblObj->dataRow['NAME'] = $this->city_name;

		$this->citiesTblObj->CreateCity(); // завжди вірно!
		$this->citiesTblObj->cities_id = $this->citiesTblObj->dataRow['CITIES_ID'];

		$this->ParseFieldsToDataRow();
		$this->companiesTblObj->dataRow = $this->dataRow;		
		$newId = $this->companiesTblObj->CreateCompany();
		
		if($newId > 0)
		{
			$this->companies_id = $newId;
		}
		else
		{
			trigger_error(Logger::LOG_EVENT_CONST." could not create the new company. ".print_r($this->dataRow, true), E_USER_WARNING);
		}
		
		return $this->companies_id;
	}

	public function CreateMessage()
	{
		$result = 0;
		$this->ParseFieldsToDataRow();		

		$result = $this->messagesTblObj->CreateMessage();

		return $result;
	}
	
	private function ParseDataRow() 
	{
		$this->users_messages_id = (float)$this->messagesTblObj->dataRow['USERS_MESSAGES_ID'];
		$this->sender_id = (float)$this->messagesTblObj->dataRow['SENDER_USERS_ID'];
		$this->sender_name = substr($this->messagesTblObj->dataRow['SENDER_USERS_NAME'], 0, 45);
		$this->sender_ip = substr($this->messagesTblObj->dataRow['SENDER_IP'], 0, 50);
		$this->receiver_id = (float)$this->messagesTblObj->dataRow['RECEIVER_USERS_ID'];
		$this->receiver_name = substr($this->messagesTblObj->dataRow['RECEIVER_USERS_NAME'], 0, 45);

		$this->message_title = substr($this->messagesTblObj->dataRow['MESSAGE_TITLE'], 0, 100);
		$this->message_body = substr($this->messagesTblObj->dataRow['MESSAGE_BODY'], 0, 5000);
		$this->unread = (bool)$this->messagesTblObj->dataRow['UNREAD'];
	}

	private function ParseFieldsToDataRow()
	{
		$this->messagesTblObj->dataRow['USERS_MESSAGES_ID'] = $this->users_messages_id;
		$this->messagesTblObj->dataRow['SENDER_USERS_ID'] = $this->sender_users_id;
		$this->messagesTblObj->dataRow['SENDER_USERS_NAME'] = $this->sender_id;
		$this->messagesTblObj->dataRow['SENDER_IP'] = $this->sender_ip;
		$this->messagesTblObj->dataRow['RECEIVER_USERS_ID'] = $this->receiver_users_id;
		$this->messagesTblObj->dataRow['RECEIVER_USERS_NAME'] = $this->receiver_users_name;

		$this->messagesTblObj->dataRow['MESSAGE_TITLE'] = $this->message_title;
		$this->messagesTblObj->dataRow['MESSAGE_BODY'] = $this->message_body;
		$this->messagesTblObj->dataRow['UNREAD'] = $this->unread;
	}

}
/*
CREATE  TABLE IF NOT EXISTS `INNODB_TABLES`.`COMPANIES` (
  `COMPANIES_ID` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `GUID` VARCHAR(36) NULL ,
  `CREATED_TIME` TIMESTAMP NULL ,
  `UPDATED_TIME` TIMESTAMP NULL ,
  `UPDATED_LOGIN` BIGINT UNSIGNED NULL ,
  `CITIES_ID` BIGINT UNSIGNED NULL ,
  `COUNTRIES_ID` BIGINT UNSIGNED NULL ,
  `LOCAL_LANG_ID` BIGINT NULL ,
  `NAME` VARCHAR(200) NULL ,
  `CITY_NAME` VARCHAR(200) NULL ,
  `COUNTRY_NAME_LOCAL` VARCHAR(200) NULL ,
  `EDRPOU_TAXPNUM` BIGINT NULL ,
  `BANK_MFO` BIGINT NULL ,
  `BANK_SWIFT_BIC` VARCHAR(50) NULL COMMENT 'ISO 13616-compliant national IBAN formats' ,
  `EORI_CODE` VARCHAR(14) NULL ,
  `IBAN` VARCHAR(30) NULL COMMENT 'ISO 13616 IBAN Standard\nCountry_code' ,
  `ACCOUNT_NUMBER_UA` DECIMAL NULL COMMENT 'номер рахунку\n21 розряд' ,
  `ISIN` VARCHAR(12) NULL ,
  `LAW_ADDRESS` VARCHAR(200) NULL COMMENT 'Юридична адреса' ,
  `PHYSICAL_ADDRESS` VARCHAR(200) NULL COMMENT 'Адреса офісу' ,
  `COMMENT` VARCHAR(1000) NULL ,
  `ZIP_CODE` VARCHAR(10) NULL ,
  `VAT_CODE` VARCHAR(20) NULL ,
  PRIMARY KEY (`COMPANIES_ID`) ,
  UNIQUE INDEX `COMPANIES_ID_UNIQUE` (`COMPANIES_ID` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Список компаній'
*/

?>