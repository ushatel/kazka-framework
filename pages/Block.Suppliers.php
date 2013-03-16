<?

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


  include_once("CommonBlock.php");
  
  include_once("Block.Suppliers_Local.php");
  
  include_once("includes/common/Tag/Lib.MaterialsGroupsList.php");
    
  include_once("includes/DatabaseClasses/Parts.Materials.php");
  include_once("includes/DatabaseClasses/Parts.Projects.php");

  	/**
	 * class Suppliers
	 *
	 * Цей класс роботи із блоком підрядника.
	 *
	 * @package Blocks.pkg
	 */

	class BlockSuppliers extends CommonBlock
	{
		public $page = NULL;
		
		private $fieldsArray = array("materials_group" => false, "materials_name" => false, "is_public" => false);
									  		
		public $blocksMarkup = "";
		
		public $stepTitle = "<:=Step_Title=:>";
		
		private $isValidForm = false;
		
		private $formTag = NULL;
		
		public $BlockMode = RENDER_SUPPLIERS_LIST ;  // SELECT_MATERIALS = renders the form to select materials,
											   		// ADD_MATERIALS = renders the form to create materials,
											   		// RENDER_MATERIALS_LIST = renders the materials list
		
		public $filter_name = "";    // Назва матеріалу для фільтрації
		public $filter_offset = 0;   // Значення offset
		public $filter_limit = 0;	 // Значення limit
		
		private $steps = NULL;
		private $materials = NULL;
		private $companies = NULL;
		
		private $selectedPrjId = 0;
		
		public function __construct()
		{
			$this->localizator = new Materials_Local();
			$this->materials = new Materials();
			$this->companies = new Companies();
			
			parent::__construct();
		}
		
		public function ValidateForm()
		{
			$this->isValidForm = false;
			
			if(Request::GetSCode() != sha1("SubMitNewMateRialtODb"))
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
			
			if($this->materials->materials_groups_id < 1)
			{
				$this->isValidForm = false;
				$this->fieldsArray['materials_group'] = "<:=Validation_Materials_Empty_Group_Error=:>";
			}
			else
			{
				$this->isValidForm = $this->isValidForm & true; 
			}
			
			$this->materials->is_public = (bool)(StaticDatabase::CleanupTheField($_POST['materials_is_public']) == 'on' ? true : false);
			
			$this->materials->comment = StaticDatabase::CleanupTheField($_POST['materials_comment']);

			return $this->isValidForm;
		}
		
		private function SwitchMode()
		{
			switch($this->BlockMode)
			{
				case RENDER_SUPPLIERS_LIST:
					$this->RenderSuppliersList();
				break;
			}
		}
		
		private function RenderSuppliersList()
		{
			$result = $this->companies->LoadCompanies();
			while($row = mysql_fetch_array($result))
			{
				$this->blocksMarkup .= $row['NAME'];
			}
		}
		
		private function AddMaterials()
		{
			$this->materials = new Materials();
			
			$this->materials->materials_id = (float)StaticDatabase::CleanupTheField($_POST['MaterialsId']);
			$this->materials->GetMaterialsById();
			
			if(!$this->page->isValidPost /*|| $this->page->isValidPost && $this->steps->isLoaded*/ || $this->page->isValidPost && !$this->ValidateForm())
			{
				$this->formTag = new Form();
				$this->formTag->name = "Materials_Form";
				
				$this->blocksMarkup = $this->formTag->RenderTop();
				
				$materialsGroups = new MaterialsGroupsList();
				$materialsGroups->name = "materials_groups_id";
				
				$hdn = new Hidden();
				$hdn->SetName("__SCode");
				$hdn->SetValue(sha1("SubMitNewMateRialtODb"));
				
				$this->blocksMarkup .= $hdn->OpenTag();
				
				if($this->materials->materials_id > 0)
				{
					$hdn = new Hidden();
					$hdn->SetName("MaterialsId");
					$hdn->SetValue($this->materials->materials_id);
					
					$this->blocksMarkup .= $hdn->OpenTag();
				}
				
				if($this->steps->steps_id > 0)
				{
					$hdn = new Hidden();
					$hdn->SetName("StepsId");
					$hdn->SetValue($this->steps->steps_id);
					
					$this->blocksMarkup .= $hdn->OpenTag();
				}
				
				$this->blocksMarkup .= "<table>";
					
				$this->blocksMarkup .= "<tr><td><:=Materials_Groups_Id=:></td><td>".$materialsGroups->GetMaterialsGroupsList($this->materials->materials_groups_id)." ".$this->formTag->RenderAsterisks($this->fieldsArray['materials_group'])."</td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_Name=:></td><td><input type='text' name='materials_name' value='".$this->materials->name."'>".$this->formTag->RenderAsterisks($this->fieldsArray['materials_name'])."</td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_Comment=:></td><td><textarea name='materials_comment' cols='30' rows='5'>".$this->materials->comment."</textarea></td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Materials_IsPublic=:></td><td><input type='checkbox' name='materials_name' value='on'></td></tr>";

				$this->blocksMarkup .= "</table>";
				
				$this->blocksMarkup .= $this->formTag->RenderSubmitButton("<:=Materials_Submit_Name=:>");
				
				$this->blocksMarkup .= $this->formTag->RenderBottom();
			}
			elseif($this->page->isValidPost && $this->isValidForm && (Request::GetSCode() == sha1("SubMitNewMateRialtODb")) )
			{
				$this->materials->CreateMaterial();
			}
		}
		
		private function SelectMaterials()
		{
			$this->formTag = new Form();
			$this->formTag->name = "Materials_Selection";
			
			$mSelect = new Select();
			$mSelect->tagAttributes['name'] = 'materials_list';
			$mSelect->isCombobox = false;
			
			$this->blocksMarkup .= $this->formTag->OpenTag();
			
			$this->materials = new Materials();
			$result = $this->materials->LoadMaterials($filter_name, $filter_limit, $filter_offset);
			
			if(mysql_num_rows($result) > 0)
			{
				$this->blocksMarkup .= $this->formTag->OpenTag();
				
				$hdn = new Hidden();
				$hdn->SetName("__SCode");
				$hdn->SetValue(sha1("SubMitSelEctEdMateRiAls"));
				
				while($row = mysql_fetch_array($result))
				{
					$mId = Security::EncodeUrlData($row["MATERIALS_ID"]);
					$opt = array("Title" => $row["NAME"], "Value" => $mId, "Id" => $mId, "Selected" => false);
	 				array_push($mSelect->optionsArray, $opt);
				}
				
				$this->blocksMarkup .= $mSelect->RenderTop().$mSelect->RenderBottom();

				$this->blocksMarkup .= "<br>".$this->formTag->RenderSubmitButton("<:=SUBMIT_SELECTION_BUTTON=:>");
				
				$this->blocksMarkup .= $this->formTag->RenderBottom();
			}
		}

		public function BlockInit()
		{
			parent::BlockInit();
			
			$this->SwitchMode();
		}
	}

?>