<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


  include_once("CommonBlock.php");

  include_once("Block.Services_Local.php");
  include_once("Block.Search.php");
  include_once("includes/common/Tag/Lib.MaterialsGroupsList.php");
  include_once("includes/common/Tag/Lib.DivisibilityList.php");
  include_once("includes/common/Tag/Lib.Grid.php");
  include_once("includes/common/Tag/Lib.Checkbox.php");
  include_once("includes/common/Tag/Lib.CountriesList.php");
  include_once("includes/common/Tag/Lib.CompaniesList.php");

  include_once("includes/DatabaseClasses/Parts.Materials.php");
  include_once("includes/DatabaseClasses/Parts.Projects.php");
  include_once("includes/DatabaseClasses/Parts.Companies.php");

  include_once("includes/common/Main.SearchOperator.php");
  include_once("includes/common/Main.Enumerator.php");


  	/**
	 * class Services
	 *
	 * Клас роботи із послугами
	 *
	 * @package Blocks.pkg
	 */

	class BlockServices extends CommonBlock
	{
		public $page = NULL;
		
		private $fieldsArray = array("materials_group" => false, 
					     "materials_name" => false, 
						 );
									  		
		public $blocksMarkup = "";
		
		private $isValidForm = false;
		
		private $formTag = NULL;
		
		public $BlockMode = SELECT_SERVICES ;  // SELECT_MATERIALS = renders the form to select materials,
											   // SELECT_MATERIALS_AJAX = renders the ajax materials
											   // ADD_MATERIALS = renders the form to create materials,
											   // RENDER_MATERIALS_LIST = renders the materials list
											   // RENDER_MATERIALS_GRID = renders the grid with materials
											   // RENDER_LATEST_MATERIALS_GRID = renders the grid with the top 20 materials on the stock
											   // ADD_SUPPLIERS_MATERIALS = renders the form to add suppliers materials
											   // MATERIAL_DETAILS = renders details for the material
		
		public $filter_name = "";    
		public $filter_offset = 0;   
		public $filter_limit = 0;

		private $selectedPrjId = 0;
		private $selectedPrjStepId = 0;

		private $newGroupName = "";
		private $newGroupId = 0;
		
		private $SCodes = NULL;
		
		public $isSupplier = false;

		public $uniqueIdentifier = "";
		public $materialsId = 0;
		public $supplierCompaniesId = 0;
		public $isCustomLink = false;

		public $materialsInput = "";

		public $servicesWindow = 15;
		public $servicesOffset = 0;

		public $linkForListing = "";
		public $sCodeForListing = "";

		public $showInBubble = false;

		private $groups_identifier = '';
		
		public $userId = 0;
		
		public function __construct()
		{
			$this->localizator = new BlockServices_Local();
			$this->services = new Services();
			
			$this->groups_identifier = substr(StaticDatabase::CleanupTheField($_GET['groups']), 0, 200);

			$this->SCodes = self::GetAllowedSCodes();

			if($this->linkForListing == "")
			{
				$this->linkForListing = Request::GetRoot()."/services/";
			}

			parent::__construct();
		}

		public static function GetAllowedSCodes()
		{
			return array (sha1("SubMitSeRvIce"), sha1("SaVeExiStiNgSerVice"), 
				sha1("SubMitSelEctEdMateRiAls"), 'ajax_search' => sha1("mAkeThEpReSeArcHreQuest"), 'ajax_create' => sha1("AddMaTeRiAlToTheProJect"), 'ajax_edit' => sha1("EditAjaxSinGlMateRial"), 'ajax_suppliers' => sha1("SelEcTSupPliErS"), 'ajax_bubble' => sha1("RenDerMateRiAlsBubBle"), 'ajax_supplier' => sha1("AddSupPliEr"), 'ajax_addsupplier' => sha1("AddAjaxSupPliEr"), 'ajax_editsupplier' => sha1("EditAjaxSuPplIeR"), 'ajax_delsupplier' => sha1("DeLeTeAjAxSupPliEr"), 'ajax_addgroupsname' => sha1("AddGroUpsNaMe"));
		}

		public function ValidateNewGroup()
		{
			$this->isValidForm = false;
			
			if(!in_array(Request::GetSCode(), $this->SCodes))
			{
				$this->isValidForm = false;
				return $this->isValidForm;
			}
			
			$this->newGroupName = StaticDatabase::CleanupTheField($_POST['groupName']);

			if(strlen($this->newGroupName) < 3)
			{
				$this->isValidForm = false;
				$this->fieldsArray['groupsName'] = "<:=Validation_Materials_Groups_Name=:>";
			}
			else
			{
				$this->isValidForm = true;
			}
			
			$this->newGroupId = (float)substr(Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['parentGroup'])), 6);
			
			if($this->newGroupId == 0)
			{
				$this->isValidForm = false;
				$this->fieldsArray['parentGroup'] = "<:=Validation_Materials_Groups_Id=:>";
			}
			else
			{
				$this->isValidForm = $this->isValidForm & true;
			}
						
			return $this->isValidForm;
		}

		public function ValidateForm()
		{
			$this->isValidForm = false;
			
			if(!in_array(Request::GetSCode(), $this->SCodes))
			{
				$this->isValidForm = false;
				return $this->isValidForm;
			}
						
			$this->materials->name = StaticDatabase::CleanupTheField($_POST['materials_name']);
			
			if(strlen($this->materials->name) < 1)
			{
				$this->isValidForm = false;
				$this->fieldsArray['materials_name'] = "<:=Validation_Materials_name_Error=:>";
			}
			else
			{
				$this->isValidForm = true;
			}
			
			$groups_array = preg_split("/_/", Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['materials_groups_id'])),2);
			$this->materials->materials_groups_id = (float)$groups_array[1];
			
			$this->materials->user_supplier_companies_id = (float)StaticDatabase::CleanupTheField($_POST["materials_supplier_id"]);
			$this->materials->user_supplier_companies_name = substr(StaticDatabase::CleanupTheField($_POST["companiesInput"]), 0, 200);

			if($this->materials->materials_groups_id < 1)
			{
				if(($this->materials->materials_groups_id = (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['treeParentGroupId'])) ) < 1)
				{
					$this->isValidForm = false;
					$this->fieldsArray['materials_group'] = "<:=Validation_Materials_Empty_Group_Error=:>";	
				}
			}
			else
			{
				$this->isValidForm = $this->isValidForm & true; 
			}
			
			$this->materials->is_public = (bool)(StaticDatabase::CleanupTheField($_POST['materials_is_public']) == 'on' ? true : false);
			$this->materials->is_approved = (bool)(StaticDatabase::CleanupTheField($_POST['materials_is_approved']) == "on" ? true : false);
			
			$this->materials->unique_name_identifier = StaticDatabase::CleanupTheField($_POST['materials_unique_name']);

			$new_unique_name_identifier = StaticDatabase::CleanupTheField($_POST['materials_unique_name']);
			//$error = $this->materials->unique_name_identifier." ".$new_unique_name_identifier;
			
			if(strlen($new_unique_name_identifier) < 3 || 
				($this->materials->unique_name_identifier != $new_unique_name_identifier) && $this->materials->ValidateUniqueIdentifier($new_unique_name_identifier))
			{
				if(strlen($this->materials->unique_name_identfier) < 3)
				{
					$this->materials->unique_name_identifier = Operations::Translator($this->materials->name);
					$this->fieldsArray["unique_name_field"] = '<:=Validation_Check_Materials_Name=:>'.$error;					
				}	
				else
				{
					$this->fieldsArray["unique_name_field"] = '<:=Validation_Materials_Name_IsNot_Unique=:>';
				}
				$this->isValidForm = false;
			}
			else 
			{
				$this->materials->unique_name_identifier = $new_unique_name_identifier;
				$this->isValidForm = $this->isValidForm & true;
			}
			
			$this->materials->common_www = StaticDatabase::CleanupTheField($_POST['common_www']);
			
			if(strlen($this->materials->common_www) > 0 )
			{
				$hdl = NULL;
				try{
					$hdl = @fopen($this->materials->common_www, "r");
					if($hdl != NULL)
					{
						@fclose($hdl);
						$hdl = true;
					}
					else
						$hdl = false;
				}
				catch(Exception $e)
				{
					$hdl = false;
				}
				
				if(!$hdl)	
				{
					$this->fieldsArray['common_www'] = '<:=Validation_WWW_Error=:>';
					$this->isValidForm = false;					
				}
			}
			else
			{
				if($hdl != NULL) fclose($hdl);
				$this->isValidForm = $this->isValidForm & true;
			}

			$countryArray = preg_split("/_/i", StaticDatabase::CleanupTheField($_POST['country_name_field']), 2);
			
			if(!is_numeric($countryArray[0]) || strlen($countryArray[1]) != 2)
			{
				$this->fieldsArray["country_name_field"] = "<:=Validation_Country_Name_Error=:>";
				$this->isValidForm = false;
			}
			else 
			{
				$this->materials->original_countries_id = (float)$countryArray[0];

				$this->isValidForm = $this->isValidForm & true;
			}

			$this->materials->vendor_text = StaticDatabase::CleanupTheField($_POST['materials_vendor_name']);
			$this->materials->vendor_companies_id = (float)Security::DecodeUrlData($_POST['COMPANIES_ID']); // !!! Should check the name in DB

			if(strlen($this->materials->vendor_text) < 2)
			{
				$this->fieldsArray["materials_vendor_name"] = "<:=Validation_Vendor_Name_Error=:>";
				$this->isValidForm = false;
			}
			else
			{
				$this->isValidForm = $this->isValidForm & true;
			}
			
			$this->materials->divisibility_id = (float) StaticDatabase::CleanupTheField($_POST['materials_divisibility_id']);
			
			if(!$this->materials->ValidateDivisibility())
			{
				$this->fieldsArray["materials_divisibility_id"] = "<:=Validation_Divisibility_Error=:>";
				$this->isValidForm = false;
			}
			else
			{
				$this->isValidForm = $this->isValidForm & true;
			}

			$this->materials->comment = substr(StaticDatabase::CleanupTheField($_POST['materials_comment']), 0, 5000);

			if($this->isSupplier)
			{
				$this->materials->user_comment = substr(StaticDatabase::CleanupTheField($_POST['materials_user_comment']), 0, 5000);
				$this->materials->user_divisibility_id = (float) (StaticDatabase::CleanupTheField($_POST['materials_users_divisibility_id']));
	
				if($this->materials->user_divisibility_id > 0 && !$this->materials->ValidateUserDivisibility())
				{
					$this->fieldsArray["materials_users_divisibility_id"] = "<:=Validation_Divisibility_Error=:>";
					$this->isValidForm = false;
				}
				else
				{
					$this->isValidForm = $this->isValidForm & true;
				}
				
				$user_quantity = StaticDatabase::CleanupTheField($_POST['materials_user_quantity']);
				if(!is_numeric($user_quantity))
				{
					$this->isValidForm = false;
					$this->fieldsArray["materials_user_quantity"] = "<:=Validation_Numeric_Quantity_Error=:>";
					$this->materials->user_quantity = 0;
				}
				else
				{
					$this->materials->user_quantity = (float)$user_quantity;
					$this->isValidForm = $this->isValidForm & true;
				}
			}

			return $this->isValidForm;
		}
		
		private function SwitchMode()
		{
			switch($this->BlockMode)
			{
				case SELECT_MATERIALS:
					$this->SelectMaterials();
				break;

				case SELECT_MATERIALS_AJAX:
					$this->SelectAjaxMaterials();
				break;

				case ADD_MATERIALS:
					$this->AddMaterials();
				break;

				case BLOCK_AJAX_SUPPLIER:
					$this->AjaxSupplier();
				break;
				
				case EDIT_MATERIAL:
					$this->EditMaterial();
				break;

				case MATERIAL_DETAILS:
					$this->MaterialsDetails();
				break;

				case RENDER_MAIN_MATERIALS:
					$this->RenderMainMaterials();
				break;
								
				case RENDER_MATERIALS_LIST:
					$this->RenderMaterialsList();
				break;
				
				case RENDER_LATEST_MATERIALS_GRID:
					$this->RenderLatestMaterialsGrid();
				break;
				
				case RENDER_MATERIALS_GRID:
					$this->RenderMaterialsGrid(true);
				break;
								
				case RENDER_MATERIALS_SEARCH_RESULT:
					$this->RenderMaterialsSearchResults();
				break;
			}
		}

		public function SetCompaniesId($companiesId)
		{
			$this->supplierCompaniesId = $this->materials->user_supplier_companies_id = (float)$companiesId;
		}
		
		public function SetProjectsId($projectsId)
		{
			$this->selectedPrjId = (float) $projectsId;
		}
		
		public function SetStepsId($stepsId)
		{
			$this->selectedPrjStepId = (float) $stepsId;
		}

		public function SetUserId($userId)
		{
			$this->userId = $userId;
		}
		
		private function RenderLatestMaterialsGrid()
		{
			$result = $this->materials->LoadMaterials('', 20);
			
			$this->RenderGrid($result);
		}

		private function RenderMaterialsGrid()
		{
			$this->materials->vendor_companies_id = $this->supplierCompaniesId;
			$result = $this->materials->LoadMaterials();
			
			$this->RenderGrid($result);
		}

		public static function InitScripts()
		{
		
			$result = '  function AjaxMaterialClick(id) { 

					if(id != null)
					{
						scode = "'.sha1("EditMateRrIal").'";
						title = Objects.Environment["Edit_Material"];
					}
					else
					{
						scode = "'.sha1("AddNeWMateRrIal").'";
						title = Objects.Environment["Add_New_Material"];
					}
					
					var parameters = { 	
						__SVar  	: Objects.Security.secureServerVar, 
						__SCode 	: scode, 
						__ClientVar : Objects.Security.createSecureVar(),
						PROJECTS_ID	: Objects.Environment[\'PROJECTS_ID\'],
						MATERIALS_ID: id,
						IS_AJAX 	: \'TRUE\'
					};

					if(Objects.Environment["COMPANIES_ID"] != null)
					{
						parameters["COMPANIES_ID"] = Objects.Environment["COMPANIES_ID"];
					}

					var rq = new Ajax.Request("/Materials/", { parameters : parameters, 
					onCreate: function () { 
						Objects.BubbleDiv.height = 520;
						Objects.BubbleDiv.width = 900;
						Objects.BubbleDiv.title = title;
						Objects.BubbleDiv.position = "midtop";
						Objects.BubbleDiv.show(); 
					}, 
				  	onSuccess: function(response) 
			  		{
	  					try
		  				{	 
			  				var rObject = Objects.Security.validateResponse(response.responseJSON);
					
			  				if(rObject != null && rObject.isSecured)
				  			{	
					  			Objects.BubbleDiv.refresh(rObject.text);
							
			  					if(rObject.scripts.length > 0)
				  					eval(rObject.scripts[0]); 	
							}
							else
							{
								alert(rObject + "_" + response.responseText);
							}
		
						}
						catch(ex)
						{
							FireError(ex);
						}
			    	}
				}); 				
				 }';
		
			return $result;
		}
		
		private function RenderGrid($result)
		{
			if($result != NULL && mysql_num_rows($result) > 0)
			{
				$grid = new Grid();
				
				if($result != NULL)
				{				
					//Init of the Grid
					$grid = new Grid();
					
					$grid->needDrawHeaders = true;
	
					// Init the Headers
					$fieldsArray = array();
					
					$field = new Field();
					$field->name = "materials_id";
					$field->value = "";
					$field->hidden = true;
					
					array_push($fieldsArray, $field);
					
					$field = new Field();
					$field->name = "materials_unique_id";
					$field->value = "<:=Materials_Unique_Name=:>";
					$field->width = "150px";
					
					array_push($fieldsArray, $field);
		
					$field = new Field();
					$field->name = "materials_name";
					$field->value = "<:=Materials_Name=:>";
					$field->width = "300px";
		
					array_push($fieldsArray, $field);

					$field = new Field();
					$field->name = "materials_supplier";
					$field->value = "<:=Materials_Supplier=:>";
					$field->width = "140px";
					
					array_push($fieldsArray, $field);
					
					$field = new Field();
					$field->name = "materials_quantity";
					$field->value = "<:=Materials_Quantity=:>";
					$field->width = "110px";
					
					array_push($fieldsArray, $field);

					$field = new RowProperties();
					$field->name = "ROW_PROPERTIES";
					$field->isSelected = false;
		
					$fieldsArray['ROW_PROPERTIES'] = $field;
							
					$grid->fieldsArray = $fieldsArray;
					
					$this->blocksMarkup .= $grid->RenderTop();	

					$nechet = true;
					while($row = mysql_fetch_array($result))
					{
						$fieldsArray = array();
						
						$field = new Field();
						$field->name = "materials_id";
						$field->value = $row['MATERIALS_ID'];
						$field->hidden = true;

						array_push($fieldsArray, $field);
												
						$field = new Field();
						$field->name = "materials_unique_id";
						$field->value = "<a href='/Materials/{$row['UNIQUE_NAME_IDENTIFIER']}/' >".$row['UNIQUE_NAME_IDENTIFIER']."</a>";
						
						array_push($fieldsArray, $field);
			
						$field = new Field();
						$field->name = "materials_name";
						$field->value = $row['NAME'];
			
						array_push($fieldsArray, $field);
						
						$field = new Field();
						$field->name = "materials_company_name";
						$field->value = $row['VENDOR_TEXT'];
						
						array_push($fieldsArray, $field);
						
						$field = new Field();
						$field->name = "materials_supplier";
						$field->value = $row['MATERIALS_SUPPLIER'];
						
						array_push($fieldsArray, $field);
						
						$field = new Field();
						$field->name = "materials_quantity";
						$field->value = $row['MATERIALS_QUANTITY'];
						
						array_push($fieldsArray, $field);

						$field = new RowProperties();
						$field->isSelected = $nechet;
						$field->name = "ROW_PROPERTIES";
		
						$fieldsArray['ROW_PROPERTIES'] = $field;

						$this->blocksMarkup .= $grid->RenderRow($fieldsArray);
					}
				
					$this->blocksMarkup .= $grid->RenderBottom();					
				}
			}

		}
		
		private function SearchResultRow($row, $index = 0)
		{
			$this->blocksMarkup .= "<div class='resultsRow'>{$index}.&nbsp;<a href='".Request::GetRoot()."/Materials/".$row['UNIQUE_NAME_IDENTIFIER']."/' >".$row['NAME']."</a>&nbsp;<ul><li onClick='cDel(\"".Security::EncodeUrlData($row['MATERIALS_ID'])."\");' title='<:=Materials_Row_Delete=:>'>×</li><li onClick='cEdit(\"".Security::EncodeUrlData($row['MATERIALS_ID'])."\");' title='<:=Materials_Row_Edit=:>' class='edit'>…</li></ul></div>";
		}

		private function RenderSearchResults($result, $all = false)
		{
			$this->blocksMarkup .= "<div id='searchResults'>";

			$materialsGroups = new MaterialsGroupsList();

			$materialsGroups->id = $materialsGroups->name = "treeParentGroupId";
			$materialsGroups->showLinks = true;




			if(strlen($this->groups_identifier) < 1)
				$materialsGroups->display = "none";

			$this->blocksMarkup .= "<div style='border:1px solid #DDF0F5; padding: 1px;'><span style='display:inline-block; width:100%; text-align:right; cursor: pointer; font-size: 13px;' onclick='Slide(310, \"treeElements\", \"<:=Materials_Close=:>\", this); return false;'><:=Materials_Open=:></span>";
			$this->blocksMarkup .= $materialsGroups->GetMaterialsGroupsTree($this->groups_identifier);
			$this->blocksMarkup .= "</div>";

			if($result != NULL && mysql_num_rows($result) > 0)
			{
				$materialsCount = $this->materials->RowsCount();

				$local = $this->GetLocalValue('Materials_Edit');

				$this->page->commonScripts .= 'function cDel(id) { if(confirm("'.$this->GetLocalValue("Materials_Are_You_Sure_Delete").'") )  AjaxMaterialClick(id, 1); Objects.BubbleDiv.hide(); }; function cEdit(id) { AjaxMaterialClick(id); } ';

				$i = 1;
				while($row = mysql_fetch_array($result))
				{
					$this->SearchResultRow($row, $this->materialsOffset + $i++);
				}

				if($all)
				{
					$this->blocksMarkup .= "<div class='searchIndexer'>";
					
					for($i = 0; $i < ($materialsCount/$this->materialsWindow); $i++)
					{
						$identifier = 'offset={$i}';
						if(strlen($this->groups_identifier) > 0)
						{
							$identifier .= "&groups=".$this->groups_identifier ;
						}
						
						$this->blocksMarkup .= "<a href='".Request::GetRoot()."/Materials/?{$identifier}' ".(($this->materialsOffset/$this->materialsWindow == $i) ? "class='selected'" : "");
							
						$this->blocksMarkup .= ">".($i + 1)."</a>";
					}
					
					$this->blocksMarkup .= "</div>";
				}
			}
			else
			{
				$this->blocksMarkup .= "<p><:=Materials_Nothing_To_Show=:></p>";
			}

			$this->blocksMarkup .= "</div>";
		}
		
		private function RenderMaterialsSearchResults()
		{
			$this->materials->unique_name_identifier = Request::$identifier;

			if(strlen($this->materials->unique_name_identifier) < 1 && strlen($this->groups_identifier) < 1)
			{
			
				$this->page->SetTitle($this->GetLocalValue('Materials_All'));
			
				$this->materialsOffset = ((float)$_GET['offset']) * $this->materialsWindow;

				$result = $this->materials->LoadMaterials('', $this->materialsWindow, $this->materialsOffset, '`NAME`');

				$this->RenderSearchResults($result, true);
			}
			elseif( ucfirst(strtolower($this->materials->unique_name_identifier)) == 'Latest' )
			
			{
				$this->page->SetTitle($this->GetLocalValue('Materials_Latest'));

				$result = $this->materials->LoadMaterials('', $this->materialsWindow, $this->materialsOffset, '`UPDATED_TIME`');

				$this->RenderSearchResults($result);
				
			}
			elseif(strlen($this->groups_identifier) > 0)
			{
				$this->page->SetTitle($this->GetLocalValue('Materials_Groups'));

				$this->materials->materials_groups_identifier = $this->groups_identifier;
				$result = $this->materials->LoadMaterials('', $this->materialsWindow, $this->materialsOffset, '`UPDATED_TIME`');

				$this->RenderSearchResults($result);
			}
		}

		private function RenderServicesList()
		{
			$result = $this->services->LoadServices();
			
			if($result != NULL && mysql_num_rows($result) > 0)
			{
				
			}
		}

		private function RenderMaterialsList()
		{
			$result = $this->materials->LoadMaterials();
			
			if($result != NULL && mysql_num_rows($result) > 0)
			{	
				if($this->isCustomLink)
				{
					$this->formTag = new Form();
					$this->formTag->name = "Materials_List";
					$this->formTag->id = "Materials_List";
					
					$this->blocksMarkup .= $this->formTag->RenderTop();
				}

				while($row = mysql_fetch_array($result))
				{
					if(!$this->isCustomLink)
					{
						$this->blocksMarkup .= "<a href='".$this->linkForListing.$row['UNIQUE_NAME_IDENTIFIER']."'>".$row['NAME']."</a>&nbsp;<span onClick='AjaxMaterialClick(\"".Security::EncodeUrlData($row['MATERIALS_ID'])."\");'>edit</span><br />";
					}
					else
					{
						$anchor = new Anchor();
						$anchor->SCode = $this->sCodeForListing;
						$anchor->title = $row['NAME'];
						$anchor->isTraditionalHref = false;
						$anchor->params = array("MaterialsId" => Security::EncodeUrlData($row['MATERIALS_ID']));
						$anchor->href = $this->linkForListing.$row['UNIQUE_NAME_IDENTIFIER'];
						
						$this->blocksMarkup .= $anchor->OpenTag()."".$row['NAME'];
						$this->blocksMarkup .= $anchor->CloseTag()."<br/>";
					}
				}
				
				if($this->isCustomLink)
				{
					$this->blocksMarkup .= $this->formTag->RenderBottom();
				}
			}
			else 
			{
				$this->blocksMarkup .= "<br><:=Materials_List_Is_Empty=:>";
			}
		}
		
		private function AjaxSupplier()
		{
			$this->materials->materials_id = $this->materials->user_materials_id = $materialsId = (float)Security::DecodeUrlData($_POST['MATERIALS_ID']);
			$this->materials->user_supplier_companies_id = ( strlen($_POST['companiesId']) > 0 ? (float)Security::DecodeUrlData($_POST['companiesId']) : (float)Security::DecodeUrlData($_POST['COMPANIES_ID']));
			$needSave = (bool)($_POST['needSave']);

			if($this->materials->user_supplier_companies_id > 0 )
			{
				$result = $this->materials->LoadSupplier($this->materials->materials_id, $this->materials->user_supplier_companies_id);	
			}

			$mode = ((Request::GetSCode() == $this->SCodes['ajax_addsupplier']) || (Request::GetSCode() == $this->SCodes['ajax_editsupplier']));

			if($this->isValidPost && (Request::GetSCode() == $this->SCodes['ajax_addsupplier'] ) 
				|| $this->isValidPost && (Request::GetSCode() == $this->SCodes['ajax_editsupplier'] ) && $this->materials->isLoaded && !$needSave 			
				|| $this->isValidPost && (Request::GetSCode() == $this->SCodes['ajax_editsupplier'] ) && !$this->ValidateSupplierForm() && $needSave)
			{

				$this->formTag = new Form();
				$companiesList = new CompaniesList();
				$companiesList->name = $companiesList->id = "materials_supplier_id";

				$userDivisibility = new DivisibilityList();
				$userDivisibility->name = $userDivisibility->id = "materials_users_divisibility_id";

				$blockSearch = new BlockSearch ();
				$blockSearch->page = $this->page;
				$blockSearch->BlockMode = RENDER_COMPANIES_SEARCH_INPUT;
				$blockSearch->inputId = "companiesInput";
				$blockSearch->inputText = $this->materials->user_supplier_companies_name;
				$blockSearch->BlockInit();

				$this->blocks['BLOCK_SEARCH'] = $blockSearch;
				
				$this->blocksMarkup .= "<input type='hidden' id='needSave' name='needSave' value='1'>";

				if(!$needSave)
					$this->blocksMarkup .= "<table id='materialsFields'>";

				$this->blocksMarkup .= "<tr><td><:=Materials_Companies_Name=:></td><td><nobr><input type='hidden' id='companiesId' name='companiesId' value='".Security::EncodeUrlData((float)$this->materials->user_supplier_companies_id)."'><:=BLOCK_SEARCH=:>".$this->formTag->RenderAsterisks($this->fieldsArray['materials_companies_id'])."</nobr></td></tr>";//".$companiesList->GetCompaniesFullList((float)$this->materials->user_supplier_companies_id).$this->formTag->RenderAsterisks($this->fieldsArray['materials_user_quantity'])."</td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_User_Comment=:></td><td><textarea id='materials_user_comment' name='materials_user_comment' cols='30' rows='5' ".($isReadOnly ? "readonly='readonly'" : "").">".$this->materials->user_comment."</textarea></td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_User_Divisibility=:></td><td>".$userDivisibility->GetDivisibilitiesList($this->materials->user_divisibility_id).$this->formTag->RenderAsterisks($this->fieldsArray["materials_users_divisibility_id"])."</td></tr>";
				$this->blocksMarkup .= "<tr style='border-bottom:1px;'><td><:=Materials_User_Quantity=:></td><td><input type='text' id='materials_user_quantity' name='materials_user_quantity' value='".$this->materials->user_quantity."'>".$this->formTag->RenderAsterisks($this->fieldsArray['materials_user_quantity'])."</td></tr>";

				$this->page->commonScripts .= "SearchInit();";

				if(!$needSave)
				{
					$this->blocksMarkup .= "</table>";
	
					if($this->isAjaxRequest)
					{ 
						$link = new Anchor();

						$link->SCode = $this->SCodes['ajax_editsupplier'];
						$link->title = "<:=Supplier_Create_Link=:>";
						$link->href = Request::$url;
						$link->hrefAJAX = Request::$url; 
						$link->isTraditionalHref = false;
						$link->refreshElementId = 'materialsFields';
						$link->getParamsValues = true;

						$link->applyScripts = false; 
						//$link->onClick = "return false;";
						$link->params = array("MATERIALS_ID" => '', "needSave" => '', "companiesId" => '', "companiesInput" => '', 'materials_user_comment' => '', 'materials_users_divisibility_id' => '', 'materials_user_quantity' => '');

						$this->blocksMarkup .= "<table style='float:right;'><tr><td colspan='2'>".$link->OpenTag()."<:=Supplier_Create_Link=:>".$link->CloseTag()."</td></tr></table>";

						$this->page->commonScripts .= $link->appendClientScript." Objects.Environment['MATERIALS_ID']='".Security::EncodeUrlData($materialsId)."';";
					}
				}

				if($this->isAjaxRequest)
				{
					$this->blocksAjaxMarkup = $this->blocksMarkup;
					$this->blocksMarkup = "";
				}
			}
			elseif($this->isValidPost && $this->isValidForm && (Request::GetSCode() == $this->SCodes['ajax_editsupplier'] ))
			{
				if($materialsId > 0 && ($id = $this->materials->CreateMaterial(true, true)) < 1) 
				{
					$this->blocksAjaxMarkup .= "<:=Supplier_Is_Not_Created=:>";
				}
				else
				{
					$this->page->commonScripts .= "Objects.BubbleDiv.hide();";
				}				
			}
			elseif($this->isValidPost && (Request::GetSCode() == $this->SCodes['ajax_delsupplier']))
			{
				$message = "";
				if($materialsId > 0 && ($this->materials->DeleteSupplier() == true) )
				{
					$message = "<:=Supplier_Is_Deleted=:>";
				}
				else
				{
					$message = "<:=Supplier_Is_Not_Deleted=:>";
				}

				$this->page->commonScripts .= "alert('".$message."'); Objects.BubbleDiv.hide();";

			}
		}
		
		private function AddSupliersMaterials()
		{
			$this->isSupplier = true;

			$this->AddMaterials();
		}
		
		private function EditMaterial()
		{
			if((float)$this->materialsId < 1 && strlen($this->uniqueIdentifier) == 0)
			{
				return;
			}
			return $this->AddMaterials();
		}
		
		public function RenderMaterialsGroups($id)
		{
			$result = "";

			$materialsGroups = new MaterialsGroupsList();
			$materialsGroups->name = $materialsGroups->id = "materials_groups_id";

			$isReadonly = !Session::IsSuperAdmin();
			if(!$isReadonly)
			{
				$needDiv = $this->isValidPost && Request::GetSCode() != $this->SCodes['ajax_addgroupsname'];
				if($needDiv)
					$result .= "<div id='groupsFields'>";

				if($this->isValidPost && Request::GetSCode() == $this->SCodes['ajax_addgroupsname'] && $this->ValidateNewGroup())
				{
					$materials = new Materials();
					
					$materials->groups_name = $this->newGroupName;
					$materials->groups_parent = $this->newGroupId;

					if($id = $materials->SaveMaterialsGroup() < 1)
					{
						$this->page->commonScripts .= "alert('".$this->GetLocalValue('Materials_Group_Save_Error')."')";
					}
				}

				$this->formTag = new Form();

				$result .= "<input type='text' value='".$this->newGroupName."' name='groupName' id='groupName'>";
				$result .= $this->formTag->RenderAsterisks($this->fieldsArray['groupName']);
													
				$materialsGroups->id = "parentGroup";
				$materialsGroups->allowSelect = true;
				$materialsGroups->width = "700px";

				$result .= $materialsGroups->GetMaterialsGroupsList($id).$this->formTag->RenderAsterisks($this->fieldsArray['parentGroup']);
				
				$link = new Anchor();
					
				$link->SCode = $this->SCodes['ajax_addgroupsname'];
				$link->title = "<:=Materials_Add_Groups_Name=:>";
				$link->href = Request::$url;
				$link->hrefAJAX = Request::$url;
				$link->isTraditionalHref = false;
				$link->refreshElementId = "groupsFields";
				$link->getParamsValues = true;
				$link->class = "ajaxLink";
				$link->applyScripts = false;

				$link->params = array("groupName" => "", "parentGroup" => "");

				$result .= "&nbsp;".$link->OpenTag()."<:=Materials_Add_Groups_Name=:>".$link->CloseTag();
				$this->page->commonScripts .= $link->appendClientScript.";";
			}

			$materialsGroups->id = $materialsGroups->name = "treeParentGroupId";
			$result .= $materialsGroups->GetMaterialsGroupsTree($id	);

			if($needDiv)
				$result .= "</div>";
			
			return $result;
		}
		
		private function AddMaterials()
		{

			if($this->page->isValidPost && Request::GetSCode() == $this->SCodes['ajax_addgroupsname'])
			{
				$this->blocksAjaxMarkup = $this->RenderMaterialsGroups($id);
				return ;
			}
			
			if($this->page->isValidPost )
			{			
				$this->materials->user_materials_id = $this->materials->materials_id = ((strlen($_POST['MaterialsId']) > 0) ? (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['MaterialsId'])) : (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['MATERIALS_ID'])) );

				if($this->isSupplier && $this->materials->user_supplier_companies_id == 0 && $this->supplierCompaniesId == 0)
				{
				// Need to parse post Companies Id
					$this->supplierCompaniesId = $this->materials->user_supplier_companies_id = (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['CompaniesId']));
				}
				elseif($this->isSupplier && $this->supplierCompaniesId > 0)
				{
				// Don't need the post Companies Id
					$this->materials->user_supplier_companies_id = $this->supplierCompaniesId;
				}
				
				$this->materials->GetMaterialsById();
				
				if($this->materials->user_materials_id == 0) // Not found the materials quantity. Need reinit
				{
					$this->materials->user_materials_id = $this->materials->materials_id;
				}
				
				if($this->isSupplier && $this->materials->user_supplier_companies_id == 0)
				{
					$this->materials->user_supplier_companies_id = $this->supplierCompaniesId;
				}
				
			}
			elseif((float)$this->materialsId > 0)
			{
				$this->materials->materials_id = $this->materialsId;
				$this->materials->GetMaterialsById();				
			}
			elseif(strlen($this->uniqueIdentifier) > 0)
			{
				$this->materials->unique_name_identifier = $this->uniqueIdentifier;
				$this->materials->ValidateUniqueIdentifier(true); // Initialize the class if material is exists
			}
			elseif(strlen($_POST['MATERIALS_ID']) > 0 || strlen($_POST['MaterialsId']) > 0)
			{
				$this->materials->user_materials_id = $this->materials->materials_id = ((strlen($_POST['MaterialsId']) > 0) ? (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['MaterialsId'])) : (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['MATERIALS_ID'])) );
				$this->materials->GetMaterialsById();
			}
			
			
			if(!$this->materials->isLoaded)
			{
				$sCode = sha1("SubMitNewMateRialtODb");
			}
			else
			{
				$sCode = sha1("SavEeXisTingMateRial");
			}

			if(!$this->page->isValidPost /*|| $this->page->isValidPost && $this->steps->isLoaded*/ || $this->page->isValidPost && !$this->ValidateForm())
			{
				$this->formTag = new Form();
				$this->formTag->name = "Materials_Form";
				
				if(!$this->isAjaxRequest)
				{
					$this->blocksMarkup = $this->formTag->RenderTop();
				}
				else
				{
					$this->blocksMarkup = "<div id='materialsFields' style='height:400px;overflow:auto;'>";
				}
				
				$materialsGroups = new MaterialsGroupsList();
				$materialsGroups->name = $materialsGroups->id = "materials_groups_id";

				$isReadOnly = false;
				
				if(!$this->isAjaxRequest)
				{
					$hdn = new Hidden();
					$hdn->SetName("__SCode");
					$hdn->SetValue($sCode);
				
					$this->blocksMarkup .= $hdn->OpenTag();

					if($this->materials->materials_id > 0)
					{
						$hdn = new Hidden();
						$hdn->SetName("MaterialsId");
						$hdn->SetValue(Security::EncodeUrlData($this->materials->materials_id));
					
						$this->blocksMarkup .= $hdn->OpenTag();
					
						$isReadOnly = $this->materials->is_approved;
					}

					if($this->steps->steps_id > 0)
					{
						$hdn = new Hidden();
						$hdn->SetName("StepsId");
						$hdn->SetValue($this->steps->steps_id);
					
						$this->blocksMarkup .= $hdn->OpenTag();
					}
				
					if($this->materials->user_supplier_companies_id > 0)
					{
						$hdn = new Hidden();
						$hdn->SetName("CompaniesId");
						$hdn->SetValue(Security::EncodeUrlData($this->materials->user_supplier_companies_id));
					
						$this->blockMarkup .= $hdn->OpenTag();
					}
				}
				elseif($this->materials->materials_id > 0)
				{
					$isReadOnly = $this->materials->is_approved;
				}

				
				$countriesList = new CountriesList();				
				$countriesList->width = "599px";

				$divisibilityList = new DivisibilityList();
				$divisibilityList->name = $divisibilityList->id = "materials_divisibility_id";
				
				$userDivisibility = new DivisibilityList();
				$userDivisibility->name = $userDivisibility->id = "materials_users_divisibility_id";
				
				if($isReadOnly)
				{
					$countriesList->tagAttributes['disabled'] = "disabled";
					$divisibilityList->tagAttributes['disabled'] = "disabled";
					$materialsGroups->tagAttributes['disabled'] = "disabled";
				}				

				$this->blocksMarkup .= "<table>";

				$this->blocksMarkup .= "<tr><td><:=Materials_Groups_Id=:>".$this->formTag->RenderAsterisks($this->fieldsArray['materials_group'])."</td><td>".$this->RenderMaterialsGroups($this->materials->materials_groups_id)/*$materialsGroups->GetMaterialsGroupsList($this->materials->materials_groups_id)." ".$this->formTag->RenderAsterisks($this->fieldsArray['materials_group'])*/."</td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_Name=:></td><td><input type='text' id='materials_name' name='materials_name' ".($isReadOnly ? "readonly='readonly'" : "")." value='".$this->materials->name."'>".$this->formTag->RenderAsterisks($this->fieldsArray['materials_name'])."</td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_Unique_Name=:></td><td><input type='text' id='materials_unique_name' name='materials_unique_name' ".($isReadOnly ? "readonly='readonly'" : "")." value='".$this->materials->unique_name_identifier."'>".$this->formTag->RenderAsterisks($this->fieldsArray['materials_unique_name'])."</td></tr>";
				
				$this->blocksMarkup .= "<tr><td><:=Materials_WWW=:></td><td><input type='text' id='common_www' name='common_www' value='".$this->materials->common_www."' />".$this->formTag->RenderAsterisks($this->fieldsArray['common_www'])."</td></tr>";

				if(strlen($_POST['COMPANIES_ID']) > 0)
				{
					$company = new Companies();
					$company->companies_id = (float)Security::DecodeUrlData($_POST['COMPANIES_ID']);
					$dr = $company->GetCompanyById();

					$this->materials->vendor_text = $company->name;
				}
				
				$this->blocksMarkup .= "<tr><td><:=Materials_Country_Name=:></td><td>".$countriesList->GetCountriesList($this->materials->original_countries_id)."".$this->formTag->RenderAsterisks($this->fieldsArray["country_name_field"])."</td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_Vendor_Company=:></td><td><input type='text' id='materials_vendor_name' name='materials_vendor_name' ".($isReadOnly ? "readonly='readonly'" : "")." value='".$this->materials->vendor_text."'>".$this->formTag->RenderAsterisks($this->fieldsArray['materials_vendor_name'])."</td></tr>"; // add new vendor or select from the list
				$this->blocksMarkup .= "<tr><td><:=Materials_Divisibility_Name=:></td><td>".$divisibilityList->GetDivisibilitiesList($this->materials->divisibility_id)." ".$this->formTag->RenderAsterisks($this->fieldsArray['materials_divisibility_id'])."</td></tr>";				

				$this->blocksMarkup .= "<tr><td><:=Materials_Comment=:></td><td><textarea name='materials_comment' id='materials_comment' cols='30' rows='5' ".($isReadOnly ? "readonly='readonly'" : "").">".$this->materials->comment."</textarea></td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_IsPublic=:></td><td><input type='checkbox' name='materials_ispublic' id='materials_ispublic'  ".($isReadOnly ? "disabled='disabled'" : "")." value='".($this->materials->is_public ? "on" : "")."'></td></tr>";
				
				if($this->isSupplier)
				{
					$companiesList = new CompaniesList();
					$companiesList->name = $companiesList->id = "materials_supplier_id";
				
					$this->blocksMarkup .= "<tr><td colspan='2'><hr /></td></tr>";
					$this->blocksMarkup .= "<tr><td><:=Materials_Companies_Name=:></td><td>".$companiesList->GetCompaniesFullList((float)$this->materials->user_supplier_companies_id)."</td></tr>";
					$this->blocksMarkup .= "<tr><td><:=Materials_User_Comment=:></td><td><textarea id='materials_user_comment' name='materials_user_comment' cols='30' rows='5' ".($isReadOnly ? "readonly='readonly'" : "").">".$this->materials->user_comment."</textarea></td></tr>";				
					$this->blocksMarkup .= "<tr><td><:=Materials_User_Divisibility=:></td><td>".$userDivisibility->GetDivisibilitiesList($this->materials->user_divisibility_id).$this->formTag->RenderAsterisks($this->fieldsArray['materials_user_divisibility_id'])."</td></tr>";
					$this->blocksMarkup .= "<tr style='border-bottom:1px;'><td><:=Materials_User_Quantity=:></td><td><input type='text' id='materials_user_quantity' name='materials_user_quantity' value='".$this->materials->user_quantity."'>".$this->formTag->RenderAsterisks($this->fieldsArray['materials_user_quantity'])."</td></tr>";
					$this->blocksMarkup .= "<tr><td colspan='2'><hr /></td></tr>";
				}
				
				$this->blocksMarkup .= "</table>";

				if($sCode != Request::GetSCode() )
				{
					$this->blocksMarkup .= "</div>";

					if($this->isAjaxRequest && Request::GetSCode() != $sCode)	
					{
						$link = new Anchor();
						$link->SCode = $sCode;

						if(!$this->materials->isLoaded)
						{
							$link->title = "<:=Projects_Create_Link=:>";
						}
						else
						{
							$link->title = "<:=Projects_Save_Link=:>";
						}
						$link->href = Request::$url;
						$link->hrefAJAX = Request::$url; 
						$link->isTraditionalHref = false;
						$link->refreshElementId = 'materialsFields';
						$link->getParamsValues = true;
						$link->applyScripts = false; 
						$link->class = "ajaxLink";
						//$link->onClick = "return false;";
						$link->params = array("MATERIALS_ID" => '', "COMPANIES_ID" => '', "treeParentGroupId" => '', "materials_divisibility_id" => '', 'common_www' => '', 'materials_unique_name' => '', 'materials_users_divisibility_id' => '', 'materials_vendor_name' => '', 'materials_user_quantity' => '', 'materials_is_approved' => '', 'materials_supplier_id' => '', 'materials_user_comment' => '', 'materials_ispublic' => '', 'materials_comment' => '', 'materials_name' => '', 'projects_edate' => '', 'projects_country_field' => '', 'projects_city_id' => '', 'projects_comment' => '', 'materials_groups_id' => '', 'country_name_field' => '');

						$this->blocksMarkup .= "<table style='float:right;'><tr><td colspan='2' >".$link->OpenTag().$link->title.$link->CloseTag()."</td></tr></table>";

						$this->page->commonScripts .= $link->appendClientScript." Objects.Environment['MATERIALS_ID'] = '".Security::EncodeUrlData($this->materials->materials_id)."'";
					}
					else
					{
						$this->blocksMarkup .= "<table><tr><td colspan='2' >".$this->formTag->RenderSubmitButton("<:=Projects_Next_Step=:>")."</td></tr></table>";
					}
				}
				
				if($this->isAjaxMarkup)
				{
					$this->blocksMarkup .= $this->formTag->RenderSubmitButton("<:=Materials_Submit_Name=:>");

					$this->blocksMarkup .= $this->formTag->RenderBottom();
					
				}
			}
			elseif($this->page->isValidPost && $this->isValidForm && (Request::GetSCode() == sha1("SubMitNewMateRialtODb")) )
			{
				if(!$this->materials->isLoaded)
				{
					if(($id = $this->materials->CreateMaterial($this->isSupplier)) < 1)
					{					
						$this->blocksMarkup .= "<:=Material_Is_Not_Created=:>";
					}
					else
					{
						//Search engine operations
						$search = new SearchOperator();

						$index_data = array("object_id" => $this->materials->materials_id, "object_quid" => $this->materials->materials_guid, "entity_id" => Enumerator::Entity('MAT')->id );
						$document_text = $this->materials->name." ".$this->materials->unique_name_identifier." ".$this->materials->vendor_text." ".$this->materials->comment." ".$this->materials->user_comment." ".$this->materials->LoadDivisibilityName()." ".$this->materials->LoadGroupsName()." ".$this->materials->LoadCountryName();
						$search->ParseDocument($document_text, $index_data, 0);
					
						$this->blocksMarkup .= "<:=Material_Is_Created_Ok=:>";

						$this->page->commonScripts = "Objects.BubbleDiv.hide();";
					}
					
					if($this->isAjaxRequest)
					{
						$this->blocksAjaxMarkup = $this->blocksMarkup;
						$this->blocksMarkup = "";
					}
				}
				else
				{
					//Mistake in request
				}
			}
			elseif($this->page->isValidPost && $this->isValidForm && (Request::GetSCode() == sha1("SavEeXisTingMateRial")) )
			{
				if($this->materials->isLoaded)
				{
					$this->materials->EditMaterial($this->isSupplier);

					//Search engine operations
					$search = new SearchOperator();
					
					$index_data = array('object_id' => $this->materials->materials_id, 'object_guid' => $this->materials->materials_guid, 'entity_id' => Enumerator::Entity('MAT')->id );
					$document_text = $this->materials->name." ".$this->materials->unique_name_identifier." ".$this->materials->vendor_text." ".$this->materials->comment." ".$this->materials->user_comment." ".$this->materials->LoadDivisibilityName()." ".$this->materials->LoadGroupsName()." ".$this->materials->LoadCountryName();
					$search->ParseDocument($document_text, $index_data, 0);

					if($this->isAjaxRequest)
					{
						$this->blocksAjaxMarkup = $this->blocksMarkup;
						$this->page->commonScripts .= "Objects.BubbleDiv.hide();";
					}
					
				}
				else 
				{
					// Error. Wrong material Id	
				}
			}
			
			if($this->isAjaxRequest)
			{
				$this->blocksAjaxMarkup = $this->blocksMarkup;
				$this->blocksMarkup = "";
			}
		}
		
		private function RenderAlbum($materialsId = 0, $albumsId = 0)
		{
			$album = new BlockAlbum();
			$album->page = $this->page;
			$album->albumsHeight = 150;
			$album->selectedObjectsTitle = $this->materials->name;
			$album->selectedObjectsId = (float)$materialsId;
			$album->selectedEntityId = 2;
			$album->selectedAlbumsId = (float)$albumsId;
			$album->showAlbums = false;
			$album->BlockMode = RENDER_COMMON_ALBUM;
			$album->BlockInit();
			
			if(!$this->isAjaxRequest)
				$this->blocksMarkup .= "<br/><br/><:=BLOCK_ALBUM=:>";
			else
				$this->blocksAjaxMarkup .= "<:=BLOCK_ALBUM=:>";

			$this->blocks['BLOCK_ALBUM'] = $album;
		}

		private function MaterialsDetails()
		{
				$albumsId = 0;
				if(strlen(Request::$identifier) > 0)
				{
					$this->materials->unique_name_identifier = $this->materials->user_materials_unique_name = Request::$identifier;
					$this->materials->ValidateUniqueIdentifier(true);
				}
				
				if($this->isValidPost && Request::GetSCode() == sha1("sAveFilEtoDatAbaSe"))
				{
					$this->RenderAlbum((float)Security::DecodeUrlData($_POST['OBJECTS_ID']), (float)Security::DecodeUrlData($_POST['ALBUMS_ID']));
				}
				else {
					$this->page->SetTitle($this->materials->name);

					$this->blocksMarkup .= "<table id='supplierFields'>";
					$this->blocksMarkup .= "<tr><td><:=Materials_Groups_Id=:></td><td>".$this->materials->LoadGroupsName()."</td></tr>";
					$this->blocksMarkup .= "<tr><td><:=Materials_Name=:></td><td>".$this->materials->name."</td></tr>";
					$this->blocksMarkup .= "<tr><td><:=Materials_Unique_Name=:></td><td>".$this->materials->unique_name_identifier."</td></tr>";

					$this->blocksMarkup .= "<tr><td><:=Materials_Country_Name=:></td><td>".$this->materials->LoadCountryName()."</td></tr>";
					$this->blocksMarkup .= "<tr><td><:=Materials_Vendor_Company=:></td><td>".$this->materials->vendor_text."</td></tr>"; // add new vendor or select from the list
					$this->blocksMarkup .= "<tr><td><:=Materials_WWW=:></td><td><a href='".$this->materials->common_www."' target='_block'>".$this->materials->common_www."</a></td></tr>";
					$this->blocksMarkup .= "<tr><td><:=Materials_Divisibility_Name=:></td><td>".$this->materials->LoadDivisibilityName()." </td></tr>";				

					$this->blocksMarkup .= "<tr><td><:=Materials_Comment=:></td><td>".preg_replace("/[\\\]/", "", preg_replace("/[n]/i", "<br>", $this->materials->comment))."</td></tr>";
					$this->blocksMarkup .= "</table>";
					
					$this->RenderAlbum($this->materials->materials_id, 0);
					
					$this->blocksMarkup .= "<br><table id='supplierFields'><tr><td><:=Materials_Suppliers=:>&nbsp;<span style='cursor:pointer;' onClick='AjaxAddSupplier(\"".Security::EncodeUrlData($row['MATERIALS_ID'])."\");'>+</span></td></tr>";
					
					$result = $this->materials->LoadMaterialsSuppliers($this->materials->materials_id);

					if($result != NULL)
					{
						while($row = mysql_fetch_array($result))
						{
							$this->RenderSupplierRow($row);
						}
					}
					
					$this->blocksMarkup .= "</table>";	

					$this->page->commonScripts .= '</script><script> function SuppliersEdit(sid, mode) 
					{
						
						if(mode == 1 && !confirm("'.$this->GetLocalValue("Are_You_Sure_Delete_Supplier").'"))
							return ;						

						var rMode = ((mode == 1) ? "'.$this->SCodes['ajax_delsupplier'].'" : "'.$this->SCodes['ajax_editsupplier'].'");

						var parameters = { 	
							__SVar  		: Objects.Security.secureServerVar, 
							__SCode 		: rMode, 
							__ClientVar 	: Objects.Security.createSecureVar(),
							IS_AJAX 		: "TRUE", 
							COMPANIES_ID	: sid, 
							MATERIALS_ID	: "'.Security::EncodeUrlData($this->materials->materials_id).'" 
						}

						var rq = new Ajax.Request(document.location.href, {
		
							parameters : parameters,
						
							onCreate: function ()
							{
								Objects.BubbleDiv.height = 420;
								Objects.BubbleDiv.width = 580;
								Objects.BubbleDiv.show();
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
										Objects.BubbleDiv.refresh(rObject.text);
								
										if(rObject.scripts.length > 0)
											eval(rObject.scripts[0]); 
								

										if(this.successMethod)
											eval(this.successMethod);
									}
									else
									{
										alert(rObject + "_" + response.responseText);
									}
			
								}
								catch(ex)
								{
									FireError(ex);
								}
								}
								});
						}
						
					function AjaxAddSupplier(id)
					{
						var parameters = {
							__SVar		: Objects.Security.secureServerVar,
							__SCode		: "'.sha1("AddAjaxSupPliEr").'",
							__ClientVar	: Objects.Security.createSecureVar(),
							MATERIALS_ID: id,
							IS_AJAX		: \'TRUE\'
						};

						var rq = new Ajax.Request(document.location.href, { parameters : parameters, 
							onCreate : function () 
							{
								Objects.BubbleDiv.height = 300;
								Objects.BubbleDiv.width = 580;
								Objects.BubbleDiv.title = "'.$this->GetLocalValue('Materials_Supplier_Title').'";
								Objects.BubbleDiv.show();
							},
							
							onSuccess : function (response) 
							{
								try 
								{
									var rObject = Objects.Security.validateResponse(response.responseJSON);

									if(rObject != null && rObject.isSecured)
									{
										Objects.BubbleDiv.refresh(rObject.text);

										if(rObject.scripts.length > 0)
											eval(rObject.scripts[0]);
									}
									else
									{
										alert(rObject + "_" + response.responseText);
									}
								}
								catch(ex)
								{
									FireError(ex);
								}
							}
						} );
					}	</script>';
				}

		}
		
		public function RenderMainMaterials()
		{
		
			$this->blocksMarkup .= "<div>";
			
			$this->materials = new Materials();
			$result = $this->materials->LoadMaterials('', 10, 0, ' `UPDATED_TIME` ');
			
			if($result != NULl && mysql_num_rows($result) > 0)
			{
				$this->blocksMarkup .= "<div class='mHead'><span><:=Materials_Latest=:></span><a href='/Materials/All/'><:=Materials_All=:></a></div>";
				
				while($row = mysql_fetch_array($result))
				{
					$this->blocksMarkup .= "<div class='row'><a href='/Materials/{$row['UNIQUE_NAME_IDENTIFIER']}'>{$row['NAME']}</a> ({$row['VENDOR_TEXT']})</div>";
				}
			}
			
			$this->blocksMarkup .= "</div>";
			
		}

		private function RenderSupplierRow($row)
		{
			$this->blocksMarkup .= "<tr><td><a href='".Request::GetRoot()."/Companies/{$row['UNIQUE_NAME_IDENTIFIER']}/' >{$row['NAME']}</a> <span onClick='SuppliersEdit(\"".Security::EncodeUrlData($row['COMPANIES_ID'])."\", 0);'>edit</span><span onClick='SuppliersEdit(\"".Security::EncodeUrlData($row['COMPANIES_ID'])."\", 1);'>del</span></td></tr>";	
		}
				
		private function SelectMaterialsOld()
		{
			if(!$this->page->isValidPost || $this->page->isValidPost && (Request::GetSCode() != sha1("SubMitSelEctEdMateRiAls")))
			{
				$this->formTag = new Form();
				$this->formTag->name = "Materials_Selection";
	
				$mSelect = new Select();
				$mSelect->tagAttributes['name'] = 'materials_list[]';
				$mSelect->isCombobox = false;
	
				$this->materials = new Materials();
				$result = $this->materials->LoadMaterials($filter_name, $filter_limit, $filter_offset);
					
				if(mysql_num_rows($result) > 0)
				{
					$this->blocksMarkup .= $this->formTag->RenderTop();
						
					$hdn = new Hidden();
					$hdn->SetName("__SCode");
					$hdn->SetValue(sha1("SubMitSelEctEdMateRiAls"));
						
					$this->blocksMarkup .= $hdn->OpenTag();
						
					while($row = mysql_fetch_array($result))
					{
						$mId = Security::EncodeUrlData($row["MATERIALS_ID"]);
						$opt = array("Title" => $row["NAME"], "Value" => $mId, "Id" => $mId, "Selected" => false);
		 				array_push($mSelect->optionsArray, $opt);
					}
						
					$this->blocksMarkup .= $mSelect->RenderTop().$mSelect->RenderBottom();
		
					$this->blocksMarkup .= "<br>".$this->formTag->RenderSubmitButton("<:=SUBMIT_SELECTION_BUTTON=:>");
				}
			}
		}
		
		private function ValidateSelectAjaxMaterialsForm()
		{
			$this->isValidForm = false;
			
			if(!in_array(Request::GetSCode(), $this->SCodes))
			{
				$this->isValidForm = false;
				return $this->isValidForm;
			}
			
			$this->projects->prjmaterials_suppliers_id = (float) Security::DecodeUrlData($_POST['SUPPLIERS_ID']);
			$this->projects->prjmaterials_materials_id = (float) Security::DecodeUrlData($_POST['MATERIALS_ID']);
			$this->materialsUsersDivisibilityId = $this->projects->prjmaterials_divisibility_id = (float)StaticDatabase::CleanupTheField($_POST['materials_users_divisibility_id']);
			$this->projects->prjmaterials_projects_id = (float)$this->selectedPrjId; //(PROJECTS_ID	)
			$this->projects->prjmaterials_projects_steps_id = 0;
			$this->materialsToDate = $this->projects->prjmaterials_request_time = strtotime(StaticDatabase::CleanupTheField($_POST['request_time']));
			
			if($this->projects->prjmaterials_request_time < 1)
			{
				$this->fieldsArray["request_time"] = "<:=Validation_DateTime_Error=:>";
				$this->isValidForm = false;

				$this->projects->prjmaterials_request_time = mktime(0, 0, 0, 8, 13, 2010);//StaticDatabase::CleanupTheField($_POST['projects_sdate']);
			}
			else
			{
				$this->isValidForm = true;
			}

			$this->projects->prjmaterials_fact_time = 0;
			$this->projects->prjmaterials_is_used = false;
			$this->projects->prjmaterials_quantity = (float)StaticDatabase::CleanupTheField($_POST['materialsQuantity']);
			$this->projects->prjmaterials_ord = 0;
			$this->projects->prjmaterials_materials_unique_name = "";
			$this->projects->prjmaterials_comment = substr(StaticDatabase::CleanupTheField($_POST['materialsComment']), 0, 5000);
						
			$this->materialsInput = substr(StaticDatabase::CleanupTheField($_POST['materialsInput']), 0, 200);
			
			return $this->isValidForm;			
		}
				
		private function SelectAjaxMaterials()
		{
			$blockSearch = new BlockSearch ();
			$blockSearch->page = $this->page;
			$blockSearch->BlockMode = RENDER_MATERIALS_SEARCH_INPUT;
			$blockSearch->inputId = "materialsInput";
			$blockSearch->inputText = "";
			$blockSearch->BlockInit();

			$this->blocks['BLOCK_SEARCH'] = $blockSearch;
			
			if(!$this->isAjaxRequest)
			{
				$this->page->commonScripts .= BlockSearch::InitScripts(RENDER_MATERIALS_SEARCH_INPUT);
				$this->page->commonScripts .= " function SuppliersClick(elmt, id) { $('m_add_button').style.display = ''; Objects.Environment['SUPPLIERS_ID']=id; var suppliers = $$('div.selectedSupplier'); for(i = 0; i < suppliers.length; i++) { suppliers[i].className=''; }; elmt.className='selectedSupplier'; } ;";
				$this->page->commonScripts .= ' function SelectMaterialsClick() { 

					var parameters = { 	
						__SVar  	: Objects.Security.secureServerVar, 
		  				__SCode 	: "'.$this->SCodes['ajax_bubble'].'", 
				  		__ClientVar : Objects.Security.createSecureVar(),
				  		IS_AJAX 	: "TRUE"
				  	}

					var rq = new Ajax.Request(document.location.href, {
	
						parameters : parameters,
				  	
			  			onCreate: function ()
			  			{
							Objects.BubbleDiv.height = 300;
							Objects.BubbleDiv.width = 480;
							Objects.BubbleDiv.show();
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
					  				Objects.BubbleDiv.refresh(rObject.text);
							
					  				if(rObject.scripts.length > 0)
						  				eval(rObject.scripts[0]); 
						  	

									if(this.successMethod)
										eval(this.successMethod);
								}
								else
								{
									alert(rObject + "_" + response.responseText);
								}
		
							}
							catch(ex)
							{
								FireError(ex);
							}
		    				}
    						});
					 }';
			}
			
			$isCreate = ($this->SCodes['ajax_create'] == Request::GetSCode());
			
			if($this->showInBubble && (!$this->page->isValidPost || $this->page->isValidPost && !in_array(Request::GetSCode(), $this->SCodes)) )
			{
				$anchor = new Anchor();
				$anchor->id = "m_add_button";
				$anchor->title = "<:=Materials_Add_Button=:>";
				$anchor->hrefAJAX = Request::$url;
				$anchor->isTraditionalHref = false;
				$anchor->class = "ajaxLink";
				$anchor->onClick = "SelectMaterialsClick(); return false;";
				
				$this->blocksAjaxMarkup .= "<br />".$anchor->OpenTag()."<:=Materials_Add_Button=:>".$anchor->CloseTag()."<br/>";
					
			}
			elseif(!$this->page->isValidPost || $this->page->isValidPost && !in_array(Request::GetSCode(), $this->SCodes) || 
					($this->page->isValidPost && $this->SCodes['ajax_bubble'] == Request::GetSCode()) || $isCreate && $this->page->isValidPost && !$this->ValidateSelectAjaxMaterialsForm())
			{
				$this->formTag = new Form();
				$this->formTag->name = "Materials_Selection";
			
				if(!$isCreate)
				{			
					$this->blocksMarkup .= "<:=BLOCK_SEARCH=:>";
									
					$this->blocksMarkup .= "<div id='suppliersList' style='width: 200px;'></div>";
				}
				else
				{
					$this->blocksMarkup .= "";
				}
								
				$divisibilityList = new DivisibilityList();
				$divisibilityList->id = $divisibilityList->name = "materials_users_divisibility_id";
				$divisibilityList->tagAttributes['style'] = "width: 50px;";

				if(!$this->isAjaxRequest || !$isCreate)
				{
					$this->blocksMarkup .= "<div id='mainFields' style='display:none;'>";
				}
				
				if($this->materialsToDate > 0)
					$date = date('Y-m-d', $this->materialsToDate);
				else
					$date = "";
				
				
				$this->blocksMarkup .= "<:=Materials_To_Date=:>&nbsp;<input type='text' name='request_time' id='request_time' value='".$date."'>".$this->formTag->RenderAsterisks($this->fieldsArray['request_time'])."<br>";
				$this->blocksMarkup .= "<:=Materials_Quantity=:><input type='text' name='materialsQuantity' id='materialsQuantity' value='".(float)$this->projects->prjmaterials_quantity."' size='5'>&nbsp;".$divisibilityList->GetDivisibilitiesList($this->materialsUsersDivisibilityId)."<br>";
				$this->blocksMarkup .= "<:=Materials_Comment=:><br><textarea cols='30' rows='5' id='materialsComment'>".$this->projects->prjmaterials_comment."</textarea>";
				
				if(!$this->isAjaxRequest)
				{
					$this->blocksMarkup .= "</div>";
				}

				$this->page->commonScripts .= "SearchInit();";
	
				//if(!$this->isAjaxRequest )
				//{		
					$anchor = new Anchor();
					$anchor->id = "m_add_button";
					$anchor->tagAttributes['style'] = "display:none";
					$anchor->SCode = $this->SCodes['ajax_create'];
					$anchor->title = "<:=Materials_Add_Button=:>";
					$anchor->href = Request::$url;
					$anchor->hrefAJAX = Request::$url;
					$anchor->isTraditionalHref = false;
					$anchor->refreshElementId = "mainFields";
					$anchor->getParamsValues = true;
					$anchor->params = array('request_time' => '', 'materialsQuantity' => '', 'materials_users_divisibility_id' => '', 'materialsComment' => '', 'PROJECTS_ID' => '', 'SUPPLIERS_ID' => '', 'MATERIALS_ID' => '');
				
					$this->blocksMarkup .= "<br />".$anchor->OpenTag()."<:=Materials_Add_Button=:>".$anchor->CloseTag()."<br/>";
					
					$this->page->commonScripts .= $anchor->appendClientScript;
				//}
				//else
				//{
					$this->blocksAjaxMarkup .= $this->blocksMarkup;
				//}
				
			}
			
			elseif($this->page->isValidPost && Request::GetSCode() == $this->SCodes['ajax_search'])
			{
				$this->blocksAjaxMarkup = "<:=BLOCK_SEARCH=:>";
			}
			elseif($this->page->isValidPost && Request::GetSCode() == $this->SCodes['ajax_create'] && $this->isValidForm)
			{
				if($this->projects->AddMaterialsToProject())
				{
					$this->blocksAjaxMarkup = "Testing";
					$this->page->commonScripts .= "Objects.BubbleDiv.refresh(''); Objects.BubbleDiv.hide();";
				}
				else
				{
					$this->blocksAjaxMarkup = "<:=Materials_Select_OneFirst=:>";
				}
			}
			elseif($this->page->isValidPost && Request::GetSCode() == $this->SCodes['ajax_suppliers'])
			{
				$this->blocksAjaxMarkup = $this->RenderSuppliersList();
			}
		}
		
		private function RenderSuppliersList()
		{
			if($this->page->isValidPost)
			{				
				$result = NULL;
				
				$materialsId = (float)Security::DecodeUrlData($_POST['MATERIALS_ID']);
				
				$materials = new Materials();
				$result = $materials->LoadMaterialsSuppliers($materialsId);
								
				if($result != NULL)
				{
					while($row = mysql_fetch_array($result))
					{
						$id = Security::EncodeUrlData($row['COMPANIES_ID']);
						$this->blocksMarkup .= "<div onclick=\"SuppliersClick(this, '{$id}');\">{$row['NAME']}</div>";
					}
				}				
			} 
			
			return $this->blocksMarkup;
		}
		
		private function SelectMaterials()
		{		
			$blockSearch = new BlockSearch ();
			$blockSearch->page = $this->page;
			$blockSearch->BlockMode = RENDER_MATERIALS_SEARCH_INPUT;
			$blockSearch->inputId = "materialsInput";
			$blockSearch->inputText = "";
			$blockSearch->BlockInit();
						
			$this->blocks['BLOCK_SEARCH'] = $blockSearch;

			if(!$this->page->isValidPost || $this->page->isValidPost && !in_array(Request::GetSCode(), $this->SCodes) )
			{
				$this->formTag = new Form();
				$this->formTag->name = "Materials_Selection";
				
				$this->blocksMarkup .= "<:=BLOCK_SEARCH=:>";
								
				$this->materials = new Materials();
				$result = $this->materials->LoadMaterials($filter_name, $filter_limit, $filter_offset);
				
				if(mysql_num_rows($result) > 0)
				{
					$this->blocksMarkup .= $this->formTag->RenderTop();

					$hdn = new Hidden();
					$hdn->SetName("__SCode");
					$hdn->SetValue(sha1("SubMitSelEcTeDmaTerials"));
					
															
					$this->blocksMarkup .= $hdn->OpenTag();
					
					//Init of the Grid
					
					$grid = new Grid();
					
					$this->blocksMarkup .= $grid->RenderTop();
						
					// Init the Headers
					$fieldsArray = array();
					
					$field = new Field();
					$field->name = "row_checker"; // Row checkers
					$field->value = "&nbsp;";
					$field->width = "50px";
					
					array_push($fieldsArray, $field);
					
					$field = new Field();
					$field->name = "materials_id";
					$field->value = "<:=Materials_Id_Header=:>";
					$field->hidden = true;
					$field->width = "150px";
						
					array_push($fieldsArray, $field);
						
					$field = new Field();
					$field->name = "materials_name";
					$field->value = "<:=Materials_Name=:>";
					$field->width = "200px";
						
					array_push($fieldsArray, $field);
						
					$field = new Field();
					$field->name = "materials_unique_name";
					$field->value = "<:=Materials_Unique_Name=:>";
					$field->width = "100px";

					array_push($fieldsArray, $field);
						
					$field = new Field();
					$field->name = "materials_position";
					$field->value = "<:=Materials_Position=:>";					
						
					array_push($fieldsArray, $field);
						
					$field = new Field();
					$field->name = "materials_quantity";
					$field->value = "<:=Materials_Quantity=:>";
					$field->width = "20px";
						
					array_push($fieldsArray, $field);
					
					$field = new RowProperties();
					$field->isSelected = $checked;
					$field->name = "ROW_PROPERTIES";

					$fieldsArray['ROW_PROPERTIES'] = $field;
						
					$grid->fieldsArray = $fieldsArray;
					$checked = true;
					while($row = mysql_fetch_array($result))
					{
						$fieldsArray = array();
						
						$field = new Field();
						$field->name = "row_checker";
						
						$checked = !(bool)$checked;
						
						$checkBox = new Checkbox();
						$checkBox->SetName("materials_items[]"); // Allows to select materials
						$checkBox->SetValue(Security::EncodeUrlData($row['MATERIALS_ID']));
						$checkBox->SetChecked($checked);
						
						$field->value = $checkBox;
						$field->type = "object";
						
						array_push($fieldsArray, $field);

						$field = new Field();
						$field->name = "materials_id";
						$field->value = "m_".$row['MATERIALS_ID']; 
						$field->hidden = true;
						
						array_push($fieldsArray, $field);
							
						$field = new Field();
						$field->name = "materials_name";
						$field->value = $row['NAME']." ('".$row['VENDOR_TEXT']."')";
							
						array_push($fieldsArray, $field);
							
						$field = new Field();
						$field->name = "materials_unique_name";
						$field->value = $row['UNIQUE_NAME_IDENTIFIER'];
							
						array_push($fieldsArray, $field);
							
						$field = new Field();
						$field->name = "materials_position";
						$field->value = $row['ORD'];
							
						array_push($fieldsArray, $field);
							
						$field = new Field();
						$field->name = "materials_quantity";
						$field->value = $row['QUANTITY'];

						array_push($fieldsArray, $field);

						$field = new RowProperties();
						$field->isSelected = $checked;
						$field->name = "ROW_PROPERTIES";

						$fieldsArray['ROW_PROPERTIES'] = $field;

						$this->blocksMarkup .= $grid->RenderRow($fieldsArray);
						
					}
						
					$this->blocksMarkup .= $grid->RenderBottom();
				}
				
				$this->blocksMarkup .= "<br>".$this->formTag->RenderSubmitButton("<:=SUBMIT_SELECTION_BUTTON=:>");
				
				$this->blocksMarkup .= $this->formTag->RenderBottom();
			}
			elseif($this->page->isValidPost && Request::GetSCode() == sha1("SubMitSelEcTeDmaTerials"))
			{
				$materials_list = $_POST['materials_list'];

				$materialsIds = array();
				foreach($materials_list as $material)
				{
					array_push($materialsIds, (float)Security::DecodeUrlData($material));
				}
				
				if(count($materialsIds) > 0)
				{
					$this->materials->projects_id = (float)$this->selectedPrjId;
					$this->materials->ProjectsMaterialsQuantity();
				}
			}
			elseif($this->page->isValidPost && Request::GetSCode() == $this->SCodes['ajax_search'])
			{
				$this->blocksAjaxMarkup = "<:=BLOCK_SEARCH=:>";
			}
			
		}

		public function BlockInit()
		{
			parent::BlockInit();

			$this->SwitchMode();
			
		}
	}
?>