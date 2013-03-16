<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("pages/CommonPage.php");
	include_once("pages/Page.Material_Local.php");
	include_once("includes/common/Tag/Lib.Select.php");
	include_once("includes/common/Tag/Lib.Form.php");
	include_once("includes/common/Tag/Lib.Hidden.php");
	include_once("includes/common/Tag/Lib.CountriesList.php");
	include_once("includes/common/Tag/Lib.MaterialsGroupsList.php");

	include_once("pages/Block.Login.php");
	
	include_once("includes/DatabaseClasses/Parts.Materials.php");
	include_once("includes/DatabaseClasses/Parts.Countries.php");
	
	include_once("Block.Materials.php");
	
	class Material extends CommonPage
	{
		public $pageItems = array("Title");
		
		public $pageMarkup = "";
		
		public $blocks = NULL;
		
		private $fields_array = array(
							"projects_country_field" => false, "projects_name" => false, "projects_sdate" => false,
							"projects_edate" => false, "projects_city_name_field" => false, "projects_city_id" => false
							);
		
		private $isValidForm = false;
		private $isFormsRender = true;
		
		private $valuesArray = array();
		
		private $step = 0;
		
		private $materials = NULL;
		private $formTag = NULL;		
		
		public function __construct()
		{
			$this->localizator = new Material_Local();
			
			$this->materials = new Materials();
						
			parent::__construct();
		}
		
		private function InitMaterialsDetails()
		{
			$editMaterial = new BlockMaterials();
			$editMaterial->name = "BLOCK_MATERIALS_DETAILS";
			$editMaterial->page = $this;
			$editMaterial->BlockMode = MATERIAL_DETAILS;
			$editMaterial->uniqueIdentifier = Request::$identifier;
			$editMaterial->isSupplier = true;
				
			$editMaterial->BlockInit();
				
			$this->blocks['BLOCK_MATERIALS_DETAILS'] = $editMaterial;

			$this->pagesMarkup .= "<:=BLOCK_MATERIALS_DETAILS=:>";
		}
		
		public function PageInit()
		{
			parent::PageInit();

			if(!$this->isAjaxRequest)
			{
				//Îñíîâí³ êë³ºíòñüê³ ñêğèïòè 
				$this->commonScripts .= BlockSearch::InitScripts(RENDER_COMPANIES_SEARCH_INPUT);
				$this->commonScripts .= MaterialsGroupsList::ClientScripts('treeParentGroupId');
				
				$searchText = $this->GetLocalValue("Material_Search_Field");
				
				$blockSearch = new BlockSearch ();
				$blockSearch->page = $this->page;
				$blockSearch->BlockMode = RENDER_MATERIALS_SEARCH_INPUT;
				$blockSearch->inputId = "materialsInput";
				$blockSearch->inputText = $searchText;
				$blockSearch->divStyle = "display:inline-block;";
				$blockSearch->BlockInit();
				
				$this->blocks['BLOCK_SEARCH'] = $blockSearch;
				
				$this->pagesMarkup .= "<div><div >&nbsp;</div><div style=' display:inline-block; '><input type='hidden' id='companiesId' name='companiesId' value=''><nobr><:=BLOCK_SEARCH=:>&nbsp;<button name='searchBtn' id='searchBtn' style='width:120px;' ><:=Material_Search_Btn=:></button></nobr></div></div>";
				

				$materials = new BlockMaterials();
				$materials->name = 'BLOCK_MATERIALS_LIST';
				$materials->page = $this;
				$materials->BlockMode = RENDER_MATERIALS_SEARCH_RESULT;
				$materials->BlockInit();
				
				$this->blocks['BLOCK_MATERIALS_LIST'] = $materials;

				if(Session::GetUserId() > 0 || Session::IsSuperAdmin())
				{
					$link = new Anchor();
					$link->title = "<:=Add_New_Material=:>"; 
					$link->href = Request::$url;
					$link->isTraditionalHref = true;
					$link->onClick = " AjaxMaterialClick(null);  return false; ";
					$link->applyScripts = false;
				}

				$this->commonScripts .= " function companyClick(id, name) { $('div_search').style.display = 'none'; $('companiesId').value = id; Objects.Search.isHalted = true; $('companiesInput').value = name; } ";

				$this->commonScripts .= ' Objects.Environment["Edit_Material"] = "'.$this->GetLocalValue('Edit_Material').'"; ';
				$this->commonScripts .= ' Objects.Environment["Add_New_Material"] = "'.$this->GetLocalValue('Add_New_Material').'"; ';
				
				$this->commonScripts .= BlockMaterials::InitScripts()." ";

				$this->pagesMarkup .= "<br/><:=BLOCK_MATERIALS_LIST=:>";

				if(strlen(Request::$identifier) > 0 && ucfirst(strtolower(Request::$identifier)) != 'Latest')
				{
					$this->InitMaterialsDetails();
				}
			}
			elseif($this->isValidPost && Request::GetSCode() == sha1("sAveFilEtoDatAbaSe"))
			{
				$this->InitMaterialsDetails(); //					echo Security::DecodeUrlData($_POST['OBJECTS_ID'])." ddd ".(float)Security::DecodeUrlData($_POST['ALBUMS_ID']);
			}
			elseif(Request::GetSCode() == sha1("AddGroUpsNaMe") || Request::GetSCode() == sha1("AddNeWMateRrIal") || Request::GetSCode() == sha1("EditMateRrIal") || Request::GetSCode() == sha1("SubMitNewMateRialtODb") || Request::GetSCode() == sha1("SavEeXisTingMateRial"))
			{
				$editMaterial = new BlockMaterials();
				$editMaterial->name = "BLOCK_ADD_MATERIAL";
				$editMaterial->page = $this;
				$editMaterial->BlockMode = ADD_MATERIALS;
				
				$editMaterial->BlockInit();
				
				$this->blocks['BLOCK_EDIT_MATERIAL'] = $editMaterial;

				$this->pagesAjaxMarkup .= "<:=BLOCK_EDIT_MATERIAL=:>";
			}
			elseif(Request::GetSCode() == sha1("AddAjaxSupPliEr") || Request::GetSCode() == sha1("EditAjaxSuPplIeR") || Request::GetSCode() == sha1("DeLeTeAjAxSupPliEr"))
			{	
				$addSupplier = new BlockMaterials();
				$addSupplier->name = "BLOCK_AJAX_SUPPLIER";
				$addSupplier->page = $this;
				$addSupplier->BlockMode = BLOCK_AJAX_SUPPLIER;
				$addSupplier->BlockInit();

				$this->blocks['BLOCK_ADD_SUPPLIER'] = $addSupplier;

				$this->pagesAjaxMarkup .= "<:=BLOCK_ADD_SUPPLIER=:>";
			}
			elseif(Request::GetSCode() == sha1("mAkeThEpReSeArcHreQuest"))
			{
				$blockSearch = new Login();
				$blockSearch->page = $this;
				$blockSearch->BlockMode = PROFILE_FIELDS;
				$blockSearch->blockName = "Render the profile fields";

				$blockSearch->BlockInit();

				$this->blocks['BLOCK_SEARCH'] = $blockSearch;

				$this->pagesAjaxMarkup .= "<:=BLOCK_SEARCH=:>";
			}
		}
	}
?>
