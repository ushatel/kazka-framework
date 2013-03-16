<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("includes/common/Lib.php");
  
  include_once("includes/common/Tag/Lib.Select.php");
  
  include_once("CommonPage.php");
  include_once("Page.Company_Local.php");
  include_once("includes/DatabaseClasses/Parts.Countries.php");
  include_once("includes/DatabaseClasses/Parts.Companies.php");
  include_once("includes/common/Tag/Lib.CountriesList.php");
  include_once("includes/common/Tag/Lib.MaterialsGroupsList.php");
  
  include_once("Block.Projects.php");
  include_once("Block.Materials.php");
  include_once("Block.Companies.php");

/**
 * class Company
 *
 * Цей класс сторінки регистрації нового користувача.
 *
 * @package Pages.pkg
 */

class Company extends CommonPage
{
	public $pageItems = array("Title");
	
	public $pagesMarkup = "";
	
	public $blocks = NULL;
	
	private $fieldsArray = array 
			(
			"company_name_field" => false,
			"company_edrpou_field" => false,
			"company_address_field" => false,
			"company_office_address_field" => false,
			"unique_name_field" => false
			);
	
	private $isValidForm = false;
	
	private $valuesArray = array();
	
	private $companies = NULL;
	private $projects = NULL;
	
	private $materialId = 0;
		
	public function __construct()
	{
		$this->localizator = new Company_Local();
		$this->companies = new Companies();
		
		parent::__construct();
	}
	
	private function ValidateForm()
	{
		$this->isValidForm = false;
		
		if(Request::GetSCode() != sha1("AdDinGtHeneWcoMpaNy") || Request::GetSCode() == sha1("ComPanIesProFile"))
		{
			return $this->isValidForm;
		}
	
		$this->companies->name = StaticDatabase::CleanupTheField($_POST['company_name_field']);
		
		if(strlen($this->companies->name) < 3)
		{
			$this->fieldsArray["company_name_field"] = '<:=Validation_Company_name_IsTooShort_Error=:>';
			$this->isValidForm = false;
		}
		else 
		{
			//First form validation
			$this->isValidForm = true;
		}
		
		$this->companies->unique_name_identifier = StaticDatabase::CleanupTheField($_POST['unique_name_field']);
		
		if(strlen($this->companies->unique_name_identifier) < 3 || $this->companies->ValidateUniqueIdentifier())
		{
			$this->fieldsArray["unique_name_field"] = '<:=Validation_Company_Name_IsNot_Unique=:>';
			$this->isValidForm = false;
		}
		else 
		{
			$this->isValidForm = $this->isValidForm & true;
		}

		$countryArray = preg_split("/_/i", StaticDatabase::CleanupTheField($_POST['country_name_field']), 2);

		if(!is_numeric($countryArray[0]) || strlen($countryArray[1]) != 2)
		{
			$this->fieldsArray["country_name_field"] = "<:=Validation_Company_name_Error=:>";
			$this->isValidForm = false;
		}
		else 
		{
			$this->companies->country_name = $countryArray[1];
			$this->companies->countries_id = (int)$countryArray[0];
		
			$this->isValidForm = true;
		}

		$this->companies->city_name = StaticDatabase::CleanupTheField($_POST['city_name_field']);
		$this->companies->edrpou_taxpnum = StaticDatabase::CleanupTheField($_POST['edrpou_field']);
		$this->companies->mfo = StaticDatabase::CleanupTheField($_POST['mfo_field']);
		$this->companies->swift = StaticDatabase::CleanupTheField($_POST['swift_field']);
		$this->companies->eori_code = StaticDatabase::CleanupTheField($_POST['eori_code']);
		$this->companies->iban = StaticDatabase::CleanupTheField($_POST['iban_field']);
		$this->companies->account = StaticDatabase::CleanupTheField($_POST['account_number_field']);
		$this->companies->isin = StaticDatabase::CleanupTheField($_POST['isin_field']);
		$this->companies->address = StaticDatabase::CleanupTheField($_POST['address_field']);
		$this->companies->physical_address = StaticDatabase::CleanupTheField($_POST['physical_address_field']);
		$this->companies->comment = StaticDatabase::CleanupTheField($_POST['company_comment']);
		$this->companies->zip_code = "";
		$this->companies->vat_code = StaticDatabase::CleanupTheField($_POST['vat_code_field']);
		
		return $this->isValidForm;
	}
	
		
	public function PageInit()
	{		
		$block = new BlockCompanies();
		$block->page = $this;
		$block->BlockMode = COMPANIES_PROFILE;

		$this->blocks['BLOCK_COMPANIES'] = $block;

		if(!$this->isAjaxRequest)
		{
			$this->commonScripts .= BlockCompanies::InitScripts()." ";
			$this->commonScripts .= BlockMaterials::InitScripts()." ";

			$this->commonScripts .= ' Objects.Environment["Edit_Material"] = "'.$this->GetLocalValue('Edit_Material').'"; ';
			$this->commonScripts .= ' Objects.Environment["Add_New_Material"] = "'.$this->GetLocalValue('Add_New_Material').'"; ';
			
			$this->commonScripts .= MaterialsGroupsList::ClientScripts('treeParentGroupId')." ";
			$this->pagesMarkup .= "<:=BLOCK_COMPANIES=:>";
		}
		else
		{	
			$this->pagesAjaxMarkup .= "<:=BLOCK_COMPANIES=:>";
		}
		
		parent::PageInit();

	}
	
}

?>