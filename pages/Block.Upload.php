<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");


  include_once("CommonBlock.php");
  
  include_once("Block.Upload_Local.php");
  include_once("includes/common/Main.FileFunctions.php");
  
  include_once("includes/DatabaseClasses/Parts.UsersFiles.php");
    
  	/**
	 * class BlockUpload
	 *
	 * Блок завантаження файлів
	 *
	 * @package Blocks.pkg
	 */
	class BlockUpload extends CommonBlock
	{
		public $page = NULL;
		
		private $fieldsArray = array("project_step_name" => false, "sdate" => false, "edate" => false, 
									  "step_type_text" => false, "comment" => false);

		public $blocksMarkup = "";

		private $isValidForm = false;

		private $formTag = NULL;

		private $alwaysSave = true;

		public $needInitProgress = true;

		public $entityId = 0;
		public $albumsId = 0;

		public $BlockMode = RENDER_UPLOAD_FILE_BUTTON; 	// RENDER_UPLOAD_FORM 			- renders the upload form
														// RENDER_FILES_LIST  			- renders the files list	
																

		public function __construct()
		{
			$this->localizator = new BlockUpload_Local();

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
			return array (
					'save_file' => sha1("sAveFilEtoDatAbaSe") // SAVE UPLOADED FILE
						); 
		}

		/**
		 * 
		 */
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
				case RENDER_UPLOAD_FILE_BUTTON:
					$this->RenderUploadFile();
				break;
			}
		}

		public static function InitScripts($blockId = "", $needInit = true, $sCode = '', $params = NULL)
		{			
			if(strlen($sCode) == 0)
				$sCode = sha1("sAveFilEtoDatAbaSe");
		
			if($blockId == "")
			{
				$blockId = rand(1001,10001);
			}

			$clientParams = ' var params = { '.$params[$i];
			if($params != NULL && is_array($params))
			{ 
				$i = 0;
				foreach($params as $key => $value)
				{
					$clientParams .= $key.' : "'.$value.'" ';
					
					if(count($params) < $i-1)
					{
						$clientParams .= ", ";
					}
					$i++;
				}
			}
			$clientParams .= " }; ";

			$commonHead  = "<script type='text/javascript' src='/scripts/swfupload/swfupload.js'></script>";
			$commonHead .= '<script type="text/javascript">
				var upload1, upload2;
				
				function uploadStart(file)
				{
					var params = {"PHPSESSID": "'.session_id().'", 
								  "__SVar" : Objects.Security.secureServerVar, "__ClientVar" : Objects.Security.createSecureVar(), 
								  "__SCode" : "'.$sCode.'", "IS_AJAX" : "TRUE" };

					if(Objects.UploadParams != null)
					{
						for(var param in Objects.UploadParams)
						{ 
							if(params[param] == null)
							{
								params[param] = Objects.UploadParams[param];
							}
						}
					}
					else
					{
						params["PROJECTS_ID"] = Objects.Environment[\'PROJECTS_ID\'];
					}
					
					//alert(Objects.UploadParams["ALBUMS_ID"]);
										
					Objects.Upload.setPostParams(params);
				}
				
				function uploadError(fileobject, errorcode, message)
				{
				}
				
				function uploadProgress(fileobject, bytescomplete, totalbytes)
				{
					$("uploadProgressId").style.width = (100* bytescomplete/totalbytes) + "px";
				}
								
				function uploadSuccess(file, serverData) 
				{
					try {
						alert(serverData);

						object = serverData.evalJSON(true);
						object = Objects.Security.validateResponse(object);
												
						if(object.jsonObject != null && object.jsonObject.Status == 1)
						{
							//$("__SVar").setValue(object.SVar);
							//alert("OK_OK! - "+$("__SVar").getValue()+serverData.length+" "+ serverData);
						}
					} catch (ex) 
					{
						Objects.Upload.debug(ex);
					}
				}

				function uploadComplete(file) {	
					
					var params = {"PHPSESSID" : "'.session_id().'", 
								  "__SVar"    : Objects.Security.secureServerVar, "__ClientVar" : Objects.Security.createSecureVar(), 
								  "__SCode"   : "'.sha1("sAveFilEtoDatAbaSe").'", "IS_AJAX"     : "TRUE", "PROJECTS_ID" : Objects.Environment[\'PROJECTS_ID\']}

					try {
						if (this.getStats().files_queued === 0) 
						{
							// DISABLE CANCEL UPLOAD
							//document.getElementById(Objects.Upload.customSettings.cancelButtonId).disabled = true;
						} else 
						{	
							Objects.Upload.setPostParams(params);						
						}
					} catch (ex) {
						this.debug(ex);
					}
				}
				
				function fileDialogComplete(numFilesSelected, numFilesQueued)
				{
					try 
					{
						if(numFilesQueued > 0)
						{
							Objects.Upload.startUpload();
						}
					}
					catch(ex)
					{
						Objects.Upload.debug(ex);
					}
				}
				
				function InitUpload(params)
				{				
					if(Objects.Upload != null)
					{
						Objects.Upload.destroy();
					}
					
					var postParams = { "PHPSESSID"	: "'.session_id().'", 
									  "__SVar" 		: Objects.Security.secureServerVar, 
									  "__SCode" 	: "'.sha1("sAveFilEtoDatAbaSe").'", 
									  "__ClientVar" : Objects.Security.createSecureVar(), 
									  "IS_AJAX" 	: "TRUE" };
					
					if(params != null)
					{
						for(var param in params)
						{ 
							postParams[param] = params[param];
						}
					}
					else
					{
						postParams["PROJECTS_ID"] = Objects.Environment[\'PROJECTS_ID\'];
					}
					
					Objects.UploadParams = postParams;
					
					Objects.Upload = new SWFUpload({
						upload_url: "'.Request::$url.'",
							
						file_dialog_complete_handler : fileDialogComplete,
						upload_start_handler : uploadStart,
						upload_complete_handler : uploadComplete,
						upload_success_handler : uploadSuccess,
						upload_error_handler : uploadError,
						upload_progress_handler : uploadProgress,
						
						file_size_limit : "2000 KB", 
							
						post_params : Objects.UploadParams,

						// Button Settings
						button_image_url : "'.Request::GetRoot().'/scripts/swfupload/XPButtonUploadText_61x22.png",
						button_placeholder_id : "spanButtonPlaceholder_'.$blockId.'",
						button_width: "61",
						button_height: "22",
						
							
						// Flash Settings
						flash_url : "'.Request::GetRoot().'/scripts/swfupload/swfupload.swf"
					}); 
				}
			';

			if($needInit)
				$commonHead .= ' Objects.OnLoadScripts[0] = \''.$clientParams.';  InitUpload(params); Objects.Environment["UploadButtonId"] = "SWFButton"; \'  '; 
				
			$commonHead .= '</script>';

			return $commonHead;
		}

		public function RenderUploadFile()
		{
			if(Session::GetUserId() > 0 && !$this->page->isValidPost || (Request::GetSCode() != $this->SCodes['save_file']) )
			{
				$res = "";
				
				$params = array();
				$params['ALBUMS_ID'] = Security::EncodeUrlData($this->albumsId);

				if(!$this->isAjaxRequest)
				{
					$this->page->commonHead .= self::InitScripts($this->blockId, true, '', $params)." "; 
				}
				else
				{
					$this->page->commonScripts .= " InitUpload(params);  ";
				}
				
				$res .= "<div style='width:300px'>";
				
				if($this->needInitProgress)
				{
					$res .= "<div id='uploadProgress' style='border:#CCCCCC solid 1px; display: inline-block; width: 100px; height:15px; '><div id='uploadProgressId' style='background-color:#3300FF; border:#3300FF solid 1px;width:0px; height:13px;' ></div></div>";
				}

				$res .= "<div id='SWFButton' style='position:absolute; margin-left: 107px; float:left; margin-top:-23px; * margin-top: -19px; '><div id='spanButtonPlaceholder_".$this->blockId."' style='padding-left: 15px;'></div></div></div>";

				if($this->isAjaxRequest)
				{
					$this->blocksAjaxMarkup = $res;
				}
				else
				{
					$this->blocksMarkup = $res;
				}
			}
			elseif(Session::GetUserId() > 0 && $this->page->isValidPost && Request::GetSCode() == $this->SCodes['save_file'])
			{
				if($_POST['ACTION'] != "DEL" && !(!isset($_FILES["Filedata"]) || 
					!is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0))
				{

					$fileFunctions = new FileFunctions();
					$fileFunctions->fileName = $_FILES["Filedata"]["tmp_name"];
					$fileFunctions->ValidateFile();

					$result->fileName = /*StaticDatabase::CleanupTheField(*/$_FILES["Filedata"]["name"]/*)*/;
					$result->fileSize = (float)$fileFunctions->fileSize;

					if($fileFunctions->fileSize > 0)
					{
						$file = new UsersFiles();

						$result->Status = 1; // Ok!
						
						$file->filename = $result->fileName;
						$file->filesize = $result->fileSize;
						
						$file->filedata = $fileFunctions->fileData; // should be base64encode later
						$file->filetype = $fileFunctions->mimeType;
						
						$file->width = $result->width = (float)$fileFunctions->iWidth; 
						$file->height = $result->height = (float)$fileFunctions->iHeight;

						$file->entities_id = $this->entityId;
						$file->albums_id = $this->albumsId;

						$result->files_id = (float) $file->CreateFile();
					}
					else
					{
						$result->Status = 0; // Error!
					}
					
					$this->page->pagesAjaxObject = $result; 
				}
				elseif($_POST['ACTION'] == 'DEL' && ($fileId = (float)Security::DecodeUrlData($_POST['FILES_ID'])) > 0 )
				{
					$file = new UsersFiles();

					if($file->DeleteFile($fileId))
					{
						$this->page->commonScript = "";
					}
					
				}
				else
				{
					$result->Status = 0;
					$this->page->pagesMarkup = "Bad file";
					$this->page->pagesAjaxObject = $result;
				}				
			}
			elseif(Session::GetUserId() < 1)
			{
				$result->Status = 0;
				$this->page->pagesMarkup = "Not authorized";
				$this->page->pagesAjaxObject = $result;
			}

		}

		public function BlockInit()
		{
			parent::BlockInit();

			$this->SwitchMode();
		}
	}

?>