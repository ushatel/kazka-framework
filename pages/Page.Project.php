<?

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("CommonPage.php");
	include_once("Page.Project_Local.php");
	include_once("includes/common/Tag/Lib.Select.php");
	include_once("includes/common/Tag/Lib.Form.php");
	include_once("includes/common/Tag/Lib.Hidden.php");
	include_once("includes/common/Tag/Lib.CountriesList.php");
	
	include_once("includes/DatabaseClasses/Parts.Projects.php");
	include_once("includes/DatabaseClasses/Parts.Countries.php");
	
	include_once("Block.Steps.php");
	include_once("Block.Projects.php");
	include_once("Block.Landmarks.php");
	include_once("Block.Materials.php");
	include_once("Block.News.php");
	
	class Project extends CommonPage
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
		
		private $projects = NULL;
		private $formTag = NULL;
		
		private $selectedProjectId = 0;
		private $selectedStepsId = 0;
		private $selectedLandmarksId = 0;
		
		public function __construct()
		{
			$this->localizator = new Project_Local();
			
			$this->projects = new Projects();
			
			$this->projects->sdate = time();
			$this->projects->edate = time();
			
			if(($projectId = (float)Security::DecodeUrlData($_POST['PROJECTS_ID'])) > 0)
			{
				$this->selectedProjectId = (float)$projectId;
			}
			elseif(!is_numeric(Request::$identifier) && strlen(Request::$identifier) > 0)
			{
				$this->projects->unique_name_identifier = Request::$identifier;
				$this->projects->ValidateUniqueIdentifier(true);

				$this->selectedProjectId = $this->projects->projects_id;
			}
			else
			{
				$this->selectedProjectId = (float) Request::$identifier;
			}

			parent::__construct();
		}
		
		/**
		 */
		private function ValidateFirstForm()
		{
			$this->isValidForm = false;
			
			$countryArray = preg_split("/_/i", StaticDatabase::CleanupTheField($_POST['projects_country_field']), 2);			

			if(!is_numeric($countryArray[0]) || strlen($countryArray[1]) != 2)
			{
				$this->fieldsArray["project_country_field"] = "<:=Validation_Country_Error=:>";
				$this->isValidForm = false;
			}
			else 
			{
				$country = new Countries();
				$country->countries_id = (float)$countryArray[0];
				$country->GetCountryById();

				$this->projects->country_name = $country->name;
				$this->projects->countries_id = $country->countries_id;
					
				$this->isValidForm = true;
			}

			$this->projects->name = StaticDatabase::CleanupTheField($_POST['projects_name']);
			$this->projects->projects_id = (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['ProjectsId']));

			if(strlen($this->projects->name) < 1) 
			{
				$this->fieldsArray['projects_name'] = "<:=Validation_Name_Error=:>";
				$this->isValidForm = false;
			}
			else 
			{
				$this->isValidForm = $this->isValidForm & true;
			}

			$this->projects->sdate = strtotime(StaticDatabase::CleanupTheField($_POST['projects_sdate']));
			
			if($this->projects->sdate < 1)
			{
				$this->fieldsArray["projects_sdate"] = "<:=Validation_DateTime_Error=:>";
				$this->isValidForm = false;

				$this->projects->sdate = mktime(0, 0, 0, 8, 13, 2010);//StaticDatabase::CleanupTheField($_POST['projects_sdate']);
			}
			else
			{
				$this->isValidForm = $this->isValidForm & true;
			}
			
			$this->projects->edate = strtotime(StaticDatabase::CleanupTheField($_POST['projects_edate']));
			
			if($this->projects->edate < 1) 
			{
				$this->fieldsArray["projects_edate"] = "<:=Validation_DateTime_Error=:>";
				$this->isValidForm = false;
				
				$this->projects->edate = mktime(0, 0, 0, 8, 13, 2010);//StaticDatabase::CleanupTheField($_POST['projects_edate']);
			}
			else 
			{
				$this->isValidForm = $this->isValidForm & true;
			}
			
			if($this->projects->edate - $this->projects->sdate <= 0) 
			{
				$this->fieldsArray["projects_sdate"] = "<:=Validation_DateTime_Diff_Error=:>";
				$this->isValidForm = false;
			}
			else 
			{
				$this->isValidForm = $this->isValidForm & true;
			}

			$this->projects->city_name = StaticDatabase::CleanupTheField($_POST['projects_city_name_field']);
			$this->projects->city_id = (float)StaticDatabase::CleanupTheField($_POST['projects_city_id']);
			
			$this->projects->comment = StaticDatabase::CleanupTheField($_POST['projects_comment']);
			
			if(Request::GetSCode() != sha1("eDitOnePrOjeCt") && Request::GetSCode() != sha1("CreAtePrjFirStSteP"))
			{
				$this->isValidForm = false;
			}
			
			return $this->isValidForm;
		}
				
		/**
		 * 
		 */
		private function SwitchStep()
		{
	
			switch(Request::GetSCode())
			{
				case sha1("eDitOnePrOjeCt"):

					$this->PreInitEditProject();
					$this->RenderFirstStep(sha1("eDitOnePrOjeCt"));
					
					break;
			
				default:
				case sha1("CreAtePrjFirStSteP"):
					
					$this->RenderFirstStep(sha1("CreAtePrjFirStSteP"));

					break;

				case sha1("SubMitStePtODb"):
				case sha1("CreAtePrjSeCondSteP"):
				case sha1("eDitProJectsSteP"):
				case sha1("SubMitNewMateRialtODb"):
				case sha1("SubMitSelEcTeDmaTerials"):

					$this->RenderSecondStep();

					break;	
			}
		}
		
		private function PreInitEditProject()
		{
			if($this->isValidPost)
			{
				if(!Request::IsPostType())
				{
					$this->projects->projects_id = (float)/*Security::DecodeUrlData(*/StaticDatabase::CleanupTheField($_GET['data'])/*)*/;
				}
				else
				{
					$this->projects->projects_id = (float)/*Security::DecodeUrlData(*/StaticDatabase::CleanupTheField($_POST['ProjectsId'])/*)*/;
				}
				$this->projects->GetProjectById();

				$country = new Countries();
				$country->countries_id = $this->projects->countries_id;
				$country->GetCountryById();
				
				$this->projects->country_name = $country->name;
			}
			
		}
				
		private function RenderStepsForProject()
		{
			$stepList = new BlockProjects();
			$stepList->page = $this;
			$stepList->selectedPrjId = (float)$this->selectedProjectId;
			$stepList->drawNewStepLink = true;
			$stepList->BlockMode = RENDER_STEPS_DIAGRAM;
			$stepList->BlockInit();
			
			$this->blocks['STEPS_LIST'] = $stepList;

			$this->pagesMarkup .= '<div id="stepsList"><:=STEPS_LIST=:></div>';
			
			if($this->isAjaxRequest)
			{
				$this->pagesAjaxMarkup .= $this->pagesMarkup;
			}
		}
		
		/** 
		 */
		private function RenderProjectsList()
		{
			//Render Steps for the company
			$projectList = new BlockProjects();
			$projectList->page = $this;
			$projectList->BlockMode = RENDER_PROJECTS_LEFT_LIST;
			$projectList->sCodeForListing = sha1("eDitOnePrOjeCt");
			$projectList->refreshDiv = "pContainer";
			$projectList->drawNewStepLink = true;
			$projectList->showInBubble = true;
			$projectList->needDrawId = true;

			$projectList->blockId = 'BLOCK_LIST_OF_PROJECTS'; 
			if(!$this->isAjaxRequest)
			{
				$this->commonHead .= "<script>".BlockSteps::InitClientScripts()."</script>";
				

				$this->pagesMarkup .= "<:=BLOCK_LIST_OF_PROJECTS=:>";
			}

			$projectList->BlockInit();

			$this->blocks['BLOCK_LIST_OF_PROJECTS'] = $projectList;
		}
		
		private function RenderNews()
		{
			if($this->isAjaxRequest)
			{
				$news = new BlockNews();
				$news->page = $this;
				$news->BlockMode = AJAX_NEWS;
				$news->blockId = "BLOCK_NEWS";
				$news->entitiesId = 4;
				$news->SetProjectsId((float)$this->selectedProjectId);
				
				$news->BlockInit();
			
				$this->blocks['BLOCK_NEWS'] = $news;
			
				$this->pagesAjaxMarkup .= "<:=BLOCK_NEWS=:>";				
			}
			else
			{
				$this->commonHead .= BlockNews::InitClientScripts();
			}
		}
		
		/**
		 * Draws the button for the Steps creation & the form to add new step to project
		 */
		public function RenderAjaxNewStep()
		{
			$this->projects->projects_id = (float)$this->selectedProjectId;
			$this->projects->GetProjectById();
		
			$step_block = new BlockSteps(); // !!!! The loaded project could be added to the block directly to save time
			$step_block->blockId = 'ADD_STEP_BLOCK';
			$step_block->page = $this;
			$step_block->selectedPrjId = (float)$this->selectedProjectId;
			$step_block->BlockInit();
	
			$this->blocks['ADD_STEP_BLOCK'] = $step_block;
					
			$this->pagesMarkup .= "<div><a class='common_button' onclick='Slide(270, \"slideStep\"); return false;'><:=Projects_Add_Step_Button=:></a><div id='slideStep' style='display:none;overflow:hidden;'><:=ADD_STEP_BLOCK=:></div></div>";
		}
		
		public function RenderMaterials()
		{
			$result = NULL;
			
			$materials = new BlockMaterials();
			
			$materials->page = $this;
			$materials->BlockMode = SELECT_MATERIALS_AJAX;
			$materials->SetProjectsId($this->selectedProjectId);
			$materials->showInBubble = true;
			$materials->BlockInit();
			
			$this->blocks['SELECT_BLOCK_MATERIALS'] = $materials;
			
			$this->pagesMarkup .= "<:=SELECT_BLOCK_MATERIALS=:>";
			
			if($this->isAjaxRequest)
			{
				$this->isAjaxJSON = true;

				$this->pagesAjaxMarkup .= $this->pagesMarkup;
			}
		}

		public function RenderSingleStep()
		{
			$result = NULL;
			
			if($this->isAjaxRequest)
			{
				$steps = new BlockSteps();
	
				$steps->page = $this;
				$steps->StepMode = SINGLE_STEP;
				$steps->blockId = "BLOCK_STEPS";

				$this->blocks['BLOCK_STEPS'] = $steps;

				$this->isAjaxJSON = true;
				$this->pagesAjaxMarkup .= "<:=BLOCK_STEPS=:>";

				$this->blocks['BLOCK_STEPS']->BlockInit();
			}


		}
		
		public function RenderLandmarks()
		{
			$result = NULL;
			
			$landmarks = new BlockLandmarks();
			
			$landmarks->page = $this;
			$landmarks->SetProjectsId($this->selectedProjectId);
			$landmarks->SetStepsId($this->selectedStepsId );
			$landmarks->SetLandmarksId($this->selectedLandmarksId );
			$landmarks->ShowNew = true;
			$landmarks->BlockMode = RENDER_LANDMARKS_LIST;
						
			$landmarks->blockId = "BLOCK_LANDMARKS";

			if($this->isAjaxRequest)
			{
				$this->pagesAjaxMarkup .= "<:=BLOCK_LANDMARKS=:>";
			}
			else
			{
				$this->pagesMarkup .= "<:=BLOCK_LANDMARKS=:>";
			}

			$landmarks->BlockInit();
			
			$this->blocks['BLOCK_LANDMARKS'] = $landmarks;
			
			return $result;
		}
		
		public function RenderAlbum()
		{
			$album = NULL;

			$album = new BlockAlbum(); // !!!! The loaded project could be added to the block directly to save time
			$album->BlockMode = RENDER_COMMON_ALBUM;

			$album->page = $this;
			$album->selectedEntityId = 4;
			$album->selectedObjectsId = (float)$this->selectedProjectId;
			$album->selectedPrjId = (float)$this->selectedProjectId;
			$album->selectedStepsId = (float)$this->selectedStepsId;
			$album->albumsTitleWidth = 170;
			$album->albumsWidth = "500px";
			$album->albumsHeight = "150px";
			$album->showAlbums = true;
			
			if(!$this->isAjaxRequest)
			{
				$album->display = "none";
			}

			if($this->isAjaxRequest)
			{			
				$this->isAjaxJSON = true;
				$this->pagesAjaxMarkup .= "<:=BLOCK_ALBUM=:>";
			}
			else 
			{
				$this->pagesMarkup .= "<div style='display:inline-block; margin-left:10px;'><:=BLOCK_ALBUM=:></div>";
			}

			$album->BlockInit();

			$this->blocks['BLOCK_ALBUM'] = $album;
		}
		
		public function PageInit()
		{
			//parent::PageInit();
			
			if(!$this->isAjaxRequest && Session::GetUserId() > 0 && Request::$identifier == "Yours" )
			{ // Single user projects
				$this->pagesMarkup .= "<div id='p_body'><div id='p_left_list'>";
	
				$this->RenderProjectsList();
	
				$this->pagesMarkup .= "</div>";
				
				$this->pagesMarkup .= "<div id='pContainer'>";
								
				$this->pagesMarkup .= '</div>';

				$this->RenderAlbum();

				$this->pagesMarkup .= "</div>";				
			}
			elseif(!$this->isAjaxRequest) // All projects list or the Latest projects
			{
				$projectList = new BlockProjects();
				$projectList->page = $this;
				$projectList->BlockMode = RENDER_PROJECTS_SEARCH_RESULT;
				$projectList->blockId = 'BLOCK_LIST_OF_PROJECTS'; 

				$projectList->BlockInit();

				$this->blocks['BLOCK_LIST_OF_PROJECTS'] = $projectList;

				$this->pagesMarkup .= "<div style='padding: 5px; padding-top: 10px; padding-bottom: 10px;'><:=BLOCK_LIST_OF_PROJECTS=:></div>";
			}
			else
			{
				if(Request::GetSCode() == sha1("sAveFilEtoDatAbaSe") || Request::GetSCode() == sha1("aJaXaDdAlBum") || Request::GetSCode() == sha1("aJaXalBuMrEfResH") || Request::GetSCode() == sha1("aJaXCliCkAlbUm") )
				{
					$this->RenderAlbum();
				}
				elseif(Request::GetSCode() == sha1("SaVeLaNdMarKs"))
				{
					$this->RenderLandmarks();
				}
				elseif(Request::GetSCode() == sha1("mAkeThEpReSeArcHreQuest") || Request::GetSCode() == sha1("RenDerMateRiAlsBubBle") || Request::GetSCode() == sha1("SelEcTSupPliErS") || Request::GetSCode() == sha1("AddMaTeRiAlToTheProJect"))
				{
					$this->RenderMaterials();
				}
				elseif(Request::GetSCode() == sha1("AddBuBbLeProJect") || Request::GetSCode() == sha1("CreAtePrjFirStSteP"))
				{
					$this->RenderProjectsList();
				}
				elseif(Request::GetSCode() == sha1('DrawTheNewStepForm') || Request::GetSCode() == sha1("SubMitStePtODb") || Request::GetSCode() == sha1("EditStepRow") || Request::GetSCode() == sha1("FinIsHsTep")) //
				{
					$this->RenderSingleStep(); 
				}
				elseif(Request::GetSCode() == sha1('DrawTheAddNewSfOrm') || Request::GetSCode() == sha1("DelEteNeWs") || Request::GetSCode() == sha1("EdiTnEws"))
				{
					$this->RenderNews();
				}
				else
				{
					$this->RenderProjectsList();
					//$this->RenderAlbum();
					$this->RenderMaterials();
					$this->RenderLandmarks();
					$this->RenderNews();
					
					$this->commonScripts .= " AlbumRefresh('".Security::EncodeUrlData((float)Security::DecodeUrlData($_POST['PROJECTS_ID']))."'); Objects.UploadParams['OBJECTS_ID'] = '".Security::EncodeUrlData((float)Security::DecodeUrlData($_POST['PROJECTS_ID']))."'; ";
				}
			}
		}
	}
?>