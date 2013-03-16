<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


  include_once("CommonBlock.php");
  
  include_once("Block.Projects_Local.php");
  include_once("Block.Calendar.php");
  include_once("includes/common/Lib.php");
  include_once("includes/common/Tag/Lib.Grid.php");
  
  include_once("includes/DatabaseClasses/Parts.Steps.php");
  include_once("includes/DatabaseClasses/Parts.Projects.php");
  include_once("includes/DatabaseClasses/Parts.Companies.php");
  
  
  	/**
	 * class BlockProjects
	 *
	 * Блок відрисовки проектів
	 *
	 * @package Blocks.pkg
	 */
	 	 
	class BlockProjects extends CommonBlock
	{
		public $page = NULL;

		private $fieldsArray = array("project_step_name" => false, 	  "projects_identifier" => false,   "sdate" =>   false,    "edate" => false, 
									 "step_type_text" =>    false,    "comment" => false, "projects_www" => false);

		public $blocksMarkup = "";

		public $stepTitle = "<:=Step_Title=:>";

		private $isValidForm = false;
		public $isReadOnly = true;

		private $formTag = NULL;

		public $BlockMode = SINGLE_PROJECT; // SINGLE_PROJECT 				 - renders the projects form
										    // RENDER_PROJECTS_LIST   		 - renders the list of the projects
										    // RENDER_PROJECTS_GRID   		 - renders the grid with the projects
										    // RENDER_PROJECTS_GRID_IN_WORK  - renders the grid with the inwork projects
										    // RENDER_PROJECTS_HISTORY 		 - renders the projects history per user or per company
										    // RENDER_STEPS_DIAGRAM			 - renders the steps Grant's diagram

		private $steps = NULL;
		private $projects = NULL;

		public $justNameColumn = false;

		public $selectedPrjId = 0;

		public $SCodes = NULL;

		public $isCustomLink = false;
		public $linkForListing = "";
		public $refreshDiv = "";
		
		public $projectOffset = 0;
		public $projectWindow = 10;

		public $drawNewStepLink = false;

		public $showInBubble = false;

		public function __construct()
		{
			$this->localizator = new Projects_Local();
			
			$this->projects = new Projects();
			
			$this->projects->sdate = time();
			$this->projects->edate = time();

			$this->SCodes = self::GetAllowedSCodes();
			
			if($this->linkForListing == "")
			{
				$this->linkForListing = Request::GetRoot()."/project/project/";
			}

			parent::__construct();
		}

		public static function GetAllowedSCodes()
		{
			return array ('ajax_create' => sha1("AddMaTeRiAlToTheProJect"), 'ajax_bubble' => sha1("AddBuBbLeProJect"), 'create_project' => sha1("CreAtePrjFirStSteP"));
		}

		public function SetCompaniesId($companiesId)
		{
			$this->projects->companies_id = (float)$companiesId;	
		}

		public function SetUserId($userId)
		{
			$this->projects->owner_id = (float) $userId;
		}

		/**
		 * Перевірити першу форму 
		 */
		private function ValidateFirstForm()
		{
			$this->isValidForm = false;
			
			if(Request::GetSCode() != sha1("eDitOnePrOjeCt") && Request::GetSCode() != sha1("CreAtePrjFirStSteP"))
			{
				$this->isValidForm = false;
				return $this->isValidForm;
			}
			
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
			$this->projects->projects_id = (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['PROJECTS_ID']));

			if(strlen($this->projects->name) < 1) 
			{
				$this->fieldsArray['projects_name'] = "<:=Validation_Name_Error=:>";
				$this->isValidForm = false;
			}
			else 
			{
				$this->isValidForm = $this->isValidForm & true;
			}
			
			$this->projects->www = StaticDatabase::CleanupTheField($_POST['projects_www']);
			
			if(strlen($this->projects->www) > 0 )
			{
				if(!preg_match("/http/i", $this->projects->www))
				{
					$this->projects->projects_www = "http://". $this->projects->www;
				}
		
				$hdl = NULL;
				try{
					$hdl = @fopen($this->projects->www, "r");
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
					$this->fieldsArray['projects_www'] = '<:=Validation_WWW_Error=:>';
					$this->isValidForm = false;		
				}
			}
			else
			{ 
				if($hdl != NULL) fclose($hdl);
				
				$this->isValidForm = $this->isValidForm & true;
			}
			
			$new_identifier = StaticDatabase::CleanupTheField($_POST['projects_identifier']);
			
			if(strlen($new_identifier) < 3 || 
				($this->projects->unique_name_identifier != $new_identifier) && $this->projects->ValidateUniqueIdentifier($new_identifier))
			{
				if(strlen($this->projects->unique_name_identifier) < 3)
				{
					$this->projects->unique_name_identifier = Operations::Translator($this->projects->name);
					$this->fieldsArray['projects_identifier'] = "<:=Validation_Check_Project_Unique_Name=:>";
				}
				else
				{
					$this->fieldsArray['projects_identifier'] = "<:=Validation_Projects_Name_IsNot_Unique=:>";
				}
				$this->isValidForm = false;
			}
			else
			{
				$this->projects->unique_name_identifier = $new_identifier;
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
						
			return $this->isValidForm;
		}

		private function SwitchMode()
		{
			switch($this->BlockMode)
			{
				case SINGLE_PROJECT:
					$this->RenderProjectsForm();
				break;
				
				case RENDER_PROJECTS_LIST:
					$this->RenderProjectsList();
				break;
				
				case RENDER_MAIN_PROJECTS:
					$this->RenderMainProjects();
				break;
				
				case RENDER_PROJECTS_LEFT_LIST:
					$this->RenderProjectsLeftList();
				break;
				
				case RENDER_PROJECTS_GRID:
					$this->RenderGrid();
				break;
				
				case RENDER_PROJECTS_PROFILE:
					$this->RenderProjectsProfile();
				break;
				
				case RENDER_STEPS_DIAGRAM:
					$this->RenderStepsDiagram();
				break;
				
				case RENDER_PROJECTS_GRID_IN_WORK:
					$this->RenderInWorkGrid();
				break;
					
				case RENDER_PROJECTS_GRID_FINISHED:
					$this->RenderFinishedGrid();
				break;
				
				case RENDER_PROJECTS_SEARCH_RESULT:
					$this->RenderProjectsSearchResults();
				break;
			}
		}

		private function RenderInWorkGrid()
		{
			$grid = new Grid();

			$this->projects = new Projects();
			$this->projects->is_finished = false;

			$result = $this->projects->LoadProjects('', 0, 10, "`END_TIME` ASC");

			$result = $this->RenderGrid($result);
		}

		private function SearchResultRow($row, $index = 0)
		{
			$this->blocksMarkup .= "<div class='resultsRow'><div style='display:inline;'><div class='number'>{$index}.</div><a href='".Request::GetRoot()."/Project/".(strlen($row['UNIQUE_NAME_IDENTIFIER']) > 0 ? $row['UNIQUE_NAME_IDENTIFIER'] : Security::EncodeUrlData($row['PROJECTS_ID']) )."/' >".$row['NAME']."</a>&nbsp;</div>";
			
			if(Session::GetUserId() > 0 && (Session::GetUserId() == (float)$row['UPDATED_LOGIN'] || Session::IsSuperAdmin()))
			{
				$this->blocksMarkup .= "<ul><li onClick='cDel(\"".Security::EncodeUrlData($row['PROJECTS_ID'])."\");' title='<:=Project_Row_Delete=:>'>×</li><li class='public' onClick=\"cEdit('".Security::EncodeUrlData($row['PROJECTS_ID'])."', 3); if(Objects.Environment['result'] == 'off') { this.childNodes[1].style.display = 'none'; } else { this.childNodes[1].style.display = 'block';}\">O<p ".( ((int)$row['IS_PUBLIC'] > 0) ? "" : "style='display: none;'").">●</p></li><li onClick='cEdit(\"".Security::EncodeUrlData($row['PROJECTS_ID'])."\", 1);' title='<:=Project_Edit=:>' class='edit'>…</li></ul>";
			}
			
			$this->blocksMarkup .= "</div>";
		}

		private function RenderSearchResults($result, $all = false)
		{
			$this->blocksMarkup .= "<div id='searchResults'>";

			if($result != NULL)
			{
				$prjCount = 0;
				
				$local = $this->GetLocalValue('Project_Edit');

				$this->page->commonScripts .= 'function cDel(id) { if(confirm("'.$this->GetLocalValue("Project_Are_You_Sure_Delete").'") )  ProjectClick(id, 2); Objects.BubbleDiv.hide(); }; function cEdit(id, action) { return ProjectClick(id, action); } ';

				$i = 1;
				if(is_array($result))
				{
					$prjCount = count($result);
					foreach($result as $row)
					{
						$this->SearchResultRow($row, $this->projectOffset + $i++);						
					}
				}
				elseif($prjCount = mysql_num_rows($result) > 0)
				{
					while($row = mysql_fetch_array($result))
					{	
						$this->SearchResultRow($row, $this->projectOffset + $i++);
					}
				}
				else
				{
					$this->blocksMarkup .= "<p><:=Project_Nothing_To_Show=:></p>";
				}

				if($all)
				{
					$this->blocksMarkup .= "<div class='searchIndexer'>";
					
					for($i = 0; $i < ($prjCount/$this->projectWindow); $i++)
					{
						$this->blocksMarkup .= "<a href='".Request::GetRoot()."/Project/?offset=".$i."' ".(($this->projectOffset/$this->projectWindow == $i) ? "class='selected'" : "");

						$this->blocksMarkup .= ">".($i + 1)."</a>";
					}
					
					$this->blocksMarkup .= "</div>";
				}
			}
			else
			{
				$this->blocksMarkup .= "<p><:=Project_Nothing_To_Show=:></p>";
			}

			$this->blocksMarkup .= "</div>";
		}
		
		private function RenderProjectsSearchResults()
		{
			$this->projects->unique_name_identifier = Request::$identifier;
			$this->projects->projects_id = (float)Request::$identifier;
			$this->InitClientScripts();

			$identifier = ucfirst(strtolower($this->projects->unique_name_identifier));
			if(strlen($this->projects->unique_name_identifier) < 1)
			{ 
				$this->page->SetTitle($this->GetLocalValue('Projects_All_Title'));

				$this->projectOffset = ((float)$_GET['offset']) * $this->projectWindow;
				
				if(Session::GetUserId() > 0)
				{
					$this->projects->updated_login = Session::GetUserId();
				}

				if(Session::IsSuperAdmin())
				{
					$this->projects->is_public = NULL;
					$this->projects->owner_id = NULL;
				}
				else
				{
					$this->projects->is_public = true;
				}

				$result = $this->projects->LoadProjects('', $this->projectOffset, $this->projectWindow, '`NAME`');

				$this->RenderSearchResults($result, true); 
			}
			elseif( $identifier == 'Latest' || $identifier == 'All')
			{
				if($identifier == "Latest")
					$this->page->SetTitle($this->GetLocalValue('Project_Latest_Title'));
				elseif($identifier == "All")
					$this->page->SetTitle($this->GetLocalValue('Projects_All_Title'));

				if(Session::GetUserId() > 0 && !Session::IsSuperAdmin())
				{
					$this->projects->updated_login = Session::GetUserId();
				}

				if(Session::IsSuperAdmin())
				{
					$this->projects->is_public = NULL;
					$this->projects->owner_id = NULL;
				}
				else
				{
					$this->projects->is_public = true;
				}

				if($identifier == 'Latest')
					$result = $this->projects->LoadProjects('', $this->projectOffset, $this->projectWindow, '`UPDATED_TIME`');
				else
					$result = $this->projects->LoadProjects('', $this->projectOffset, $this->projectWindow, '`NAME`');

				$this->RenderSearchResults($result);
			}
			elseif (($this->projects->projects_id > 0 && $this->projects->GetProjectById()) || strlen($this->projects->unique_name_identifier) > 0 && $this->projects->ValidateUniqueIdentifier())
			{
				$this->RenderProjectsProfile();
			}
			else
			{
				$this->blocksMarkup .= "<:=Projects_Nothing_Found=:>";
			}
		}
		
		private function RenderProjectsFields()
		{
			$this->blocksMarkup .= "<div id='projectsFields'>";
			
			$company = new Companies();
			$company->companies_id = $this->projects->projects_companies_id;
			$company->GetCompanyById();

			$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Name=:></div><div><span style='float:left'>{$this->projects->name} (<a href='/Companies/designer/'>Ваш дизайнер</a>)</span><span><a href='{$this->projects->www}'>{$this->projects->www}</a></span></div></div>";
			$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Sdate=:></div><div><span>".date('Y-m-d H:i:s', $this->projects->sdate)."</span>&nbsp;&nbsp;&nbsp;<:=Projects_Edate=:>&nbsp;<span>".date('Y-m-d H:i:s', $this->projects->edate)."</span></div></div>";
			$this->blocksMarkup .= "<div class='row' style='height:100px'><div class='title' style='height: 100px;'><:=Projects_Comment=:></div><div style='height: 100px; overflow: auto; width: 537px;'>".$this->projects->comment."</div></div>";

			$this->blocksMarkup .= "</div>";
		}
		
		private function RenderProjectsProfile()
		{
			$result = NULL;
						
			$this->RenderProjectsFields();

			$this->isReadOnly = !(Session::GetUserId() == $this->projects->updated_login || Session::IsSuperAdmin());
			
			$this->RenderAlbum($this->projects->projects_id, (float)Security::DecodeUrlData($_POST['ALBUMS_ID']));

			//$this->blocksMarkup .= "<b>Testing ".$this->projects->name."</b>";
		}
		
		private function RenderAlbum($projectsId = 0, $albumsId = 0)
		{
			$album = new BlockAlbum();
			$album->page = $this->page;
			
			$album->selectedObjectsId = (float)$projectsId;
			$album->selectedEntityId = 4;
			$album->selectedAlbumsId = (float)$albumsId;
			$album->isReadOnly = $this->isReadOnly;
			$album->albumsHeight = 150;
			$album->albumsWidth = 723;
			$album->BlockMode = RENDER_COMMON_ALBUM;
			$album->BlockInit();

			if(!$this->isAjaxRequest)
				$this->blocksMarkup .= "<br/><br/><:=BLOCK_ALBUM=:>";
			else
				$this->blocksAjaxMarkup .= "<:=BLOCK_ALBUM=:>";

			$this->blocks['BLOCK_ALBUM'] = $album;
		}
		
		private function RenderMainProjects()
		{
			$this->projects = new Projects();
			//$this->projects->is_finished = false;
			$this->projects->is_public = true;

			if( Session::GetUserId() > 0)
			{
				$this->projects->owner_id = $this->projects->updated_login = (float)Session::GetUserId();
			}

			$result = $this->projects->LoadProjects('', 0, 10, '`UPDATED_TIME` DESC');	
			
			$this->blocksMarkup .= "<div class='pHead'><span class='pTitle'><:=Projects_Block_Title=:></span>";
			$this->blocksMarkup .= "<span class='pAll'><a href='".Request::GetRoot()."/Project/'><:=Projects_All_Title=:></a></span></div>";

			if($result != NULL && (@mysql_num_rows($result) > 0 || is_array($result) && count($result) > 0))
			{
				foreach($result as $row)
				{
					$this->blocksMarkup .= "<div class='pRow'><a href='".Request::GetRoot()."/Project/".(strlen($row['UNIQUE_NAME_IDENTIFIER']) > 0 ? $row['UNIQUE_NAME_IDENTIFIER'] : Security::EncodeUrlData((float)$row['PROJECTS_ID']))."/'>".$row['NAME']."</a> ".( ((float)$row['COMPANIES_ID'] > 0) ? "(<a href='/Companies/{$row['COMPANY_IDENTIFIER']}'>{$row['COMPANY_NAME']}</a>)" : "" )." </div>";
				}
			}			
		}

		private function RenderStepsDiagram()
		{
			$result = NULL;

			$this->steps = new Steps();

			$this->steps->projects_id = $this->projects->projects_id = $this->selectedPrjId;
			$result = $this->steps->LoadProjectsSteps();
			
			$this->blocksMarkup .= "<div id='stepsGrid'>";

			if(mysql_num_rows($result) > 0)
			{
				$i = 1;
				while($row = mysql_fetch_array($result))
				{
					$this->blocksMarkup .= BlockProjects::RenderStepRow($row['STEP_NAME'], $row['PROJECTS_STEPS_ID'], 1, $row['START_TIME'], $row['END_TIME'], $row['IS_FINISHED'], $i++);
				}
			}

			if($this->page->isAjaxRequest)
			{
				$this->blocksAjaxMarkup .= $this->blocksMarkup;

				$this->blocksAjaxMarkup .= "<div class='bottom'>";

				$this->page->commonScripts .= "Objects.Environment['PROJECTS_ID'] = '".Security::EncodeUrlData($this->selectedPrjId)."'; ";

				if($this->drawNewStepLink) 
				{
					$link = new Anchor();
					$link->SCode = sha1("DrawTheNewStepForm");//Request::GetSCode() == sha1('DrawTheNewStepForm')
					$link->title = '<:=Projects_Step_Create_Link=:>';
					$link->href = Request::$url;
					$link->hrefAJAX = Request::$url; 
					$link->isTraditionalHref = false;
					$link->applyScripts = false; 
					$link->onClick = "AddStepClick(); return false;"; //AjaxStepsClick
					$link->class = "ajaxLink";

					$this->blocksAjaxMarkup .= "<div>".$link->OpenTag()."<:=Projects_Step_Create_Link=:>".$link->CloseTag()."</div>";

					$this->page->commonScripts .= $link->appendClientScript;
				}

				$calendar = new BlockCalendar();
	
				$this->blocksAjaxMarkup .= "<div id='daysOfWeek'>";
	
				for($i = 0; $i < 7; $i++)
				{
					$add = "+".$i." day";
					$this->blocksAjaxMarkup .= "<ul><li>".date("d", strtotime($add))."</li><li>".$calendar->GetLocalValue("DayAbbr".$i)."</li></ul>";
				}
	
				$this->blocksAjaxMarkup .= "</div></div>";
	
				$this->blocksMarkup .= "</div>";
			}
			else
			{
				$this->page->commonScripts .= '<script>function AjaxStepsClick(stepId, mode, refreshObj) { 
				
				if(Security.isBlockedGui)
				{
					return ;
				}
				else
				{
					Security.isBlockedGui = true;
				}
				
				var parameters = { 	
					__SVar  			: Objects.Security.secureServerVar, 
					__SCode 			: "RefreshStep", 
					__ClientVar 		: Objects.Security.createSecureVar(),
					PROJECTS_ID			: Objects.Environment[\'PROJECTS_ID\'],
					PROJECTS_STEPS_ID 	: stepId,
					IS_AJAX 			: \'TRUE\'
					}

				if(mode == 1)
				{
					parameters["__SCode"] = "'.sha1("EditStepRow").'";
				}
				
				if(mode == 2)
				{
					parameters["__SCode"] = "'.sha1("FinIsHsTep").'";
				}

				var rq = new Ajax.Request(document.location.href, { parameters : parameters, 
				onCreate: function () { 
					if(mode == 1) 
					{
						Objects.BubbleDiv.height = 300;
						Objects.BubbleDiv.width = 480;
						Objects.BubbleDiv.show(); 
					}
				}, 
			  	onSuccess: function(response) 
			  	{
	  				try
	  				{
		  				var rObject = Objects.Security.validateResponse(response.responseJSON);
					
		  				if(rObject != null && rObject.isSecured)
			  			{
							if(mode == 1)
								Objects.BubbleDiv.refresh(rObject.text);
							
							if(mode == 2 && refreshObj != null)
								refreshObj.innerHTML = rObject.text.substr(0,2);
							
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
				
				Security.isBlockedGui = false;
				}</script>';
			}
		}
		
		public static function RenderStepRow($sname, $sid, $show_mode = 0, $start_time = 0, $end_time = 0, $is_finished = false, $row_counter = 0)
		{
			$identity = Security::EncodeUrlData($sid);
			
			if($row_counter > 0)
				$counter = $row_counter.".";
			
			$result = "<div class='stepsRow'><div class='counter'>".$counter."</div><div class='status' title='".($is_finished ? "<:=Projects_Step_Finished=:>" : "<:=Projects_Step_Open=:>")."' onClick=\"AjaxStepsClick('{$identity}', 2, this);\">".($is_finished ? "٧" : "&nbsp;")."</div><div class='stepsTitle' onClick=\"AjaxStepsClick('{$identity}');\" title='{$sname}'>{$sname}</div><div class='stepsScale'>";

			$now = strtotime("now"); // current time

			if($show_mode > 0)
			{
				$window = 0;
				if($show_mode == 1)
				{
					$window = strtotime("+7 day");
				}

				$stime = strtotime($start_time);
				$etime = strtotime($end_time);

				if($window > 0 && $stime < $window && ($etime - $now > 0))
				{
					if($etime > $window)
					{
						$etime = $window;
					}

					if($stime < $now)
					{
						$stime = $now;
					}
					
					$left_border = round(($stime-$now)/(60*60*24));
					$right_border = round(($etime-$now)/(60*60*24));

					$result .= "<span style='margin-left:".($left_border * 25)."px; float: left; width:".(($right_border - $left_border) * 25) ."px; display:inline-block; background-color:#FF0000;'>&nbsp;</span>";
				}
				else
				{ // red alert!
					$result .= "<span style='color:#FF0000;'>time is up</span>";
				}
			}

			$result .= "</div><ul><li class='del' onClick=\"AjaxStepsClick('{$identity}', 2);\" title='<:=Projects_Step_Delete=:>'>×</li><li class='edit' title='<:=Projects_Step_Edit=:>' onClick=\"AjaxStepsClick('{$identity}', 1);\">…</li></ul></div>"; //√٧
			
			return $result;
		}

		private function InitClientScripts()
		{
			
			$this->page->commonHead .= '<script>
			
					function ProjectClick(id, action) { 
					
					if(Security.isBlockedGui)
					{
						return ;
					}
					else
					{
						Security.isBlockedGui = true;
					}

					var parameters = { 	
						__SVar  	: Objects.Security.secureServerVar, 
		  				__SCode 	: "'.$this->SCodes['ajax_bubble'].'", 
				  		__ClientVar : Objects.Security.createSecureVar(),
				  		IS_AJAX 	: "TRUE"
				  		};
					
					if(action != null && action > 0)
					{
						
						parameters["PROJECTS_ID"] = id;

						if(action == 2)
						{
							parameters["DELETE"] = "TRUE";
						}
						if(action == 3)
						{
							parameters["ACTIVATE"] = "TRUE";
						}
					}

					var rq = new Ajax.Request(document.location.href, {
	
						parameters : parameters,
					
			  			onCreate: function ()
			  			{
							if(action == null || action < 2)
							{
								Objects.BubbleDiv.height = 405;
								Objects.BubbleDiv.width = 480;
								Objects.BubbleDiv.position = "midtop";
								Objects.BubbleDiv.position = "midtop";
								Objects.BubbleDiv.title = "'.$this->GetLocalValue("Projects_Create_Link").'";
								Objects.BubbleDiv.show();
							}
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
									if(action == 3)
										Objects.Environment[\'result\'] = rObject.text;
									else
										Objects.BubbleDiv.refresh(rObject.text);

					  				if(rObject.scripts.length > 0)
						  				eval(rObject.scripts[0]); 

									//alert(action + " " + rObject.text + " " + Objects.Environment[\'result\']);

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

	
					Security.isBlockedGui = false;
					}
				</script>';

		}
		
		private function RenderGrid($result = NULL)
		{

			//Init of the Grid
			$grid = new Grid();
				
			$grid->needDrawForm = false;
				
			$grid->needDrawHeaders = true;

			// Init the Headers
			$fieldsArray = array();
			
			$field = new Field();
			$field->name = "projects_id";
			$field->value = "<:=Projects_Id_Header=:>";
			$field->hidden = true;
			$field->width = "150px";
	
			array_push($fieldsArray, $field);

			$field = new Field();
			$field->name = "projects_name";
			$field->width = "200px";
	
			if($this->showInBubble)
			{
				$anchor = new Anchor();
				$anchor->id = "p_add_button";
				$anchor->title = "<:=Projects_Add_Button=:>";
				$anchor->href = "/Project/";
				$anchor->hrefAJAX = Request::$url;
				$anchor->isTraditionalHref = false;
				$anchor->onClick = "ProjectClick(); return false;";

				echo $this->InitClientScripts();

				$field->value = "<:=Projects_Name=:>".$anchor->OpenTag()."+".$anchor->CloseTag();
			}
			else
			{
				$field->value = "<:=Projects_Name=:>";
			}
	
			array_push($fieldsArray, $field);
	
			if($this->justNameColumn)
			{
				$field = new Field();
				$field->name = "projects_unique_name";
				$field->value = "<:=Projects_Unique_Name=:>";
				$field->width = "100px";
	
				array_push($fieldsArray, $field);
		
				$field = new Field();
				$field->name = "projects_company";
				$field->value = "<:=Projects_Company=:>";					
	
				array_push($fieldsArray, $field);
	
				$field = new Field();
				$field->name = "projects_days_to_finish";
				$field->value = "<:=Projects_Days_To_Finish=:>";
				$field->width = "20px";
		
				array_push($fieldsArray, $field);
	
				$field = new Field();
				$field->name = "projects_status";
				$field->value = "<:=Projects_Status=:>";
				$field->width = "20px";
		
				array_push($fieldsArray, $field);
	
				$field = new Field();
				$field->name = "projects_views";
				$field->value = "<:=Projects_Views=:>";
				$field->width = "20px";
		
				array_push($fieldsArray, $field);
		
				$field = new Field();
				$field->name = "projects_votes";
				$field->value = "<:=Projects_Votes=:>";
				$field->width = "20px";
					
				array_push($fieldsArray, $field);
					
				$field = new Field();
				$field->name = "projects_rating";
				$field->value = "<:=Projects_Rating=:>";
				$field->width = "20px";
					
				array_push($fieldsArray, $field);
			}
	
			$field = new RowProperties();
			$field->name = "ROW_PROPERTIES";
			$field->isSelected = false;
	
			$fieldsArray['ROW_PROPERTIES'] = $field;
						
			$grid->fieldsArray = $fieldsArray;
				
			$this->blocksMarkup .= $grid->RenderTop();	

			if($result != NULL && is_array($result))
			{
				$nechet = true;
				foreach($result as $row)
				{
					$fieldsArray = array();
					
					$field = new Field();
					$field->name = "projects_id";
					$field->value = "m_".$row['PROJECTS_ID']; 
					$field->hidden = true;
	
					array_push($fieldsArray, $field);
	
					$field = new Field();
					$field->name = "projects_name";

					$prjId = (strlen($row['UNIQUE_NAME_IDENTIFIER']) == 0 ? Security::EncodeUrlData((float)$row['PROJECTS_ID']) : $row['UNIQUE_NAME_IDENTIFIER']);				

					$link = new Anchor();
					$link->SCode = sha1("ReFrEshStePsList");
					$link->title = "<:=Projects_Step_Submit=:>";
					$link->href = Request::GetRoot()."/Project/".$prjId."/";
	
					if(strlen($this->refreshDiv) > 0)
					{
						$link->hrefAJAX = Request::$url; 
	
						$link->isTraditionalHref = false;
						$link->refreshElementId = $this->refreshDiv;
						$link->getParamsValues = false;
						$link->params = array("PROJECTS_ID" => Security::EncodeUrlData((float)$row['PROJECTS_ID']));
	
						$link->applyScripts = true; 
					}
					
					$field->value = $link->OpenTag().$row['NAME'].$link->CloseTag();
						
					array_push($fieldsArray, $field);
	
					if($this->justNameColumn)
					{
						$field = new Field();
						$field->name = "projects_unique_name";
						$field->value = $row['UNIQUE_NAME_IDENTIFIER'];
		
						array_push($fieldsArray, $field);
									
						$field = new Field();
						$field->name = "projects_company";
						$field->value = $row['COMPANY_NAME'];
									
						array_push($fieldsArray, $field);
	
						$field = new Field();
						$field->name = "projects_days_to_finish";
						$field->value = $row['DAYS_TO_FINISH'];
		
						array_push($fieldsArray, $field);
						
						$field = new Field();
						$field->name = "projects_status";
						$field->value = $row['PROJECTS_STATUS'];
						
						array_push($fieldsArray, $field);
						
						$field = new Field();
						$field->name = "projects_views";
						$field->value = $row['PROJECTS_VIEWS'];
						
						array_push($fieldsArray, $field);
						
						$field = new Field();
						$field->name = "projects_votes";
						$field->value = $row['PROJECTS_VOTES'];
						
						array_push($fieldsArray, $field);
						
						$field = new Field();
						$field->name = "projects_rating";
						$field->value = $row['PROJECTS_RATING'];
						
						array_push($fieldsArray, $field);
					}
	
					$field = new RowProperties();
					$field->isSelected = $nechet;
					$field->name = "ROW_PROPERTIES";
	
					$fieldsArray['ROW_PROPERTIES'] = $field;

					$this->blocksMarkup .= $grid->RenderRow($fieldsArray);
				}
											
			}

			$this->blocksMarkup .= $grid->RenderBottom();


			$this->page->commonScripts = " Objects.Environment['PROJECTS_ID'] = '".Security::EncodeUrlData((float)$row['PROJECTS_ID'])."'; ";
			
		}
		
		private function RenderLinks()
		{
			while ($row = mysql_fetch_array($result))
			{
				$this->blocksMarkup .= "<table>";			
			
				if(!$this->isCustomLink)
				{
					$this->blocksMarkup .= "<tr><td><a href='".$this->linkForListing.$dataRow['UNIQUE_NAME_IDENTIFIER']."'>".$this->dataRow['NAME']."</a> (".$this->dataRow['UNIQUE_NAME_IDENTIFIER'].")</td></tr>";
				}
				else
				{
					$anchor = new Anchor();
					$anchor->SCode = $this->sCodeForListing;
					$anchor->title = $row["NAME"];
					$anchor->isTraditionalHref = false;
					$anchor->href = $this->linkForListing;

					$this->blocksMarkup .= $anchor->OpenTag()."".$row['NAME'];
					$this->blocksMarkup .= $anchor->CloseTag()."<br/>";
				}

				$this->blocksMarkup .= "</table>";
			}
		}
		
		private function RenderProjectsList()
		{
			$this->blocksMarkup = "Not implemented";
		}

		private function RenderProjectsLeftList()
		{
			if(!$this->isAjaxRequest && Session::GetUserId() > 0)
			{
				if($this->projects->companies_id > 0)
				{
					$result = $this->projects->LoadCompaniesProjects();
				}
				elseif($this->projects->users_id > 0)
				{
					$result = $this->projects->LoadUsersProjects();
				}
				else
				{
					if(Session::GetUserId() > 0 || Session::IsSuperAdmin())
					{
						if(Session::IsSuperAdmin())
							$this->projects->owner_id = 0;

						$this->projects->updated_login = Session::GetUserId();
					}
										
					$result = $this->projects->LoadProjects();
				}

				$this->RenderGrid($result);
			}
			elseif((Request::GetSCode() == $this->SCodes['ajax_bubble']) || $this->SCodes['create_project'] == Request::GetSCode())
			{
				$this->page->isAjaxJSON = true;
				
				$newPrj = new BlockProjects();
				$newPrj->page = $this;
				$newPrj->BlockMode = SINGLE_PROJECT;
				$newPrj->BlockInit();

				$this->blocks['NEW_PROJECT'] = $newPrj;
			
				$this->blocksAjaxMarkup .= '<:=NEW_PROJECT=:>';

				$this->page->commonScripts = $newPrj->page->commonScripts;
			}
			else
			{	
				$this->page->isAjaxJSON = true;
				$this->selectedPrjId = (float)Security::DecodeUrlData($_POST['PROJECTS_ID']);

				$this->RenderStepsDiagram();
			}
		}
		
		private function RenderProjectsForm()
		{

			if(Session::GetUserId() > 0)
			{
				$this->projects = new Projects();
				
				if(strlen($_POST['PROJECTS_ID']) > 0)
					$this->projects->projects_id = (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['PROJECTS_ID']));
				else
					$this->projects->projects_id = (float) $this->selectedPrjId;
				
				if(!Session::IsSuperAdmin())
					$this->projects->updated_login = (float) Session::GetUserId();
				
				$this->projects->GetProjectById();				
			}

			if($this->page->isValidPost && $this->projects->isLoaded && $_POST["DELETE"] == "TRUE")
			{
				$this->projects->DeleteProject();
			}
			elseif($this->page->isValidPost && $this->projects->isLoaded && $_POST["ACTIVATE"] == "TRUE")
			{
				$this->blocksAjaxMarkup .= ( $this->projects->PublicateProject() > 0 ? "on" : "off" );
			}
			elseif(!$this->page->isValidPost || !isset($_POST["ACTIVATE"]) && ($this->page->isValidPost && !Request::IsPostType() || $this->page->isValidPost && !$this->ValidateFirstForm() ) ) 
			{

				$this->formTag = new Form();

				if(!$this->isAjaxRequest)
				{
					$this->blocksMarkup .= $this->formTag->RenderTop();

					$hdn = new Hidden();
					$hdn->SetName("__SCode");
					$hdn->SetValue($this->SCodes['create_project']);
				
					$this->blocksMarkup .= $hdn->OpenTag();
				}

				if($this->SCodes['create_project'] != Request::GetSCode())
				{
					$this->blocksMarkup .= "<div id='projectsFields'>";
				}	
				
				if($this->projects->projects_id > 0)
				{
					$hdn = new Hidden();
					$hdn->SetName("ProjectsId");
					$hdn->SetValue(Security::EncodeUrlData($this->projects->projects_id));
				
					$this->blocksMarkup .= $hdn->OpenTag();
				}
				
				include_once("includes/common/Tag/Lib.CountriesList.php");

				$countryList = new CountriesList();
				$countryList->name = "projects_country_field";
				$countryList->id = "projects_country_field";
				$countryList->width = "290px";


				$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Name=:></div><div><input type='text' id='projects_name' name='projects_name' value='".$this->projects->name."' />".$this->formTag->RenderAsterisks($this->fieldsArray['projects_name'])."</div></div>";

				$companies = new Companies();
				if($this->projects->companies_id > 0)
				{
					$companies->companies_id = (float)$this->projects->companies_id;					
				}
				else
				{
					$companies->users_id = (float)Session::GetUserId();
				}
				
				$companies->GetCompanyById();
				
				//!!!! Projects templates For the future
				//$this->pagesMarkup .= "<tr><td><:=Projects_Type=:></td><td><input type='text' id='projects_type' name='projects_type' value='".$this->projects->projects_type."'></td></tr>";

				$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Unique_Identifier=:></div><div><input type='text' name='projects_identifier' id='projects_identifier' value='".$this->projects->unique_name_identifier."'>".$this->formTag->RenderAsterisks($this->fieldsArray['projects_identifier'])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Sdate=:></div><div><input type='text' id='projects_sdate' name='projects_sdate' value='".date('Y-m-d H:i:s', $this->projects->sdate)."' />".$this->formTag->RenderAsterisks($this->fieldsArray['projects_sdate'])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Edate=:></div><div><input type='text' id='projects_edate' name='projects_edate' value='".date('Y-m-d H:i:s', $this->projects->edate)."' />".$this->formTag->RenderAsterisks($this->fieldsArray['projects_edate'])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Country=:></div><div>".$countryList->GetCountriesList($this->projects->country_name)."".$this->formTag->RenderAsterisks($this->fieldsArray["project_country_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Name_City=:></div><div><input type='hidden' id='projects_city_id' name='projects_city_id' value='".$this->projects->city_id."' /><input type='text' id='projects_city_name_field' name='projects_city_name_field' value='".$this->projects->city_name."' />".$this->formTag->RenderAsterisks($this->fieldsArray["projects_city_name_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Companies=:></div><div><input type='hidden' name='projects_companies_id' id='projects_companies_id' value='{$this->projects->companies_id}' /><input type='text' name='projects_company_name' value='{$this->projects->companies_name}' /></div></div>";
				$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_WWW=:></div><div><input type='text' id='projects_www' name='projects_www' value='{$this->projects->www}' />".$this->formTag->RenderAsterisks($this->fieldsArray["projects_www"])."</div></div>";
				$this->blocksMarkup .= "<div class='row' style='height:100px'><div class='title'><:=Projects_Comment=:></div><div><textarea id='projects_comment' name='projects_comment' cols='33' rows='5'>".$this->projects->comment."</textarea></div></div>";

				if($this->SCodes['create_project'] != Request::GetSCode() )
				{
					$this->blocksMarkup .= "</div>";

					if($this->isAjaxRequest)
					{
						$link = new Anchor();
						$link->SCode = $this->SCodes['create_project'];
						$link->title = "<:=Projects_Create_Link=:>";
						$link->href = Request::$url;
						$link->hrefAJAX = Request::$url; 
						$link->isTraditionalHref = false;
						$link->refreshElementId = 'projectsFields';
						$link->getParamsValues = true;
						$link->applyScripts = false; 
						$link->class = "ajaxLink";
						$link->params = array("PROJECTS_ID" => '', 'projects_name' => '', 'projects_www' => '', 'projects_identifier' => '', 'projects_companies_id' => '', 'projects_type' => '', 'projects_sdate' => '', 'projects_edate' => '', 'projects_country_field' => '', 'projects_city_id' => '', 'projects_comment' => '');

						$this->blocksMarkup .= "<div style='text-align:right; padding: 5px;'>".$link->OpenTag()."<:=Projects_Create_Link=:>".$link->CloseTag()."</div>";

						$this->page->commonScripts = $link->appendClientScript;
						
						if($this->projects->isLoaded)
						{
							$this->page->commonScripts .= " Objects.Environment['PROJECTS_ID'] = '".Security::EncodeUrlData($this->projects->projects_id)."'; ";
						}
						else
						{
							$this->page->commonScripts .= " Objects.Environment['PROJECTS_ID'] = ''; ";
						}
						
					}
					else
					{
						$this->blocksMarkup .= "<table><tr><td colspan='2' >".$this->formTag->RenderSubmitButton("<:=Projects_Next_Step=:>")."</td></tr></table>";
					}
				}

				if($this->isAjaxRequest)
				{
				
					$this->blocksAjaxMarkup .= $this->blocksMarkup;
					$this->blocksMarkup = "";
				}
				else
				{
					$this->blocksMarkup .= $this->formTag->RenderBottom();
				}

			}
			elseif($this->isValidPost && !$this->projects->isLoaded && (Request::GetSCode() == $this->SCodes['create_project'])/*sha1("CreAtePrjFirStSteP")*/ && $this->isValidForm ) 
			{
				if(($prjId = (float)$this->projects->CreateProject()) > 0)
				{
					Session::SetProjectsId($prjId);
					$this->projects->projects_id = $prjId;

					if($this->isAjaxRequest)
					{
						$this->blocksAjaxMarkup = "<:=Project_Is_Successfully_Created=:>";
						$this->page->commonScripts = "Objects.BubbleDiv.hide(); alert('<:=Project_Is_Successfully_Created=:>'); ";
					}
					else
					{
						$this->blocksMarkup = "<:=Project_Is_Successfully_Created=:>";
					}
				}
				else 
				{
					Session::SetProjectsId(0);
					$this->blocksMarkup = "<:=Project_Creation_Error=:>";
				}	
			}
			elseif($this->isValidPost && (Request::GetSCode() == $this->SCodes['create_project'] && $this->projects->isLoaded || Request::GetSCode() == sha1("eDitOnePrOjeCt"))) 
			{
				if($this->ValidateFirstForm() && $this->projects->projects_id > 0)
				{
					if($this->projects->EditProject() > 0)
					{
						Session::SetProjectsId($this->projects->projects_id);
						$this->page->commonScripts = "Objects.BubbleDiv.hide(); alert('<:=Project_Successfully_Saved=:>'); ";
					}
				}
				else 
				{
					$this->page->commonScripts = "Objects.BubbleDiv.hide(); alert('<:=Project_Empty_Projects_Id=:>'); ";
					//$this->blocksMarkup = "<:=Project_Empty_Projects_Id=:>";
				}
			}

			if($this->isAjaxRequest)
			{
				$this->blocksAjaxMarkup .= $this->blocksMarkup;
			}	
		
		}
		
		public function BlockInit()
		{
			parent::BlockInit();
						
			$this->SwitchMode();			
		}
	}
?>