<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("pages/CommonPage.php");
	include_once("pages/Page.News_Local.php");
	include_once("includes/common/Tag/Lib.Select.php");
	include_once("includes/common/Tag/Lib.Form.php");
	include_once("includes/common/Tag/Lib.Hidden.php");
	include_once("includes/common/Tag/Lib.CountriesList.php");
	include_once("includes/common/Tag/Lib.MaterialsGroupsList.php");
		
	include_once("Block.News.php");
	include_once("Block.Search.php");
	include_once("Block.Materials.php");
	
	class News 	extends CommonPage
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
		
		private $news = NULL;
		private $formTag = NULL;		
		
		public function __construct()
		{
			$this->localizator = new News_Local();
			
			//$this->news = new PNews();

			parent::__construct();
		}
		
		private function InitNewsDetails()
		{
			$editNews = new BlockNews();

			$editNews->name = 'BLOCK_NEWS_DETAILS';
			$editNews->page = $this;
			$editNews->BlockMode = NEWS_DETAILS;

			$editNews->BlockInit();

			$this->blocks['BLOCK_NEWS_DETAILS'] = $editNews;

			$this->pagesMarkup .= "<:=BLOCK_NEWS_DETAILS=:>";
		}
		
		private function InitNews()
		{
			$editNews = new BlockNews();
			
			$editNews->name = "BLOCK_NEWS";
			$editNews->page = $this;
			$editNews->BlockMode = RENDER_NEWS_SEARCH_RESULT;
			
			$editNews->BlockInit();
			
			$this->blocks['NEWS_RESULT'] = $editNews;
			
			$this->pagesMarkup .= "<:=NEWS_RESULT=:>";
		}
		
		private function EditNews()
		{
			$editNews = new BlockNews();

			$editNews->name = "";
			$editNews->page = $this;
			$editNews->BlockMode = NEWS_EDIT;

			$editNews->BlockInit();

			$this->blocks['NEWS_BLOCK'] = $editNews;

			if($this->isAjaxRequest)
				$this->pagesAjaxMarkup = "<:=NEWS_BLOCK=:>";
			else
				$this->pagesMarkup = "<:=NEWS_BLOCK=:>";

		}
		
		public function PageInit()
		{
			parent::PageInit();

			if(!$this->isAjaxRequest)
			{
				$this->pagesMarkup .= "<div id='newsBlock'>";

				//Îñíîâí³ êë³ºíòñüê³ ñêğèïòè 
				//$this->commonScripts .= BlockSearch::InitScripts(RENDER_COMPANIES_SEARCH_INPUT);

				$this->commonScripts .= 'Objects.Dictionary["News_Add_New"] = "'.$this->GetLocalValue("News_Add_New").'"; Objects.Dictionary["News_Edit_Title"] = "'.$this->GetLocalValue("News_Edit_Title").'"; ';
				$this->commonScripts .= BlockNews::InitClientScripts();

				/*$searchText = $this->GetLocalValue("Material_Search_Field");
				
				$blockSearch = new BlockSearch ();
				$blockSearch->page = $this->page;
				$blockSearch->BlockMode = RENDER_MATERIALS_SEARCH_INPUT;
				$blockSearch->inputId = "materialsInput";
				$blockSearch->inputText = $searchText;
				$blockSearch->divStyle = "display:inline-block;";
				$blockSearch->BlockInit();
				
				$this->blocks['BLOCK_SEARCH'] = $blockSearch;
				
				$this->pagesMarkup .= "<div><div >&nbsp;</div><div style=' display:inline-block; '><input type='hidden' id='companiesId' name='companiesId' value=''><nobr><:=BLOCK_SEARCH=:>&nbsp;<button name='searchBtn' id='searchBtn' style='width:120px;' ><:=Material_Search_Btn=:></button></nobr></div></div>";
				*/

				if((strlen(Request::$identifier) > 0) && Request::$identifier != 'Latest')
					$this->InitNewsDetails();
				else
					$this->InitNews();

				$this->pagesMarkup .= "</div>";
			}
			elseif($this->isValidPost && (Request::GetSCode() == sha1("DelEteNeWs") || Request::GetSCode() == sha1("EdiTnEws")) )
			{
				$this->EditNews();
			}
			elseif($this->isValidPost && Request::GetSCode() == sha1("sAveFilEtoDatAbaSe"))
			{
//				$this->InitMaterialsDetails(); 
//				echo Security::DecodeUrlData($_POST['OBJECTS_ID'])." ddd ".(float)Security::DecodeUrlData($_POST['ALBUMS_ID']);
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