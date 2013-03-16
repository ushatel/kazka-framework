<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

  include_once("CommonBlock.php");
  include_once("Block.Album_Local.php");
  include_once("Block.Upload.php");
  include_once("includes/common/Main.FileFunctions.php");
  include_once("includes/DatabaseClasses/Parts.UsersFiles.php");

  	/**
	 * class BlockAlbum
	 *
	 * Блок керування альбомом
	 *
	 * @package Blocks.pkg
	 */
	class BlockAlbum extends CommonBlock
	{
		public $page = NULL;
		
		private $fieldsArray = array("project_step_name" => false);

		public $blocksMarkup = "";

		private $isValidForm = false;

		private $formTag = NULL;
		
		private $alwaysSave = true;
		
		public $showAlbums = true;
		
		public $isReadOnly = false;
				
		public $selectedPrjId = 0;
		public $selectedStepsId = 0;
		
		public $selectedEntityId = 0;
		public $selectedObjectsId = 0;
		public $selectedObjectsTitle = "";
		
		public $albumsHeight = 300;
		public $albumsTitleWidth = 0;
		public $albumsWidth = 0;
		
		public $nailWidth = 50;
		public $nailHeight = 50;
		
		public $display = "";

		public $partsProjectTblObj = NULL;

		public $BlockMode = RENDER_LINEAR_ALBUM;		// RENDER_LINEAR_ALBUM 			- renders the upload form
														// RENDER_FILES_LIST  			- renders the files list			
														// RENDER_COMMON_ALBUM			- renders the common album
														

		public function __construct()
		{
			$this->localizator = new BlockAlbum_Local();

			parent::__construct();
			
			$this->SCodes = self::GetAllowedSCodes();
			
			if($this->linkForListing == "")
			{
				$this->linkForListing = Request::GetRoot()."/search/";
			}
			
			parent::__construct();
		}
		
		public static function GetAllowedSCodes()
		{
			$array_codes = array (
					'save_file' => sha1("sAveFilEtoDatAbaSe"), 'ajax_add_album' => sha1("aJaXaDdAlBum"), 'ajax_album_click' => sha1("aJaXCliCkAlbUm"), 'ajax_album_refresh' => sha1("aJaXalBuMrEfResH") // SAVE UPLOADED FILE
						);
			
			return $array_codes; 
		}

		/**
		 * Перевірка форми */
		private function ValidateFirstForm()
		{
			$this->isValidForm = false;
			
			if(Request::GetSCode() != sha1("eDitOnePrOjeCt") && Request::GetSCode() != sha1("CreAtePrjFirStSteP"))
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
				case RENDER_LINEAR_ALBUM:
					$this->RenderLinearAlbum();
				break;
				
				case RENDER_BANNERS:
					$this->RenderBannersList();
				break;
				
				case RENDER_COMMON_ALBUM:
					$this->RenderMultilevelAlbum();
				break;
			}
		}
		
		public function RenderBannersList()
		{
			$this->RenderUploadButton(true, 12);
		}
			
		private function RenderSinglePhoto($row)
		{		
			$title = (strlen($row['TITLE']) > 0) ? $row['TITLE'] : $row['FILENAME'];
			$path = Request::GetRoot()."/image/".$row['USERS_FILES_ID'];//.".".preg_replace("/image\//i", '', $row['FILETYPE']);

			
			$result = "<div class='singlePhoto'><span><p style='margin-left: ".($this->nailWidth-10)."px; ' onClick='fDel(this);' fileId='".Security::EncodeUrlData($row['USERS_FILES_ID'])."' title='delete'>X</p>";
			
			
			if( Request::$browser != "empty")
			{
				$result .= "<a href='{$path}' onClick='return false;'><IMG src='/images/spacer.gif' src2='{$path}' width='".$this->nailWidth."px' height='".$this->nailHeight."px' onClick='Objects.Album.show(); Objects.Album.imgObjClick(this); ' title='".$title."' /></a>"; //SImageClick(this);
			}
			elseif(Request::$browser == "empty")
			{
				$result .= "<a href='{$path}' target='_blank'><IMG src='{$path}' width='".$this->nailWidth."px' height='".$this->nailHeight."px' onClick='Objects.Album.show(); Objects.Album.imgObjClick(this); ' title='".$title."' /></a>"; //SImageClick(this);
			}
			
			$result .= "</span></div>";

			if(!$this->isAjaxRequest)
			{
				$this->blocksMarkup .= $result;
			}
			else
			{
				$this->blocksAjaxMarkup .= $result;
			}
		}
		
		private function RenderPhotosForAlbum($albumId = -1)
		{
			$this->blocksMarkup .= "<div style='padding-left:5px;display:inline-block;' ><div>";

			if($albumId > 0)
			{
				$photos = new UsersFiles();
				$photos->albums_id = $albumId;
				$result = $photos->LoadFiles();

				if(!$this->isAjaxRequest)
				{
					$this->page->commonScripts .= "</script><script>function fDel(obj) { if(confirm(\"".$this->GetLocalValue('Album_Confirm_Delete')."\")) {  fClick(obj, 1); } }</script>";
					//$this->page->commonScripts .= "</script><script>Objects.OnLoadScripts[1] = \"var elem = $('albums_block'); alert(elem.offsetWidth); alert($('albums_data').offsetWidth); \"</script>";

					$this->RenderUploadButton(false, $entityId, $albumId, $buttonId = "simpleButton");

					$aWidth = '';
					if($this->albumsTitleWidth < 1)
					{
						$this->albumsTitleWidth = 250;
					}

					if(($this->albumsWidth - $this->albumsTitleWidth) > 0)
					{
						$aWidth = ($this->albumsWidth - $this->albumsTitleWidth - 15);
					}
					$aWidth .= "px";

					$this->blocksMarkup .= "</div><div id='albumsPhoto' style='height:".($this->albumsHeight - 30)."px; width: ".($aWidth)."' >";
				}

				if($result != NULL && mysql_num_rows($result) > 0)
				{
					while($row = mysql_fetch_array($result))
					{
						$this->RenderSinglePhoto($row); 
					}
				}
				
				if(Request::$browser != "empty"/* Enumerator::$browser['fox'] || preg_match("/MSIE 9.0/i", Request::$agent)*/)
				{
					if(!$this->isAjaxRequest)
						$this->page->commonScripts .= '<script>	Objects.OnLoadScripts[1] = \' Objects.Album = new Controls.Album(); Objects.Album.preLoad(); \'; </script>';
					else
					{ // Init photos
						$this->page->commonScripts .= '<script> Objects.Album = new Controls.Album(); Objects.Album.preLoad(); </script>';		
					}
				}
				/**/
			}

			if(!$this->isAjaxRequest)
				$this->blocksMarkup .= "</div></div>";

			return $result;
		}

		public function RenderAlbums($result)
		{
			$uaId = -1;
			if($result != NULL)
			{
				while($row = mysql_fetch_array($result))
				{
					if((float)$uaId < 1)
					{
						$uaId = (float)$row['USERS_ALBUMS_ID'];
						$this->RenderAlbumName($row['TITLE'], (float)$row['USERS_ALBUMS_ID'], true);
					}
					else
					{
						$this->RenderAlbumName($row['TITLE'], (float)$row['USERS_ALBUMS_ID'], false);
					}
				}
			}
			
			return $uaId;
		}
		
		public function RenderMultilevelAlbum()
		{
			if(!$this->isAjaxRequest && !$this->isValidPost)
			{

				$this->blocksMarkup =  "<div style='width: ".($this->albumsWidth > 0 ? $this->albumsWidth."px" : '99%')."; height:".$this->albumsHeight."px; display:".$this->display.";' id='albums_block' >";

				$usersFiles = new UsersFiles();
				$usersFiles->album_objects_id = (float)$this->selectedObjectsId;
				$usersFiles->album_entities_id = (float)$this->selectedEntityId;
				$result = $usersFiles->LoadAlbums();

				$uaId = -1;				
				if($this->showAlbums)
				{	
					$anchor = new Anchor();
					$anchor->id = "m_add_button";
					$anchor->tagAttributes['style'] = "";
					$anchor->SCode = $this->SCodes['ajax_add_album'];
					$anchor->title = "<:=Album_Add_New=:>";
					$anchor->class = "ajaxLink";
					$anchor->href = Request::$url;
					$anchor->hrefAJAX = Request::$url;
					$anchor->isTraditionalHref = false;
					$anchor->refreshElementId = "albums_list";
					$anchor->getParamsValues = true;
					$anchor->params = array('album_name' => '', 'OBJECTS_ID' => '');
						
					$objectsId = Security::EncodeUrlData($this->selectedObjectsId);
					
					$this->blocksMarkup .= "<div id='albums_data' ".($this->albumsTitleWidth > 0 ? "style='width:".$this->albumsTitleWidth."px;'" : '').">";

					if(!$this->isReadOnly)
						$this->blocksMarkup .= "<p style='font-size:15px; padding-bottom:3px; padding-top:2px; '><input type='hidden' name='OBJECTS_ID' id='OBJECTS_ID' value='{$objectsId}' /><input type='text' name='album_name' id='album_name' value='<:=Album_Name=:>' style='width:100px;' />&nbsp;".$anchor->OpenTag()."<:=Album_Add_New=:>".$anchor->CloseTag()."</p>";

					$this->page->commonScripts .= $anchor->appendClientScript;
					$this->page->commonScripts .= "</script><script> ".BlockAlbum::InitScripts(ALL)." </script>";

					$this->blocksMarkup .= "<div id='albums_list'>";
					$uaId = $this->RenderAlbums($result);

					$this->blocksMarkup .= "</div></div>"; 

				}
				elseif(!$this->showAlbums && ($result == NULL || mysql_num_rows($result) == 0))
				{
					// 
					$userFiles = new UsersFiles();
					$userFiles->album_title = substr($this->selectedObjectsTitle, 0, 200); // create the album with the same name as object
					$userFiles->album_entities_id = (float)$this->selectedEntityId;
					$userFiles->album_objects_id = (float)$this->selectedObjectsId; 
				
					if(strlen($userFiles->album_title) < 1)
					{
						$userFiles->album_title = Enumerator::Entity($this->selectedEntityId);
					}
					
					$this->page->commonScripts .= "</script><script> ".BlockAlbum::InitScripts(IMAGE)." </script>";

					$uaId = (float)$userFiles->SaveAlbum(); 
				}
				elseif($result != NULL && mysql_num_rows($result) > 0)
				{ 
					$row = mysql_fetch_array($result);
					$uaId = $row['USERS_ALBUMS_ID'];
					
					$this->page->commonScripts .= "</script><script> ".BlockAlbum::InitScripts(IMAGE)." </script>";
				}
				
				$this->RenderPhotosForAlbum($uaId); 

				$this->page->commonScripts .= "</script><script> Objects.Environment['Album_Confirm_Album_Delete']='".$this->GetLocalValue('Album_Confirm_Album_Delete')."'; Objects.UploadParams['ALBUMS_ID'] = '".Security::EncodeUrlData($uaId)."'; Objects.UploadParams['OBJECTS_ID'] = '".Security::EncodeUrlData($this->selectedObjectsId)."'; </script>";

				$this->blocksMarkup .= "</div>";
			}
			elseif($this->isValidPost && Request::GetSCode() == sha1("aJaXalBuMrEfResH"))
			{
				$usersFiles = new UsersFiles();
				$usersFiles->album_objects_id = (float)Security::DecodeUrlData($_POST['OBJECTS_ID']);
				$usersFiles->album_entities_id = (float)$this->selectedEntityId;
				$result = $usersFiles->LoadAlbums();
				
				$this->RenderAlbums($result); //$this->blocksAjaxMarkup = 'fdfddsffsd';
			}
			elseif($this->isValidPost && Request::GetSCode() == $this->SCodes['ajax_album_click'])
			{
				if($_POST["ACTION"] == "DEL")
				{
					$usersFiles = new UsersFiles();
					$usersFiles->users_album_id = (float)Security::DecodeUrlData($_POST['ALBUMS_ID']);
					//$usersFiles->album_entities_id = (float)$this->selectedEntityId;
					$result = $usersFiles->DeleteAlbum();
					
				}
				else
					$this->RenderPhotosForAlbum((float)Security::DecodeUrlData($_POST['ALBUMS_ID']));

			}
			elseif($this->isValidPost && Request::GetSCode() == $this->SCodes['ajax_add_album'])
			{
				$userFiles = new UsersFiles();
				$userFiles->album_title = substr(StaticDatabase::CleanupTheField($_POST['album_name']), 0, 200);
				$userFiles->album_entities_id = (float)$this->selectedEntityId;
				$userFiles->album_objects_id = (float)Security::DecodeUrlData($_POST['OBJECTS_ID']);; 
				
				$album_id = (float)$userFiles->SaveAlbum(); 
				
				$usersFiles = new UsersFiles();
				$usersFiles->album_objects_id = (float)Security::DecodeUrlData($_POST['OBJECTS_ID']);
				$usersFiles->album_entities_id = (float)$this->selectedEntityId;
				$result = $usersFiles->LoadAlbums();
				
				$this->RenderAlbums($result); //$this->blocksAjaxMarkup = 'fdfddsffsd';
				
			}
			elseif($this->isValidPost && Request::GetSCode() == sha1("sAveFilEtoDatAbaSe"))
			{
				$this->RenderUploadButton(false, 6, (float)Security::DecodeUrlData($_POST["ALBUMS_ID"]));
			}
			else {	}
			
		}
		
		private function RenderAlbumName($title = '', $albums_id = 0, $selected = false)
		{
			$res = "<div onClick='AlbumClick(this); ' albumsId='".Security::EncodeUrlData($albums_id)."' ".($selected ? 'class="selectedDiv"' : '')."><p class='del' onClick='aClick(this.parentNode, 1); Event.stop(event);' title='delete' ".($this->albumsTitleWidth > 0 ? "style='margin-left:".($this->albumsTitleWidth-15)."px;'" : '')." >×</p><p class='edit' onClick='aClick(this);' title='edit' ".($this->albumsTitleWidth > 0 ? "style='margin-left:".($this->albumsTitleWidth-17)."px'" : '').">…</p><nobr title='{$title}'>".$title."</nobr></div>";

			if(!$this->isAjaxRequest)
				$this->blocksMarkup .= $res;
			else
				$this->blocksAjaxMarkup .= $res;
		}

		public function RenderImage($row)
		{
			$title = (strlen($row['TITLE']) > 0) ? $row['TITLE'] : $row['FILENAME'];
			$res .= "<a onClick='SImageClick(this.firstChild);'><IMG src='".Request::GetRoot()."/image/".(float)$row['USERS_FILES_ID']."/".$row['FILENAME']."'  width='55px' height='55px' onClick='SImageClick(this);' title='".$title."' /></a>&nbsp;";

			return $res;
		}
		
		public function RenderLinearAlbum()
		{
			$this->RenderUploadButton();

			$scode = $this->SCodes['save_file'];

			if(Request::GetSCode() != $scode)
			{
				$this->userFilesTblObj = new UsersFiles();
				$this->userFilesTblObj->projects_id = (float)Security::DecodeUrlData($_POST['PROJECTS_ID']);//$this->selectedPrjId ;
				$this->userFilesTblObj->projects_steps_id = $this->selectedStepsId;

				$result = $this->userFilesTblObj->GetProjectsFiles();

				if($result != NULL)
				{
					$res = "<div>";

					while($row = mysql_fetch_array($result))
					{
						//Render nails
						//$res .= "<IMG src='".Request::GetRoot()."/image/".(float)$row['USERS_FILES_ID']."/".$row['FILENAME']."'  width='55px' height='55px' onClick='SImageClick(this);'  />&nbsp;";	//width='".(float)$row['WIDTH']."' height='".(float)$row['HEIGHT']."'
						$this->RenderImage($row);
					}
	
					$res .= "</div>";

					if($this->isAjaxRequest)
					{
						$this->blocksAjaxMarkup .= $res;
					}
					else
					{
						$this->blocksMarkup .= $res;
					}
				}

			}
			elseif( Request::GetSCode() == $scode && $this->page->pagesAjaxObject->Status == 1 )
			{
				if($this->page->pagesAjaxObject->files_id)
				{
					$userFiles = new UsersFiles();

					$userFiles->projects_id = (float)Security::DecodeUrlData($_POST['PROJECTS_ID']);//(float)$this->selectedPrjId;
					$userFiles->projects_steps_id = (float)Security::DecodeUrlData($_POST['PROJECTS_STEPS_ID']);
					$userFiles->projects_file_ispublic = false;
					$userFiles->projects_users_files_id = (float)$this->page->pagesAjaxObject->files_id;
					
					$userFiles->width = (float) $this->page->pagesAjaxObject->width;
					$userFiles->height = (float) $this->page->pagesAjaxObject->height;

					$userFiles->CreateProjectsFile();					
				}
			}
		}
		
		/*  
		 * Ініціалізувати скріпти
		 */
		public static function InitScripts($status = ALL)
		{
			$result = '';
			if($status == ALL || $status == IMAGE)
				$result = ' 
				
				function AlbumRefresh(objId)
				{

					var parameters = { 
						__SVar 			: Objects.Security.secureServerVar,
						__ClientVar 	: Objects.Security.createSecureVar(),
						IS_AJAX			: "TRUE",
						OBJECTS_ID		: objId
					}
					
					parameters["__SCode"] = "'.sha1("aJaXalBuMrEfResH").'";
					
					Objects.Environment["ALBUMS_SELECTED"] = null;

					var rq = new Ajax.Request(document.location.href, {

						parameters : parameters,

						onSuccess: function(response) 
						{
							try 
							{	 
								var rObject = Objects.Security.validateResponse(response.responseJSON);

								if(rObject != null && rObject.isSecured)
								{
									$("albums_list").update(rObject.text);
									$("albums_block").style.display = "";
									
									Objects.Environment["ALBUMS_SELECTED"] = $("albums_list").firstChild;
									if(Objects.Environment["ALBUMS_SELECTED"] != null)
									{
										AlbumClick(Objects.Environment["ALBUMS_SELECTED"]);
									}
								
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
				
				function aClick(obj, action)
				{
					///Security::EncodeUrlData((float)$this->selectedPrjId)AjaxStepsClick
					
					if(action == 1 )
					{
						if(confirm(Objects.Environment["Album_Confirm_Album_Delete"]))
						{ 	
							AlbumClick(obj, 1);
						}
					}
				
				}
				
				function fClick(obj, action)
				{		
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
							__SCode 	: "'.sha1("sAveFilEtoDatAbaSe").'", 
							__ClientVar : Objects.Security.createSecureVar(),
							FILES_ID	: $(obj).readAttribute("fileId"),
							IS_AJAX 	: "TRUE"
						}
						
						if(action == 1)
						{
							parameters["ACTION"] = "DEL";
						}
						
						var rq = new Ajax.Request(document.location.href, {
		
							parameters : parameters,
						
							onCreate: function ()
							{
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
									
									/*
										if(action == 1)
										{
											var image = obj.parentNode.parentNode;

											if(Prototype.Browser.IE)
												image.outerHTML = "";
											else
												image.remove();
										}

										if(rObject.scripts.length > 0)
											eval(rObject.scripts[0]); 
									*/
									
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
				}';

			if($status == ALL)
			{
				$result .= '
				function AlbumClick(elem, action) { 

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
							__SCode 	: "'.sha1("aJaXCliCkAlbUm").'", 
							__ClientVar : Objects.Security.createSecureVar(),
							ALBUMS_ID	: $(elem).readAttribute("albumsId"),
							OBJECTS_ID	: $("OBJECTS_ID").value,
							IS_AJAX 	: "TRUE"
						}

						if(action == 1)
						{
							parameters["ACTION"] = "DEL";
						}
						
						Objects.UploadParams["ALBUMS_ID"] = $(elem).readAttribute("albumsId");

						var rq = new Ajax.Request(document.location.href, {

							parameters : parameters,

							onCreate: function ()
							{
								var albums = $$("#albums_data div"); 

								for(i = 0; i < albums.length; i++) { albums[i].className=""; }; 

								$(elem).className = "selectedDiv";											
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
										$("albumsPhoto").innerHTML = rObject.text;

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
					}';
			}
			
			return $result;
		}
		
		public function RenderUploadButton($needInitScripts = false, $entityId = 0, $albumsId = 0, $buttonId = "simpleButton", $params = "  ")
		{
			if(Session::GetUserId() < 1)
			{
				return;

			}
		
			$resTag = "<:=UPLOAD_CONTROL=:>";
			if(!$this->isAjaxRequest)
			{
				$this->blocksMarkup .= $resTag;
			}
			


			else
			{
				$this->blocksAjaxMarkup .= $resTag;
			}
	
			$block = new BlockUpload();
			$block->page = $this->page;
			$block->blockId   = "UPLOAD_CONTROL";
			$block->BlockMode = RENDER_UPLOAD_FILE_BUTTON;
			$block->blockName = "Render upload file button";
			$block->entityId = (float)$entityId;
			$block->albumsId = (float)$albumsId;
			$block->needInitProgress = true;
			$block->BlockInit();
			
			if($needInitScripts)
			{
				if(!$this->isAjaxRequest)
				{
					$params['ALBUMS_ID'] = Security::EncodeUrlData((float)$albumsId);
					$this->page->commonScripts .= BlockUpload::InitScripts($buttonId, true, '', $params);
				}
				
				$block->page->commonScripts .= " <script> InitUpload(); </script>";
			}
			
			$this->blocks['UPLOAD_CONTROL'] = $block;
			

			return $resTag;
		}

		public function BlockInit()
		{
			parent::BlockInit();
			
			$this->SwitchMode();
		}
	}

?>