<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


  include_once("CommonBlock.php");

  include_once("Block.News_Local.php");
  include_once("Block.Calendar.php");
  include_once("includes/common/Lib.php");
  include_once("includes/common/Tag/Lib.Grid.php");
  include_once("includes/common/Tag/Lib.CompaniesList.php");
  include_once("includes/common/Lib.Operations.php");

  include_once("includes/DatabaseClasses/Parts.Materials.php");
  include_once("includes/DatabaseClasses/Parts.Projects.php");
  include_once("includes/DatabaseClasses/Parts.News.php");


  	/**
	 * class BlockNews
	 *
	 * @package Blocks.pkg
	 */

	class BlockNews extends CommonBlock
	{
		public $page = NULL;
		
		private $fieldsArray = array(
								"news_title" => false,
								"news_short_text" => false,
								"news_text" => false,
								"news_unique" => false,
								"news_entities_id" => false,
								"news_objects_id" => false,
								"news_langs_id" => false,
								"news_is_active" => false,
								"news_is_public" => false,
								"country_name_field" => false,
								"news_publication_time" => false);

		public $blocksMarkup = "";

		private $isValidForm = false;

		private $formTag = NULL;

		public $BlockMode = RENDER_NEWS_GRID ; // RENDER_NEWS_GRID = renders the grid with the latest news,
											   // ADD_NEWS = renders the form of the news creation
											   // AJAX_NEWS = draws the latest news list and the add news form
											   // NEWS_DETAILS = draws the current selected news
											   // RENDER_NEWS_
		
		public $filter_name = "";    // 
		public $filter_offset = 0;   // offset
		public $filter_limit = 0;    // limit
		
		private $steps = NULL;
		private $materials = NULL;
		
		private $selectedPrjId = 0;
		private $usersId = 0;
		
		public $entitiesId = 11;
		public $objectsId = 0;
		
		public $newsWindow = 20;
		public $newsOffset = 0;
		
		private $SCodes = NULL;
		
		public function __construct()
		{
			$this->localizator = new BlockNews_Local();
			$this->news = new PNews();
			
			$this->SCodes = self::GetAllowedSCodes();
						
			parent::__construct();
		}
		
		public static function GetAllowedSCodes()
		{
			return array ( sha1("SubMitNewsToDb"), /*'ajax_new' => sha1('DrawTheAddNewSfOrm'), */'ajax_editnews' => sha1("EdiTnEws"), 'ajax_delnews' => sha1("DelEteNeWs") );
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

			$this->news->news_name = StaticDatabase::CleanupTheField($_POST['news_title']);

			if(strlen($this->news->news_name) < 3)
			{
				$this->isValidForm = false;
				$this->fieldsArray['news_title'] = "<:=Validation_News_Name_Error=:>";
			}
			else
			{
				$this->isValidForm = true;
			}
			
			$this->news->news_short_text = StaticDatabase::CleanupTheField($_POST['news_short_text']);
			$this->news->news_text = StaticDatabase::CleanupTheField($_POST['news_text']);

			$tempNews = new PNews();
			$tempNews->unique_name_identifier = $this->news->unique_name_identifier = Operations::Translator(StaticDatabase::CleanupTheField($_POST['news_unique']));
			
			$tempNews->ValidateUniqueIdentifier(true);
			if( (strlen($this->news->unique_name_identifier) < 3 || $tempNews->news_id > 0 && $this->news->news_id != $tempNews->news_id ) )
			{
				$this->isValidForm = false; 
				
				if(strlen($this->news->unique_name_identifier) < 3)
				{
					$this->news->unique_name_identifier = Operations::Translator($this->news->news_name);
					$this->fieldsArray['news_unique'] = "<:=Validation_News_Unique_Identifier_Recheck=:>";
				}
				else
				{
					$this->fieldsArray['news_unique'] = "<:=Validation_News_Unique_Identifier_Error=:>";				
				}
			}
			else
			{
				$this->isValidForm = $this->isValidForm & true;
			}
			/*
			$countryArray = preg_split("/_/i", StaticDatabase::CleanupTheField($_POST['country_name_field']), 2);
			
			if(!is_numeric($countryArray[0]) || strlen($countryArray[1]) != 2)
			{
				$this->fieldsArray["country_name_field"] = "<:=Validation_Country_Name_Error=:>";
				$this->isValidForm = false;
			}
			else 
			{
				$this->news->countries_id = (float)$countryArray[0];

				$this->isValidForm = $this->isValidForm & true;
			}
			
			
			$this->news->entities_id = (float) StaticDatabase::CleanupTheField($_POST['news_entities_id']);
			$this->news->objects_id = (float) StaticDatabase::CleanupTheField($_POST['news_objects_id']);
			$this->news->langs_id = (float) StaticDatabase::CleanupTheField($_POST['news_langs_id']);
			$this->news->is_active = (bool) StaticDatabase::CleanupTheField($_POST['news_is_active']);
			*/
			$this->news->is_public = (bool) StaticDatabase::CleanupTheField($_POST['news_is_public']);

			$this->news->publication_time = strtotime(StaticDatabase::CleanupTheField($_POST['news_publication_time']));
			
			if($this->news->publication_time < 1 || $this->news->publication_time - time() < 0)
			{
				$this->isValidForm = false;

				if($this->news->publication_time < 1)
				{
					$this->news->publication_time = time();//mktime(0, 0, 0, 8, 13, 2010);//StaticDatabase::CleanupTheField($_POST['projects_sdate']);
					$this->fieldsArray["news_publication_time"] = "<:=Validation_DateTime_Error=:>";
				}
				else
				{
					$this->fieldsArray["news_publication_time"] = "<:=Validation_DateTime_Low_Error=:>";
				}

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
				case RENDER_NEWS_GRID:
					$this->RenderNewsGrid();
				break;
				
				case ADD_NEWS:
					$this->CreateNews();
				break;
				
				case AJAX_NEWS:
					$this->AjaxNews();
				break;
				
				case RENDER_LATEST_NEWS:
					$this->LatestNews();
				break;
				
				case RENDER_MAIN_NEWS_BLOCK:
					$this->MainNewsBlock();
				break;
				
				case NEWS_EDIT:
					//$this->NewsEdit();
					$this->AjaxNews();
				break;
				
				case NEWS_DETAILS:
					$this->NewsDetails();
				break;
				
				case RENDER_NEWS_SEARCH_RESULT:
					$this->RenderNewsSearchResults();
				break;
			}
		}
		
		public function NewsDetails()
		{
			$albumsId = 0;

			$this->news->unique_name_identifier = Request::$identifier;
			if(strlen(Request::$identifier) > 0)
			{
				$this->news->ValidateUniqueIdentifier(true);
			}
			else
			{
				$result = $this->news->LoadNewsById((float)$_POST['NEWS_ID']);
			}
				
			if($this->isValidPost && Request::GetSCode() == sha1("sAveFilEtoDatAbaSe"))
			{
				$this->RenderAlbum((float)Security::DecodeUrlData($_POST['OBJECTS_ID']), (float)Security::DecodeUrlData($_POST['ALBUMS_ID']));
			}
			elseif($this->news->is_public || (Session::GetUserId() > 0 && (Session::GetUserId() == $this->news->updated_login || Session::IsSuperAdmin()))) {
				$this->page->SetTitle($this->news->news_name." - ".$this->news->news_short_text);
				
				$this->blocksMarkup .= "<div id='newsDetails'>";
				//$this->blocksMarkup .= "<div class='row'><div><:=News_Unique_Name=:></div><div>{$this->news->unique_name_identifier}</div></div>";
				//$this->blocksMarkup .= "<div class='row'><div><:=News_Title=:></div><div>{$this->news->news_name}</div></div>";
				$this->blocksMarkup .= "<div class='title'>".BlockCalendar::GetNewsDateFormat($this->news->publication_time)." - {$this->news->news_name}</div>";
				$this->blocksMarkup .= "<div class='body'>".preg_replace("/\\\n/i", "<br>", $this->news->news_text)."</div>";
				//$this->blocksMarkup .= "<div class='row'><div><:=News_Publication=:></div><div>{$this->news->publication_time}</div></div>";
				//$this->blocksMarkup .= "<div class='row'><div><:=News_Tags=:></div><div>{$this->news->GetTags()}</div></div>";
				//$this->blocksMarkup .= "<div class='row'><div><:=News_Announce=:></div><div>{$this->news->news_short_text}</div></div>";
				//$this->blocksMarkup .= "<div class='row'><div><:=News_Body=:></div><div>{$this->news->news_text}</div></div>";
				//$this->blocksMarkup .= "<div class='row'><div><:=News_Active=:></div><div>{$this->news->is_active}</div></div>";
				//$this->blocksMarkup .= "<div class='row'><div><:=News_Public=:></div><div>{$this->news->is_public}</div></div>";
				$this->blocksMarkup .= "</div>";

			}
		}
		
		public function SetProjectsId($projectsId)
		{
			$this->selectedPrjId = (float) $projectsId;
		}
		
		private function RenderLatestNewsGrid()
		{
			$result = $this->news->LoadNews();
			
			$this->RenderGrid($result);
		}
		
		private function RenderNewsGrid()
		{
			$result = $this->news->LoadNews();	
			
			$this->RenderGrid($result);
		}
		
		private function RenderGrid($result)
		{
			if($result != NULL && mysql_num_rows($result) > 0)
			{
				// Init of the grid
				$grid = new Grid();

				$grid->needDrawHeaders = true;

				//Init the Headers
				$fieldsArray = array();
				
				$field = new Field();
				$field->name = "news_id";
				$field->value = "";
				$field->hidden = true;

				array_push($fieldsArray, $field);

				$field = new Field();
				$field->name = "news_unique_id";
				$field->value = "<:=News_Unique_Id=:>";
				$field->width = "100px";

				array_push($fieldsArray, $field);

				$field = new Field();
				$field->name = "news_name";
				$field->value = "<:=News_Name=:>";
				$field->width = "100px";

				array_push($fieldsArray, $field);

				$field = new Field();
				$field->name = "news_short_text";
				$field->value = "<:=News_Short_Text=:>";
				$field->width = "100px";

				array_push($fieldsArray, $field);

				$field = new Field();
				$field->name = "news_is_public";
				$field->value = "<:=News_Is_Public=:>";
				$field->width = "10px";

				array_push($fieldsArray, $field);

				$field = new Field();
				$field->name = "news_publication_time";
				$field->value = "<:=News_Publication_Time=:>";
				$field->width = "100px";

				array_push($fieldsArray, $field);

				$grid->fieldsArray = $fieldsArray;

				$this->blocksMarkup .= $grid->RenderTop();

				$nechet = true;				
				while($row = mysql_fetch_array($result))
				{
					$fieldsArray = array();
				
					$field = new Field();
					$field->name = "news_id";
					$field->value = "n_".$row['NEWS_ID'];
					$field->hidden = true;
					
					array_push($fieldsArray, $field);
					
					$field = new Field();
					$field->name = "news_name";
					$field->value = $row['NEWS_NAME'];
					
					array_push($fieldsArray, $field);
					
					$field = new Field();
					$field->name = "news_short_text";
					$field->value = $row["NEWS_SHORT_TEXT"];
					
					array_push($fieldsArray, $field);
					
					$field = new Field();
					$field->name = "news_publication_time";
					$field->value = $row["NEWS_PUBLICATION_TIME"];
					
					array_push($fieldsArray, $field);
					
					$field = new Field();
					$field->name = "news_is_public";
					$field->value = $row["IS_PUBLIC"];
					
					array_push($fieldsArray, $field);

					$field = new RowProperties();
					$field->isSelected = $nechet;
					$field->name = "ROW_PROPERTIES";
	
					$fieldsArray['ROW_PROPERTIES'] = $field;

					$this->blocksMarkup .= $grid->RenderRow($fieldsArray);
					
					$nechet = !$nechet;
				}
			}
		}
		
		public static function InitClientScripts()
		{
		
			$result = ' 						
					function NewsEdit(nid, mode) 
					{
						if(Security.isBlockedGui)
						{
							return ;
						}
						else
						{
							Security.isBlockedGui = true;
						}
					
						if(mode < 1)
							Objects.BubbleDiv.title = Objects.Dictionary["News_Add_New"]; 
						else
							Objects.BubbleDiv.title = Objects.Dictionary["News_Edit_Title"];

						var rMode = ((mode == 2) ? "'.sha1("DelEteNeWs").'" : "'.sha1("EdiTnEws").'");

						var parameters = { 	
							__SVar  		: Objects.Security.secureServerVar, 
							__SCode 		: rMode, 
							__ClientVar 	: Objects.Security.createSecureVar(),
							IS_AJAX 		: "TRUE", 
							NEWS_ID			: nid
						}
						
						var rq = new Ajax.Request(document.location.href, {
		
							parameters : parameters,
						
							onCreate: function ()
							{
								if(mode != 2)
								{
									Objects.BubbleDiv.height = 400;
									Objects.BubbleDiv.width = 580;
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
						 
			return $result;
		}
		
		public function MainNewsBlock()
		{
			$this->news = new PNews();
			$this->news->is_public = true;
			$result = $this->news->LoadNews('', 10, 0, '`PUBLICATION_TIME`');

			if($result != NULL)
			{
				$this->blocksMarkup .= "<div class='nHead'><span><:=News_Text=:></span><a href='/News/'><:=News_All=:></a></div>";
			
				while($row = mysql_fetch_array($result))
				{
					$this->blocksMarkup .= "<div class='newsRow'><h6>".BlockCalendar::GetNewsDateFormat($row['PUBLICATION_TIME'])." - ".$row['NEWS_NAME']."</h6>".
										   "<div class='short'><div>".preg_replace("/\n/i", "<br>", $row['NEWS_SHORT_TEXT'])."</div><a href=\"".Request::GetRoot()."/News/".(string)$row['UNIQUE_NAME_IDENTIFIER']."/\">Read more</a></div></div>";
				}
			}
			
		}

		private function SearchResultRow($row, $index = 0)
		{
			$regexp[0] = "/\\\n/i";
			$repl = "<br>";
			$this->blocksMarkup .= "<div class='resultsRow'><div style='display:inline-block;'><div class='number'>{$index}.</div><a href='".Request::GetRoot()."/News/".$row['UNIQUE_NAME_IDENTIFIER']."/' >".$row['NEWS_NAME']."</a>&nbsp;";
			$this->blocksMarkup .= "<br/><p>".preg_replace($regexp, $repl, $row['NEWS_SHORT_TEXT'])."</p></div>"; //BlockCalendar::GetNewsDateFormat($row['PUBLICATION_TIME'])
			
			if(Session::GetUserId() > 0 && (Session::GetUserId() == (float)$row['UPDATED_LOGIN'] || Session::IsSuperAdmin()))
			{
				$this->blocksMarkup .= "<ul><li onClick='cDel(\"".Security::EncodeUrlData($row['NEWS_ID'])."\");' title='<:=News_Row_Delete=:>'>×</li><li onClick='cEdit(\"".Security::EncodeUrlData($row['NEWS_ID'])."\");' title='<:=News_Row_Edit=:>' class='edit'>…</li></ul>";
			}
			
			$this->blocksMarkup .= "</div>";
		}

		private function RenderSearchResults($result, $all = false)
		{
			$this->blocksMarkup .= "<div id='searchResults'>";

			if($result != NULL)
			{
				$newsCount = mysql_num_rows($result); //$this->news->RowsCount();

				$local = $this->GetLocalValue('News_Edit');

				$this->page->commonScripts .= 'function cDel(id) { if(confirm("'.$this->GetLocalValue("News_Are_You_Sure_Delete").'") )  NewsEdit(id, 2); Objects.BubbleDiv.hide(); }; function cEdit(id) { NewsEdit(id, 1); } ';

				$i = 1;
				while($row = mysql_fetch_array($result))
				{	
					$this->SearchResultRow($row, $this->newsOffset + $i++);
				}

				if($all)
				{
					$this->blocksMarkup .= "<div class='searchIndexer'>";
					
					for($i = 0; $i < ($newsCount/$this->newsWindow); $i++)
					{
						$this->blocksMarkup .= "<a href='".Request::GetRoot()."/News/?offset=".$i."' ".(($this->newsOffset/$this->newsWindow == $i) ? "class='selected'" : "");
							
						$this->blocksMarkup .= ">".($i + 1)."</a>";
					}
					
					$this->blocksMarkup .= "</div>";
				}
			}
			else
			{
				$this->blocksMarkup .= "<p><:=News_Nothing_To_Show=:></p>";
			}

			$this->blocksMarkup .= "</div>";
		}
		
		private function RenderNewsSearchResults()
		{
			$this->news->unique_name_identifier = Request::$identifier;

			if(strlen($this->news->unique_name_identifier) < 1)
			{ 
				$this->page->SetTitle($this->GetLocalValue('News_All'));

				$this->newsOffset = ((float)$_GET['offset']) * $this->newsWindow;
				
				if(Session::GetUserId() > 0)
				{
					$this->news->updated_login = Session::GetUserId();
				}

				$this->news->is_public = true;

				$result = $this->news->LoadNews('', $this->newsWindow, $this->newsOffset, '`NEWS_NAME`');

				$this->RenderSearchResults($result, true);
			}
			elseif( ucfirst(strtolower($this->news->unique_name_identifier)) == 'Latest' )
			{
				$this->page->SetTitle($this->GetLocalValue('News_Latest'));

				if(Session::GetUserLogin() > 0)
				{
					$this->news->updated_login = Session::GetUserLogin();
				}

				$this->news->is_public = true;

				$result = $this->news->LoadNews('', $this->newsWindow, $this->newsOffset, '`UPDATED_TIME`');

				$this->RenderSearchResults($result);
			}
		}
		
		private function AjaxNews()
		{
			$this->news = new PNews();
			$this->news->publication_time = time();
			$sentBack = (bool)$_POST['sentBack'];
			
			$this->news->news_id = (float)Security::DecodeUrlData($_POST['NEWS_ID']);
			$this->news->LoadNewsById();
			
			$this->formTag = new Form();

			if($this->isValidPost && !in_array(Request::GetSCode(), $this->SCodes))
			{
				$this->news->objects_id = (float)Security::DecodeUrlData($_POST['PROJECTS_ID']);
				$result = $this->news->LoadNews();
				
				if($result != NULL)
				{
					$this->blocksAjaxMarkup .= "<div id='newsList'>";
				
					while($row = mysql_fetch_array($result))
					{
						$this->blocksAjaxMarkup .= "<div class='row'><div >".BlockCalendar::GetNewsDateFormat($row['PUBLICATION_TIME'])."</div>".$row['NEWS_NAME']."<ul><li class='del' onClick='NewsEdit(\"".Security::EncodeUrlData($row['NEWS_ID'])."\", 2);'>×</li><li class='public'>O<p style='display:none;'>●</p></li><li class='edit' onClick='NewsEdit(\"".Security::EncodeUrlData($row['NEWS_ID'])."\", 1);'>…</li></ul></div>";
					}
					
					$this->blocksAjaxMarkup .= "</div>";
				}

				$link = new Anchor();
				$link->SCode = $this->SCodes['ajax_editnews'];
				$link->title = "<:=News_Add_New=:>";
				$link->class = "ajaxLink";
				$link->href = Request::$url;
				$link->hrefAJAX = Request::$url;
				$link->isTraditionalHref = false;
				$link->applyScripts = false;
				$link->onClick = "NewsEdit('', 0); return false;";

				$this->blocksAjaxMarkup .= "<div>".$link->OpenTag()."<:=News_Add_New=:>".$link->CloseTag()."</div>";

				$this->page->commonScripts .= $link->appendClientScript.' Objects.Dictionary["News_Add_New"] = "'.$this->GetLocalValue('News_Add_New').'"; Objects.Dictionary["News_Edit_Title"] = "'.$this->GetLocalValue('News_Edit_Title').'"; ';
			}
			elseif( Request::GetSCode() != $this->SCodes['ajax_delnews'] && ($this->isValidPost && !$sentBack || ($this->isValidPost && $sentBack && !$this->ValidateForm()) ))
			{
				//$result = "ddd ".$this->isValidForm;
				$result  .= "<input type='hidden' name='sentBack' id='sentBack' value='1'>";
				
				if($this->news->news_id > 0)
				{
					$result .= "<input type='hidden' name='NEWS_ID' id='NEWS_ID' value='".Security::EncodeUrlData($this->news->news_id)."' />";
				}
				
				if(!$sentBack)
					$result .= "<div id='newsFields'>";

				$result .= "<div class='row'><div class='title'><:=News_Title=:></div><div><input type='text' name='news_title' id='news_title' value='".$this->news->news_name."' >".$this->formTag->RenderAsterisks($this->fieldsArray['news_title'])."</div></div>";
				$result .= "<div class='row'><div class='title'><:=News_Unique_Name_Identifier=:></div><div><input type='text' name='news_unique' id='news_unique' value='".$this->news->unique_name_identifier."' >".$this->formTag->RenderAsterisks($this->fieldsArray['news_unique'])."</div></div>";
				$result .= "<div class='row'><div class='title'><:=News_Publication_Time=:></div><div><input type='text' id='news_publication_time' name='news_publication_time' value='".date('Y-m-d H:i:s', $this->news->publication_time)."'>".$this->formTag->RenderAsterisks($this->fieldsArray['news_publication_time'])."</div></div>";
				$result .= "<div class='row' style='height:100px;'><div class='title'><:=News_Announce=:></div><div><textarea id='news_short_text' cols='37' rows='5'>".$this->news->news_short_text."</textarea></div></div>";
				$result .= "<div class='row' style='height:100px;'><div class='title'><:=News_Text=:></div><div><textarea id='news_text' cols='37' rows='5'>".$this->news->news_text."</textarea></div></div>";
				$result .= "<div class='row'><div class='title'><:=News_Public=:></div><div><input type='checkbox' name='news_is_public' id='news_is_public' value='on'></div></div>";
				$result .= "</div>";
				
				if(!$sentBack)
				{
					$link = new Anchor();
	
					$link->SCode = $this->SCodes['ajax_editnews'];
					$link->title = "<:=News_Add_Link=:>";
					$link->href = Request::$url;
					$link->hrefAJAX = Request::$url;
					$link->isTraditionalHref = false;
					$link->refreshElementId = 'newsFields';
					$link->class = 'ajaxLink';
					$link->getParamsValues = true;
					$link->applyScripts = false;

					$link->params = array("news_title" => "", "news_short_text" => "", "news_text" => "", "news_unique" => "", 'PROJECTS_ID' => '', 'NEWS_ID' => '', 
											"news_is_public" => "", "news_publication_time" => "", "sentBack" => "");

					$result .= "<div style='float:right;padding-right:5px;'>".$link->OpenTag()."<:=News_Add_Link=:>".$link->CloseTag()."</div>";

					$this->page->commonScripts .= $link->appendClientScript;
				}
					
				$this->blocksAjaxMarkup = $result;
			}
			elseif($this->isValidPost && $this->isValidForm)
			{
				$this->news->entities_id = $this->entitiesId;
				$this->news->objects_id = (float)Security::DecodeUrlData($_POST['PROJECTS_ID']);// Projects_Id
				if($this->news->objects_id > 0)
				{
					$this->news->objects_id = (float)$this->objectsId;
				}
				
				
				
				if($id = $this->news->SaveNews() > 0)
				{
					$this->page->commonScripts .= "Objects.BubbleDiv.hide();";
				}


				
				else
				{
					$this->page->commonScripts .= "alert('".$this->GetLocalValue('News_Something_Wrong_Error')."'); Objects.BubbleDiv.hide();";
				}
			}
			elseif($this->isValidPost && Request::GetSCode() == $this->SCodes['ajax_delnews'])
			{
				$this->news->DeleteNews();
				$this->page->commonScripts .= "Objects.BubbleDiv.hide();";
			}
			
			
			
			
			/*
			if($this->isValidPost && (Request::GetSCode() == $this->SCodes['ajax_addnews']))
			{
				$this->formTag = new Form();

				$this->blocksMarkup .= "<table>";

				$this->blocksMarkup .= "<tr><td><:=News_Title=:></td><td></td></tr>";
				$this->blocksMarkup .= "<tr><td><:=News_Unique_Identifier=:></td><td><input type='text' name='news_unique_identifier' id='news_unique_identifier' value=''></td></tr>";
				$this->blocksMarkup .= "<tr><td><:=News_Announce=:></td><td><textarea id='news_announce' name='news_announce' cols='25' rows='5'></textarea></td></tr>";
				$this->blocksMarkup .= "<tr><td><:=News_Text=:></td><td><textarea id='news_text' name='news_text' cols='25' rows='5'></textarea></td></tr>";
				$this->blocksMarkup .= "<tr><td><:=News_Language=:></td><td>language</td></tr>";

				$this->blocksMarkup .= "</table>";
			}
			*/

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

				$this->blocksMarkup .= "<tr><td><:=Materials_Comment=:></td><td><textarea name='materials_comment' cols='33' rows='5' ".($isReadOnly ? "readonly='readonly'" : "").">".$this->materials->comment."</textarea></td></tr>";
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

		public function BlockInit()
		{
			parent::BlockInit();

			$this->SwitchMode();
			
		}
	}
?>