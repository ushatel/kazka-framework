<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


  include_once("CommonBlock.php");

  include_once("Block.Friends_Local.php");

  include_once("includes/common/Main.Enumerator.php");


  	/**
	 * class Friends
	 *
	 * Блок, що дозволяє переглядати друзів
	 *
	 * @package Blocks.pkg
	 */

	class BlockFriends extends CommonBlock
	{
		public $page = NULL;
		
		private $fieldsArray = array("materials_group" => false);
									  		
		public $blocksMarkup = "";
		
		private $isValidForm = false;
		
		private $formTag = NULL;
		
		public $BlockMode = RENDER_FRIENDS;    	 // RENDER_FRIENDS 		= renders the friends list
												 // RENDER_FRIENDS_GRID = renders the friends grid
												 // ADD_FRIENDS 		= renders the friends grid
		
		public $filter_name = "";    
		public $filter_offset = 0;   
		public $filter_limit = 0;    
		
		private $usersId = 0;
		
		private $friends = NULL;
		
		private $SCodes = NULL;
		
		public $isCustomLink = false;
				
		public function __construct()
		{
			$this->localizator = new BlockFriends_Local();
			$this->friends = new Users();
			
			$this->SCodes = self::GetAllowedSCodes();
			
			parent::__construct();
		}
		
		public static function GetAllowedSCodes()
		{
			return array (sha1("AddFrIenD"), sha1("SeLecTfRieNd"));
		}
		
		public function ValidateForm()
		{
			$this->isValidForm = false;
			
			if(!in_array(Request::GetSCode(), $this->SCodes))
			{
				$this->isValidForm = false;
				return $this->isValidForm;
			}
						
			return $this->isValidForm;
		}
		
		private function SwitchMode()
		{
			switch($this->BlockMode)
			{
				case RENDER_FRIENDS:  			// = renders the friends list
					$this->RenderFriends();
				break;    	 
				
				case RENDER_FRIENDS_GRID:  		// = renders the friends grid
					$this->RenderFriendsGrid();
				break;

				case ADD_FRIENDS:				// = add friends
					$this->AddFriend();
				break;
				
				default:
				break;
			}
		}
		
		public function SetUserId($usersId)
		{
			$this->usersId = $this->friends->friends_primary_id = $usersId;
		}
		
		public function SetCompaniesId($companiesId)
		{
			$this->supplierCompaniesId = $this->materials->user_supplier_companies_id = (float)$companiesId;
		}

		private function RenderFriendsGrid()
		{
			$result = $this->friends->LoadUsers(); 

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
					$field->name = "friends_id";
					$field->value = "";
					$field->hidden = true;
					
					array_push($fieldsArray, $field);
					
					$field = new Field();
					$field->name = "friends_login";
					$field->value = "<:=Friends_Login=:>";
					$field->width = "100px";
					
		
					$field = new Field();
					$field->name = "friends_ava";
					$field->value = "<:=Friends_Avatar=:>";
					$field->width = "100px";
		
					array_push($fieldsArray, $field);
					
					$field = new Field();
					$field->name = "friends_company";
					$field->value = "<:=Friends_Company=:>";
					$field->width = "150px";
					
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
						$field->name = "friends_id";
						$field->value = $row['USERS_ID'];
						$field->hidden = true;

						array_push($fieldsArray, $field);
						
						$field = new Field();
						$field->name = "friends_login";
						$field->value = $row['LOGIN'];
						
						array_push($fieldsArray, $field);
												
						$field = new Field();
						$field->name = "friends_ava";
						$field->value = "";//$row['UNIQUE_NAME_IDENTIFIER'];
												
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
		
		private function RenderMaterialsList()
		{
			$result = $this->materials->LoadMaterials();
			
			if(mysql_num_rows($result) > 0)
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
						$this->blocksMarkup .= "<a href='".$this->linkForListing.$row['UNIQUE_NAME_IDENTIFIER']."'>".$row['NAME']."</a><br />";
					}
					else
					{
						$anchor = new Anchor();
						$anchor->SCode = $this->sCodeForListing;
						$anchor->title = $row['NAME'];
						$anchor->isTraditionalHref = false;
						$anchor->params = array("MaterialsId" => Security::EncodeUrlData($row['MATERIALS_ID']));
						$anchor->href = $this->linkForListing;
						
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
		
		private function AddMaterials()
		{
			if($this->page->isValidPost)
			{
				$this->materials->user_materials_id = $this->materials->materials_id = (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['MaterialsId']));
				
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
			
			if(!$this->materials->isLoaded)
			{
				$sCode = sha1("SubMitNewMateRialtODb");
			}
			else
			{
				// &#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533; &#65533;&#65533;&#65533;&#65533;&#65533;&#65533;&#65533;
				$sCode = sha1("SavEeXisTingMateRial");
			}

			if(!$this->page->isValidPost /*|| $this->page->isValidPost && $this->steps->isLoaded*/ || $this->page->isValidPost && !$this->ValidateForm())
			{
				$this->formTag = new Form();
				$this->formTag->name = "Materials_Form";
				
				$this->blocksMarkup = $this->formTag->RenderTop();
				
				$materialsGroups = new MaterialsGroupsList();
				$materialsGroups->name = "materials_groups_id";
				
				$hdn = new Hidden();
				$hdn->SetName("__SCode");
				$hdn->SetValue($sCode);
				
				$this->blocksMarkup .= $hdn->OpenTag();
				
				$isReadOnly = false;
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
				
				$countriesList = new CountriesList();				
				$divisibilityList = new DivisibilityList();
				$divisibilityList->name = "materials_divisibility_id";
				
				$userDivisibility = new DivisibilityList();
				$userDivisibility->name = "materials_users_divisibility_id";
				
				if($isReadOnly)
				{
					$countriesList->tagAttributes['disabled'] = "disabled";
					$divisibilityList->tagAttributes['disabled'] = "disabled";
					$materialsGroups->tagAttributes['disabled'] = "disabled";
				}				

				$this->blocksMarkup .= "<table>";

				$this->blocksMarkup .= "<tr><td><:=Materials_Groups_Id=:></td><td>".$materialsGroups->GetMaterialsGroupsList($this->materials->materials_groups_id)." ".$this->formTag->RenderAsterisks($this->fieldsArray['materials_group'])."</td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_Name=:></td><td><input type='text' name='materials_name' ".($isReadOnly ? "readonly='readonly'" : "")." value='".$this->materials->name."'>".$this->formTag->RenderAsterisks($this->fieldsArray['materials_name'])."</td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_Unique_Name=:></td><td><input type='text' name='materials_unique_name' ".($isReadOnly ? "readonly='readonly'" : "")." value='".$this->materials->unique_name_identifier."'>".$this->formTag->RenderAsterisks($this->fieldsArray['materials_unique_name'])."</td></tr>";

				$this->blocksMarkup .= "<tr><td><:=Materials_Country_Name=:></td><td>".$countriesList->GetCountriesList($this->materials->original_countries_id)."".$this->formTag->RenderAsterisks($this->fieldsArray["country_name_field"])."</td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_Vendor_Company=:></td><td><input type='text' name='materials_vendor_name' ".($isReadOnly ? "readonly='readonly'" : "")." value='".$this->materials->vendor_text."'>".$this->formTag->RenderAsterisks($this->fieldsArray['materials_vendor_name'])."</td></tr>"; // add new vendor or select from the list
				$this->blocksMarkup .= "<tr><td><:=Materials_Divisibility_Name=:></td><td>".$divisibilityList->GetDivisibilitiesList($this->materials->divisibility_id)." ".$this->formTag->RenderAsterisks($this->fieldsArray['materials_divisibility_id'])."</td></tr>";				

				$this->blocksMarkup .= "<tr><td><:=Materials_Comment=:></td><td><textarea name='materials_comment' cols='30' rows='5' ".($isReadOnly ? "readonly='readonly'" : "").">".$this->materials->comment."</textarea></td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_IsPublic=:></td><td><input type='checkbox' name='materials_ispublic'  ".($isReadOnly ? "disabled='disabled'" : "")." value='".($this->materials->is_public ? "on" : "")."'></td></tr>";
				
				if($this->isSupplier)
				{
					$companiesList = new CompaniesList();
					$companiesList->name = "materials_supplier_id";
				
					$this->blocksMarkup .= "<tr><td colspan='2'><hr /></td></tr>";
					$this->blocksMarkup .= "<tr><td><:=Materials_Companies_Name=:></td><td>".$companiesList->GetCompaniesFullList((float)$this->materials->user_supplier_companies_id)."</td></tr>";
					$this->blocksMarkup .= "<tr><td><:=Materials_User_Comment=:></td><td><textarea name='materials_user_comment' cols='30' rows='5' ".($isReadOnly ? "readonly='readonly'" : "").">".$this->materials->user_comment."</textarea></td></tr>";				
					$this->blocksMarkup .= "<tr><td><:=Materials_User_Divisibility=:></td><td>".$userDivisibility->GetDivisibilitiesList($this->materials->user_divisibility_id).$this->formTag->RenderAsterisks($this->fieldsArray['materials_user_divisibility_id'])."</td></tr>";
					$this->blocksMarkup .= "<tr style='border-bottom:1px;'><td><:=Materials_User_Quantity=:></td><td><input type='text' name='materials_user_quantity' value='".$this->materials->user_quantity."'>".$this->formTag->RenderAsterisks($this->fieldsArray['materials_user_quantity'])."</td></tr>";
					$this->blocksMarkup .= "<tr><td colspan='2'><hr /></td></tr>";
				}
				
				//$this->blocksMarkup .= "<tr><td><:=Materials_IsApproved=:></td><td><input type='checkbox' name='materials_is_approved' value='".($this->materials->is_approved ? "on" : "")."'></td></tr>";

				$this->blocksMarkup .= "</table>";
				
				$this->blocksMarkup .= $this->formTag->RenderSubmitButton("<:=Materials_Submit_Name=:>");
				
				$this->blocksMarkup .= $this->formTag->RenderBottom();
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
					
				}
				else 
				{
					// Error. Wrong material Id
					
				}
			}
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
		
		private function SelectMaterials()
		{		
			if(!$this->page->isValidPost || $this->page->isValidPost && (Request::GetSCode() != sha1("SubMitSelEcTeDmaTerials")))
			{
				$this->formTag = new Form();
				$this->formTag->name = "Materials_Selection";
				
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
						//echo "sdff - ".$field->clientId."<br>";

						$fieldsArray['ROW_PROPERTIES'] = $field;

						$this->blocksMarkup .= $grid->RenderRow($fieldsArray);
						
						//array_push($grid->rowsArray, $fieldsArray);
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
		}

		public function BlockInit()
		{
			parent::BlockInit();

			$this->SwitchMode();
			
		}
	}
?>