<?

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


  include_once("CommonBlock.php");

  include_once("Block.Landmarks_Local.php");
  
  include_once("includes/DatabaseClasses/Parts.Projects.php");

  	/**
	 * class BlockLandmarks
	 *
	 * @package Blocks.pkg
	 */
	class BlockLandmarks extends CommonBlock
	{
		public $page = NULL;
		
		private $fieldsArray = array(
								"landmarkDate_field" => false,
								"landmarkText_field" => false
								);

		public $blocksMarkup = "";

		private $isValidForm = false;

		private $formTag = NULL;

		public $BlockMode = RENDER_LANDMARKS_LIST ; // RENDER_LANDMARKS_LIST = renders the grid with the latest news,
											   		// RENDER_LANDMARKS = renders the form of the news creation
		
		public $filter_name = "";    // 
		public $filter_offset = 0;   // offset
		public $filter_limit = 0;    // limit
				
		private $selectedProjectId = 0;
		private $selectedStepsId = 0;
		
		public $ShowNew = true;
		
		private $landmarksId = 0;

		private $usersId = 0;
		
		private $SCodes = NULL;
		
		public function __construct()
		{
			$this->localizator = new BlockLandmarks_Local();

			$this->SCodes = self::GetAllowedSCodes();

			$this->projects = new Projects();

			parent::__construct();
		}
		
		public static function GetAllowedSCodes()
		{
			return array ( sha1("SaVeLaNdMarKs") );
		}
		
		public function SetUserId($userId)
		{
			$this->usersId = $userId;
		}
		
		public function ValidateForm()
		{
			$this->isValidForm = false;
			
			if(!in_array(Request::GetSCode(), $this->SCodes))
			{
				$this->isValidForm = false;
				return $this->isValidForm;
			}

			$this->projects->landmarks_ldate = strtotime(StaticDatabase::CleanupTheField($_POST['landmarkDate']));
						
			if($this->projects->landmarks_ldate < 1) 
			{
				$this->fieldsArray["landmarkDate_field"] = "<:=Validation_DateTime_Error=:>";
				$this->isValidForm = false;
				
				$this->projects->landmarks_ldate = mktime(0, 0, 0, 8, 13, 2010);//StaticDatabase::CleanupTheField($_POST['projects_edate']);
			}
			else 
			{
				$this->isValidForm = true;
			}
			
			$this->projects->landmarks_text = substr(StaticDatabase::CleanupTheField($_POST['landmarkText']), 0, 200); //landmarkText_field 
			
			if(strlen($this->projects->landmarks_text) > 188)
			{
				$this->isValidForm = false;
				$this->fieldsArray['landmarkText_field'] = "<:=Validation_Landmarks_Length_Error=:>";
			}
			else
			{
				$this->isValidForm = $this->isValidForm & true;
			}

			return $this->isValidForm;
		}
		
		private function SwitchMode()
		{
			switch($this->BlockMode)
			{
				case RENDER_LANDMARKS_GRID:
					//$this->RenderNewsGrid();
				break;
				
				case RENDER_LANDMARKS_LIST:
					$this->RenderLandmarksList(); 
				break;
				
				case ADD_NEWS:
					$this->CreateNews();
				break;		
			}
		}
				
		public function SetProjectsId($projectsId)
		{
			$this->selectedProjectId = (float) $projectsId;
		}
		
		public function SetStepsId($stepsId)
		{
			$this->selectedStepsId = (float) $stepsId;
		}
		
		public function SetLandmarksId($landmarksId)
		{
			$this->landmarksId = (float) $landmarksId;
		}
		
		private function LandmarksForm()
		{
			return "<div style=\"display:inline;\"><input type=\"text\" name=\"landmarkDate\" id=\"landmarkDate\" value=\"".StaticDatabase::CleanupTheField($_POST['landmarkDate'])."\"></div>".$this->formTag->RenderAsterisks($this->fieldsArray["landmarkDate_field"], false)."<div style=\"display:inline;\">&nbsp;<input type=\"text\" name=\"landmarkText\" id=\"landmarkText\" value=\"".$this->projects->landmarks_text."\"></div>".$this->formTag->RenderAsterisks($this->fieldsArray["landmarkText_field"], false);
		}
		
		private function RenderLandmarksNew()
		{
			$this->formTag = new Form();	
			
			$link = new Anchor();
			$link->SCode = sha1("SaVeLaNdMarKs");
			$link->title = "<:=Add_Landmarks=:>";
			$link->href = Request::GetRoot()."/Projects/Projects/";
			$link->hrefAJAX = Request::$url; 
			$link->class = "ajaxLink";
			$link->isTraditionalHref = false;
			$link->getParamsValues = true;
			$link->params = array('landmarkDate' => "", 'landmarkText' => "", "PROJECTS_ID" => '');
			$link->refreshElementId = "addLandmarkInput";

			$link_text = $link->OpenTag()."<:=Add_Landmarks=:>".$link->CloseTag();
			
			$this->page->commonScripts .= /*"alert('fdfds');";*/ $link->appendClientScript;

			$res = "";

			if(!$this->isAjaxRequest || Request::GetSCode() != sha1("SaVeLaNdMarKs"))
			{
				$res .= "<div id='addLandmarkBlock'><nobr><span id='addLandmarkInput'>";
			
				$res .= $this->LandmarksForm();

				$res .= "</span><span style='display:inline;'>&nbsp;".$link_text."</span></nobr></div>";
			}
			else
			{
				$res = $this->LandmarksForm();
			}

			if($this->isAjaxRequest)
			{
				$this->blocksAjaxMarkup = $res;
			}
			else
			{
				$this->blocksMarkup = $res;
			}

			return $res;
		}
		
		private function RenderLandmarksRow($ldate, $landmarks_text)
		{
			$result = "<div id='d_".rand(11111, 99999)."'>".date('Y-m-d', strtotime($ldate))." - ".$landmarks_text."</div>";
			return $result;
		}
		
		private function RenderLandmarksList()
		{					

			if( !$this->isValidPost || $this->isAjaxRequest && $this->isValidPost && Request::GetSCode() != sha1("SaVeLaNdMarKs"))
			{
				$res .= "<div id='landmarksDiv'>";
			
				if($this->ShowNew)
				{
					$res .= $this->RenderLandmarksNew();
				}
				
				$this->projects->landmarks_projects_id = (float) Security::DecodeUrlData($_POST['PROJECTS_ID']);//(float)$this->selectedProjectId;

				$result = $this->projects->LoadLandmarks();				

				if($result != NULL && mysql_num_rows($result) > 0)
				{
					while($row = mysql_fetch_array($result))
					{
						$res .= $this->RenderLandmarksRow($row['LDATE'], $row['LANDMARKS_TEXT']);
					}
				}
		
				$res .= "</div>";

				if($this->isAjaxRequest)
				{
					$this->blocksAjaxMarkup = $res;
				}
				else
				{
					$this->blocksMarkup = $res;
				}
			}
			elseif($this->isAjaxRequest && $this->isValidPost && Request::GetSCode() == sha1("SaVeLaNdMarKs") && $this->ValidateForm())
			{
				$this->projects->landmarks_projects_id = (float)Security::DecodeUrlData($_POST['PROJECTS_ID']);
			
				if($this->projects->CreateLandmark())
				{
					$this->page->commonScripts = "<script> var elem = $('landmarksDiv'); elem.innerHTML = elem.innerHTML + \"".$this->RenderLandmarksRow(date('Y-m-d', $this->projects->landmarks_ldate), $this->projects->landmarks_text)."\"; </script>";
				}
			
				$this->projects->landmarks_text = "";
				$this->projects->landmarks_ldate = 0;
				$this->projects->landmarks_langs_id = 0;
				$this->projects->landmarks_is_public = false;
				$this->projects->landmarks_projects_id = 0;
				$this->projects->landmarks_steps_id = 0;
								
				$this->RenderLandmarksNew();			
			}
			elseif($this->isValidPost && Request::GetSCode() == sha1("SaVeLaNdMarKs") && !$this->isValidForm)
			{
				if($this->isAjaxRequest && $this->isValidPost)
				{
					$this->RenderLandmarksNew();
				}
/** 
 *	addNewLandmark
 */
			}
		}
		
		private function AddSupliersMaterials()
		{
			$this->isSupplier = true;
			
			$this->AddMaterials();
		}

		public function BlockInit()
		{
			parent::BlockInit();

			$this->SwitchMode();
		}
	}
?>