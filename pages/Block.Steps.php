<?

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


  include_once("CommonBlock.php");
  
  include_once("Block.Steps_Local.php");
  include_once("includes/common/Lib.php");
  
  include_once("pages/Block.Materials.php");
  
  include_once("includes/DatabaseClasses/Parts.Steps.php");
  include_once("includes/DatabaseClasses/Parts.Projects.php");

  	/**
	 * class Steps
	 *
	 * Кроки
	 *
	 * @package Blocks.pkg
	 */

	class BlockSteps extends CommonBlock
	{
		public $page = NULL;
		
		private $fieldsArray = array("project_step_name" => false, "sdate" => false, "edate" => false, 
									  "step_type_text" => false, "comment" => false);
		
		public $blocksMarkup = "";
		
		public $stepTitle = "<:=Step_Title=:>";
		
		private $isValidForm = false;
		
		private $formTag = NULL;
		
		public $StepMode = SINGLE_STEP; // SINGLE_STEP = renders the steps form, GET_STEPS = renders the steps list
		
		private $steps = NULL;
		private $projects = NULL;
		
		public $selectedPrjId = 0;
		
		public function __construct()
		{
			$this->localizator = new Steps_Local();
			$this->steps = new Steps();
			
			$this->steps->sdate = time();
			$this->steps->edate = time();			

			$this->SCodes = self::GetAllowedSCodes();

			parent::__construct();
		}

		
		public static function GetAllowedSCodes()
		{
			return array ('ajax_addform' => sha1('DrawTheNewStepForm'), 'ajax_create' => sha1("SubMitStePtODb"), 'ajax_edit' => sha1("EditStepRow"));
		}

		
		public function ValidateForm()
		{
			$this->isValidForm = false;
			
			if(Request::GetSCode() != sha1("SubMitStePtODb") && Request::GetSCode() != sha1("eDitProJectsSteP"))
			{
				$this->isValidForm = false;
				return $this->isValidForm;
			}
			
			if(!is_numeric($this->selectedPrjId) || ($this->selectedPrjId < 1))
			{
				$this->selectedPrjId = (float)Security::DecodeUrlData( StaticDatabase::CleanupTheField($_POST['PROJECTS_ID']) );
			}

			$this->steps->projects_steps_id = (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_POST['PROJECTS_STEPS_ID']));

			$this->steps->step_name = StaticDatabase::CleanupTheField($_POST['project_step_name']);

			if(strlen($this->steps->step_name) < 1)
			{
				$this->fieldsArray['project_step_name'] = "<:=Validation_Step_Name_Error=:>";
				$this->isValidForm = false;
			}
			else 
			{
				$this->isValidForm = true;
			}
						
			$this->steps->sdate = strtotime(StaticDatabase::CleanupTheField($_POST['step_sdate']));
			
			if($this->steps->sdate < 1)
			{
				$this->fieldsArray['sdate'] = "<:=Validation_Step_Date_Error=:>";
				$this->isValidForm = false;
				$this->steps->sdate = mktime(0, 0, 0, 8, 13, 2010);
			}
			else
			{
				$this->isValidForm = true & $this->isValidForm;
			}
			
			$this->steps->edate = strtotime(StaticDatabase::CleanupTheField($_POST['step_edate']));
			
			if($this->steps->edate < 1) 
			{
				$this->fieldsArray['edate'] = "<:=Validation_Step_Date_Error=:>";
				$this->isValidForm = false;
				$this->steps->edate = mktime(0, 0, 0, 8, 13, 2010);
			}
			else
			{
				$this->isValidForm = true & $this->isValidForm;
			}
						
			if($this->steps->edate - $this->steps->sdate <= 0 || ($this->steps->sdate - $this->projects->sdate <= 0 ))
			{
				//$dt = ($this->steps->edate - $this->steps->sdate <= 0)." _ ".($this->steps->sdate - $this->projects->sdate <= 0 )." ".$this->projects->sdate." ".$this->steps->sdate;
				$this->fieldsArray['sdate'] = "<:=Validation_Step_Date_Diff_Error=:>";
				$this->isValidForm = false;
			}
			else 
			{
				$this->isValidForm = true & $this->isValidForm;
			}
			
			$this->steps->type_text = StaticDatabase::CleanupTheField($_POST['step_type_text']);
			$this->steps->step_type_id = (float)StaticDatabase::CleanupTheField($_POST['step_type_id']);
			
			$this->steps->comment = StaticDatabase::CleanupTheField($_POST['comment']);

			return $this->isValidForm;
		}
		
		private function StepPreInit()
		{
		
			switch(Request::GetSCode())
			{
				case sha1("SubMitStePtODb"):
				
				break;
				
				case sha1("FinIsHsTep"):
				case sha1("EditStepRow"):
					if($this->isValidPost)					
					{
						$this->steps->projects_steps_id = (float) Security::DecodeUrlData($_POST['PROJECTS_STEPS_ID']);
						$this->steps->GetStepById();
					}

				break;
				
				case sha1("eDitProJectsSteP"):
					
					if($this->isValidPost)
					{
						$this->steps->projects_steps_id = (float)Security::DecodeUrlData(StaticDatabase::CleanupTheField($_GET['data']));
						$this->steps->GetStepById();
					}
				break;

				default:

				break;
			}
		}
		
		public function RenderDiagramRow()
		{
			
		}

		public static function InitClientScripts()
		{
			$result = 'function AddStepClick() { 
						var parameters = { 	
						__SVar  		: Objects.Security.secureServerVar,  
		  				__SCode 		: "'.sha1('DrawTheNewStepForm').'", 
				  		__ClientVar 	: Objects.Security.createSecureVar(),
				  		IS_AJAX 		: "TRUE",
						PROJECTS_ID 	: Objects.Environment[\'PROJECTS_ID\']
				  		};

					if(Security.isBlockedGui)
					{
						return ;
					}
					else
					{
						Security.isBlockedGui = true;
					}

					var rq = new Ajax.Request(document.location.href, {
	
						parameters : parameters,
				  	
			  			onCreate: function ()
			  			{
							Objects.BubbleDiv.height = 300;
							Objects.BubbleDiv.width = 480;
							Objects.BubbleDiv.position = "midtop";
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

							Security.isBlockedGui = false;
							
						 } ';
			/*
				/////
			*/
			return $result;			
		}

		
		public function BlockInit()
		{
			parent::BlockInit();
			
			$this->StepPreInit();

			if(!$this->steps->isLoaded)
			{
				$this->projects = new Projects();

				if(strlen($_POST['PROJECTS_ID']) > 0 )
				{
					$this->projects->projects_id = (float)Security::DecodeUrlData($_POST['PROJECTS_ID']);
				}
				else
				{
					$this->projects->projects_id = (float) $this->selectedPrjId; //Session::GetProjectsId();
				}
		
				$this->projects->GetProjectById();
			}

			if($this->StepMode == SINGLE_STEP) 
			{
				if($this->page->isValidPost && Request::GetSCode() == sha1("FinIsHsTep"))
				{					
					$result = "";
					
					if($this->steps->isLoaded)
					{
						$is_finished = $this->steps->is_finished = !$this->steps->is_finished;
						if($this->steps->FinishStep())
						{
							$this->blocksMarkup = ($is_finished) ? "٧" : " ";
						}
						else
						{
							$this->blocksMarkup = "error";
						}
					}
				}
				elseif(!$this->page->isValidPost /*&& Session::GetUserId() == 0*/ || $this->page->isValidPost && $this->steps->isLoaded || $this->page->isValidPost && !$this->ValidateForm())
				{	
					$this->formTag = new Form();
					$this->formTag->name = "first_step";				
				
					if(!$this->page->isAjaxRequest)
					{
						$this->blocksMarkup = $this->formTag->RenderTop()."<div id='stepDivId'>";
						
						$hdn = new Hidden();
						$hdn->SetName("__SCode");
						$hdn->SetValue(sha1("SubMitStePtODb"));
						
						$this->blocksMarkup .= $hdn->OpenTag();
						
						if($this->steps->projects_steps_id > 0)
						{
							$hdn = new Hidden();
							$hdn->SetName("PROJECTS_STEPS_ID");
							$hdn->SetValue(Security::EncodeUrlData($this->steps->projects_steps_id));
							
							$this->blocksMarkup .= $hdn->OpenTag();
						}
						
						if($this->projects->projects_id > 0)
						{
							$hdn = new Hidden();
							$hdn->SetName("PROJECTS_ID");
							$hdn->SetValue(Security::EncodeUrlData($this->projects->projects_id));
						}
					}
					elseif(Request::GetSCode() != $this->SCodes['ajax_create'] && Request::GetSCode() != $this->SCodes['ajax_edit'] && Request::GetSCode() != $this->SCodes['ajax_addform']) // Just draw the link  Projects_Step_Create_Link
					{
						$link = new Anchor();
						$link->SCode = $this->SCodes['ajax_create'];
						$link->title = 'Projects_Step_Create_Link';
						$link->href = Request::$url;
						$link->hrefAJAX = Request::$url; 
						$link->isTraditionalHref = false;
						$link->getParamsValues = false;
						$link->applyScripts = false; 
						$link->class = "ajaxLink";

						$this->blocksMarkup .= "<div>".$link->OpenTag()."<:=Projects_Step_Create_Link=:>".$link->CloseTag()."</div>";

						$this->page->commonScripts = $link->appendClientScript;
					}

					if(Request::GetSCode() == sha1('DrawTheNewStepForm') || Request::GetSCode() == $this->SCodes['ajax_edit'] )	
						$this->blocksMarkup .= "<div id='stepsFields'>"; //steps_fields
					
					$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Step_Name=:></div><div><input id='project_step_name' name='project_step_name' type='text' value='".$this->steps->step_name."' />".$this->formTag->RenderAsterisks($this->fieldsArray['project_step_name'])."</div></div>";
					$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Step_SDate=:></div><div><input type='text' id='step_sdate' name='step_sdate' value='".date('Y-m-d H:i:s', $this->steps->sdate)."' />".$this->formTag->RenderAsterisks($this->fieldsArray['sdate'])."</div></div>";
					$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Step_EDate=:></div><div><input type='text' id='step_edate' name='step_edate' value='".date('Y-m-d H:i:s', $this->steps->edate)."' />".$this->formTag->RenderAsterisks($this->fieldsArray['edate'])."</div></div>";
					$this->blocksMarkup .= "<div class='row'><div class='title'><:=Projects_Step_Type=:></div><div><input type='hidden' id='step_type_id' name='step_type_id' value='".$this->steps->step_type_id."' /><input id='step_type_text' type='text' name='step_type_text' value='".$this->steps->step_type_text."' /></div></div>";
					$this->blocksMarkup .= "<div class='row' style='height: 105px; padding-top: 3px;'><div class='title'><:=Projects_Step_Comment=:></div><div><textarea cols='30' rows='5' id='comment' name='comment' >".$this->steps->comment."</textarea></div></div>";
					//Materials selections

					if(Request::GetSCode() == sha1('DrawTheNewStepForm') || Request::GetSCode() == $this->SCodes['ajax_edit'] )
					{
						$this->blocksMarkup .= "</div>";

						if($this->isAjaxRequest)
						{	
							$link = new Anchor();
							$link->SCode = $this->SCodes['ajax_create'];
							$link->title = "<:=Steps_Create_Link=:>";
							$link->href = Request::$url;
							$link->hrefAJAX = Request::$url; 
							$link->isTraditionalHref = false;
							$link->refreshElementId = 'stepsFields';
							$link->getParamsValues = true;
							$link->applyScripts = false; 
							$link->class = "ajaxLink";
							$link->params = array("PROJECTS_ID" => '', "PROJECTS_STEPS_ID" => '', "project_step_name" => '', 'step_sdate' => '', 'step_edate' => '', 'step_type_text' => '', 'comment' => '');

							$this->blocksMarkup .= "<div style='text-align: right; padding-right: 5px;'>".$link->OpenTag()."<:=Steps_Create_Link=:>".$link->CloseTag()."</div>";
	
							$this->page->commonScripts = $link->appendClientScript." Objects.Environment['PROJECTS_STEPS_ID'] = '".(string)Security::EncodeUrlData($this->steps->projects_steps_id)."';";
						}
						else
						{
							$this->blocksMarkup .= "<table><tr><td colspan='2' >".$this->formTag->RenderSubmitButton("<:=Projects_Next_Step=:>")."</td></tr></table>";
						}
					}
				}
				elseif($this->page->isValidPost && $this->isValidForm && Request::GetSCode() == sha1("SubMitStePtODb"))
				{
					$this->steps->projects_id = (float)$this->selectedPrjId; 

					if($this->steps->projects_steps_id < 1)
					{
						if($this->steps->CreateStep() > 0) 
						{
							if($this->page->isAjaxRequest)
							{
								$this->page->commonScripts = "<script> Objects.BubbleDiv.hide(); </script>";//"<script> /*$('stepsList').innerHtml = '".BlockProjects::RenderStepRow($this->steps->step_name, $this->steps->projects_steps_id, 0, $this->steps->start_date, $this->steps->end_date)."';*/ </script>";							
							}

							// draws the empty form
							$this->page->isValidPost = false;
							$this->blocksMarkup = "";
							$this->steps = NULL;

							$this->BlockInit();
						}
					}
					elseif($this->steps->projects_steps_id > 0)
					{
						$this->steps->SaveStep();
						

						if($this->page->isAjaxRequest)
						{
							$this->page->commonScripts = "<script> Objects.BubbleDiv.hide(); </script>";
						}
					}
				}
				
				if($this->page->isAjaxRequest)
				{
					$this->blocksAjaxMarkup = $this->blocksMarkup;
				}
				else
				{
					$link = new Anchor();
					$link->SCode = sha1("SubMitStePtODb");
					$link->title = "<:=Projects_Step_Submit=:>";
					$link->href = "";
					$link->hrefAJAX = Request::$url;
					$link->isTraditionalHref = false;
					$link->refreshElementId = "stepDivId";
					$link->applyScripts = true;
					$link->class = 'ajaxLink';
					$link->params = array("project_step_name" => $this->steps->step_name, "step_sdate" => date('Y-m-d H:i:s', $this->steps->sdate), "step_edate" => date('Y-m-d H:i:s', $this->steps->edate), 
									  	  "step_type_text" => $this->steps->step_type_text, "comment" => $this->steps->comment, 'PROJECTS_ID' => '');
					
					$this->blocksMarkup .= "</div>".$link->OpenTag()."<:=Projects_Step_Submit=:>".$link->CloseTag();					
					
					$this->blocksMarkup .= $this->formTag->RenderBottom();
				}
			}
			elseif($this->StepMode == GET_STEPS)
			{
				// 	$this->steps->projects_id = (float)$this->selectedPrjId; //Session::GetProjectsId();
				$result = $this->steps->LoadProjectsSteps();

				$this->blocksMarkup = "<table>";

				while($row = mysql_fetch_array($result))
				{
					$prjStepsId = (float)$row['PROJECTS_STEPS_ID'];
					$prjStepsId = Security::EncodeUrlData($prjStepsId);
					$this->blocksMarkup .= "<tr><td><a href='".Request::GetRoot()."/project/?__SVar=".Session::$mainSessionVariable."&__SCode=".sha1("eDitProJectsSteP")."&data=".$prjStepsId."'>".$row['STEP_NAME']."</a></td></tr>";
				}
				
				$this->blocksMarkup .= "</table>";
			}
			else 
			{
			}
		}
	}

?>