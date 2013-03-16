<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


  include_once("CommonBlock.php");
  
  include_once("includes/common/Tag/Lib.Anchor.php");
  include_once("includes/DatabaseClasses/Parts.Search.php");
  include_once("Block.Search_Local.php");

  	/**
	 * class Search
	 *
	 * Блок пошуку
	 *
	 * @package Blocks.pkg
	 */

	class BlockSearch extends CommonBlock
	{	
		public $page = NULL;
		
		private $fieldsArray = array( "search_text" => false );

		public $blocksMarkup = "";
		
		public $inputId = "searchText";
		public $inputText = "searchValue";
		public $divCss = "searchCss";
		public $divStyle = "";
		
		private $searchText = "";
		
		private $isValidForm = false;
		
		private $formTag = NULL;
		
		public $BlockMode = RENDER_AJAX_SEARCH_FORM; // RENDER_AJAX_SEARCH_FORM ajax search form
							     // RENDER_MATERIALS_SEARCH_INPUT ajax input
							     // RENDER_COMPANIES_SEARCH_INPUT ajax input

		private $search = NULL;
				
		public function __construct()
		{
			$this->localizator = new BlockSearch_Local();
			$this->search = new Search();
			
			$this->SCodes = self::GetAllowedSCodes();
			
			if($this->linkForListing == "")
			{
				$this->linkForListing = Request::GetRoot()."/search/";
			}
			
			parent::__construct();
		}
		
		public static function GetAllowedSCodes()
		{
			return array (
					'ajax_search' => sha1("mAkeThEpReSeArcHreQuest") // &#65533;&#65533;&#65533;&#65533;&#65533;&#65533; AJAX &#65533;&#65533;&#65533;&#65533;&#65533;
						); 
		}
		
		public function ValidateForm()
		{
			$this->isValidForm = false;
			
			if(!in_array(Request::GetSCode(), $this->SCodes))
			{
				$this->isValidForm = false;
				return $this->isValidForm;
			}
			
			$searchText = StaticDatabase::CleanupTheField($_POST[$this->inputId]); //;
			
			return $this->isValidForm;
		}
		
		private function SwitchMode()
		{
			switch($this->BlockMode)
			{
				case RENDER_AJAX_SEARCH_FORM:
				
					$this->RenderSimpleAjaxSearch();
					
				break;
				
				case RENDER_MATERIALS_SEARCH_INPUT:
				
					$this->RenderMaterialsSearchInput();
				
				break;

				case RENDER_COMPANIES_SEARCH_INPUT:

					$this->RenderCompaniesSearchInput();

				break;

			}
		}

		public static function InitScripts($blockMode)
		{
			switch($blockMode)
			{
				case RENDER_COMPANIES_SEARCH_INPUT: 

					$commonScripts = " function companiesClick(obj, companiesId) { Objects.Environment['COMPANIES_ID'] = companiesId; var dSearch = $('div_search'); dSearch.style.display = 'none'; $('selectedResult').update(obj.innerHTML); ".
							' 
								Objects.Environment["COMPANIES_ID"] = "";

								var parameters = {
										__SVar		: Objects.Security.secureServerVar,
										__SCode		: "'.sha1("SelEcTComPanY").'",
										__ClientVar	: Objects.Security.createSecureVar(),
										IS_AJAX		: "TRUE",
										COMPANIES_ID: companiesId
									};

								var rq = new Ajax.Request("'.Request::$url.'", { 
										
										parameters : parameters,
										
										onCreate : function ()
										{
											this.requestIsActive = true;
										},

										onFailure : function ()
										{
										},

										onSuccess: function(response)
										{
											try {
												var rObject = Objects.Security.validateResponse(response.responseJSON);
												
												if(rObject != null && rObject.isSecured)
												{
													$("companiesList").innerHTML = rObject.text;
													$("companiesList").style.display = "block";

													if(rObject.scripts.length > 0)
														eval(rObject.script[0]);
												}
											}
											catch(ex)
											{
												FireError(ex);
											}
										}

									}); }
							';

				break;

				case RENDER_MATERIALS_SEARCH_INPUT:

					$commonScripts = " function materialsClick(obj, materialsId) { Objects.Environment['MATERIALS_ID'] = materialsId; var dSearch = $('div_search'); dSearch.style.display = 'none';  $('selectedResult').update(obj.innerHTML); ".
						  ' 
						  	Objects.Environment["SUPPLIERS_ID"] = "";
						  	$("mainFields").style.display = "block";
											  	
							  var parameters = {			
									  	__SVar   	: Objects.Security.secureServerVar,
										__SCode  	: "'.sha1("SelEcTSupPliErS").'",
			  							__ClientVar : Objects.Security.createSecureVar(),
										IS_AJAX  	: "TRUE",
										MATERIALS_ID: materialsId
									};

											  
							  var rq = new Ajax.Request("'.Request::$url.'", {

										parameters : parameters,

									  	onCreate: function ()
									  	{
									  		this.requestIsActive = true;
									  	},
											
									  	onFailure: function ()
									  	{	
									  	},
											
										onSuccess: function(response) 
										{
											try
											{	
										  		var rObject = Objects.Security.validateResponse(response.responseJSON);

										  		if(rObject != null && rObject.isSecured)
												{
											  		$("suppliersList").innerHTML = rObject.text;
											  		$("suppliersList").style.display = "block";
					  		
											  		if(rObject.scripts.length > 0)
												  		eval(rObject.scripts[0]); 
	
													if(this.successMethod)
														eval(this.successMethod);
												}
												else
												{
												}
											}
											catch(ex)
											{
												FireError(ex);
											}
									    	}
							    		}); }';

				break;
			}

			return $commonScripts;
			
		}

		private function RenderCompaniesSearchInput()
		{
			//$this->blocksAjaxMarkup .= "fsdfsdfsd";

			if(!$this->isAjaxRequest || $this->isValidPost && Request::GetSCode() != $this->SCode['ajax_search'])
			{
				$this->RenderSimpleForm();

				if($this->isAjaxRequest)
				{
					$this->blocksAjaxMarkup = $this->blocksMarkup;
				}
			}
			elseif($this->isValidPost && Request::GetSCode() == $this->SCode['ajax_search'])
			{
				$this->page->isAjaxJSON = true;
				
				$companies = new Companies();
				$result = $companies->LoadCompanies($this->searchText);

				if($result != NULL && mysql_num_rows($result) > 0)
				{
					while($row = mysql_fetch_array($result))
					{
						$companiesId = Security::EncodeUrlData($row['COMPANIES_ID']);
						$this->blocksAjaxMarkup .= "<div onClick='alert(\"12345\");'>".$row['NAME']."</div>";
					}
				}
			}
	
		}
		
		private function RenderMaterialsSearchInput()
		{
			if( !$this->isAjaxRequest || $this->isValidPost && Request::GetSCode() != $this->SCodes['ajax_search'])
			{
				$this->RenderSimpleForm ();

				if($this->isAjaxRequest)
				{
					$this->blocksAjaxMarkup = $this->blocksMarkup;
				}
			}

			elseif($this->isValidPost && Request::GetSCode() == $this->SCodes['ajax_search'])
			{
				$this->page->isAjaxJSON = true;

				$materials = new Materials();
				$result = $materials->LoadMaterials($this->searchText);
				
				if($result != NULL && mysql_num_rows($result) > 0)
				{
					while($row = mysql_fetch_array($result))
					{
						$materialsId = Security::EncodeUrlData($row['MATERIALS_ID']);
						
						$this->blocksAjaxMarkup .= "<div onClick='materialsClick(this, \"{$materialsId}\");'>".$row['NAME']."</div>";
					}
				}				
			}	
		}
		
		private function RenderSimpleForm($searh_link = "")
		{
			$this->blocksMarkup .= "<div id='search_container' class='".$this->divCss."' ".(strlen($this->divStyle) > 0 ? "style='".$this->divStyle."'" : '').">";
			$this->blocksMarkup .= "<input type='text' name='{$this->inputId}' id='{$this->inputId}' value='{$this->inputText}'>&nbsp;".$search_lnk;
			$this->blocksMarkup .= "<div id='div_search' style='display:none; cursor:default;' >Search Result</div>";
			$this->blocksMarkup .= "<div id='selectedResult'></div>";
			$this->blocksMarkup .= "</div>";
			 
			$this->page->commonScripts .= "Objects.Environment['SearchInput'] = '{$this->inputId}'; ";
		}

		private function RenderSimpleAjaxSearch()
		{
			$anchor = new Anchor();
			$anchor->SCode = $this->sCodes['ajax_search'];
			$anchor->title = $row['NAME'];
			$anchor->isTraditionalHref = false;
			$anchor->href = $this->linkForListing;
			$anchor->onClick = "";

			$search_lnk = $anchor->OpenTag()."<:=Search_Link=:>".$anchor->CloseTag()."<br/>";

			$this->RenderSimpleForm();			
		}

		public function BlockInit()
		{
			parent::BlockInit();

			$this->formTag = new Form();
			$this->name = "search_form";

			$this->SwitchMode();
		}
	}
?>