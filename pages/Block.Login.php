<?php

  if (@preg_match("/pages/i", $_SERVER['PHP_SELF'])) 
    die ("You can't access this file directly...");

	include_once("CommonBlock.php");
	include_once("Block.Login_Local.php");
	include_once("Block.Album.php");

	include_once("includes/common/Lib.php");

	include_once("includes/DatabaseClasses/Parts.Users.php");

	/**
	 * class Login
	 *
	 * ץ顪족񠱲ﰳ 𥣨񲰠򳿠冷 믰鲲󢠷஍
	 *
	 * @package Blocks.pkg
	 */
	
	class Login extends CommonBlock
	{
		public $page = NULL;
		
		private $fields_array = array("login" => false, 'password_new_field' => false, 'password_field' => false);
	
		public $blocksMarkup = "";
		
		private $isValidForm = false;
		
		private $users = NULL;

		public $BlockMode = LOGIN_FORM ;  // CHANGE_PASSWORD = renders the form to change password,
										  // LOGIN_FORM = renders the user name or the login form if not in
										  // PROFILE_FIELDS = renders the user profile fields 
										  // LOST_LOGIN = renders the lost user password

		public function __construct()
		{
			$this->localizator = new Login_Local();

			$this->users = new Users();
		}

		public function ValidateForm()
		{
			$this->valuesArray["login_field"] = StaticDatabase::CleanupTheField($_POST['login_field']);
			$this->valuesArray["password_field"] = StaticDatabase::CleanupTheField($_POST['password_field']);

			if( preg_match("/Lost/i",$this->valuesArray["login_field"]) || preg_match("/Login/i", $this->valuesArray["login_field"]) || 
				!$this->users->ValidateUser($this->valuesArray["login_field"], $this->valuesArray["password_field"]))
			{	
				$this->fields_array["login"] = "<:=Validation_Login_IsNotFound_Error=:>";
				$this->isValidForm = false;
				
				Session::IncErrorCount();
			}
			else 
			{
				$this->isValidForm = true;
			}

			$this->users->login = StaticDatabase::CleanupTheField($_POST['login_field']);
			$this->users->password = StaticDatabase::CleanupTheField($_POST['password_field']);


			$this->users->public_email = StaticDatabase::CleanupTheField($_POST['public_email_field']);
			$len = strlen($this->users->public_email);

			if($len > 0 && ( $len < 7 || !preg_match("/@/", $this->users->public_email)) )
			{
				$this->fieldsArray['public_email_field'] = "<:=Validation_Email_Error=:>";
				$this->isValidForm = false;
			}
			else
			{
				$this->isValidForm = $this->isValidForm & true;
			}


			$this->users->description = substr(StaticDatabase::CleanupTheField($_POST['description_field']), 0, 500);
			$this->users->phone = StaticDatabase::CleanupTheField($_POST['phone_field']);
			$this->users->phone2 = StaticDatabase::CleanupTheField($_POST['phone2_field']);
			$this->users->companiesId = (float)StaticDatabase::CleanupTheField($_POST['companiesId']);

			return $this->isValidForm;
		}
		
		private function ValidateLostForm()
		{
			$this->isValidForm = false;
			
			if(Request::GetSCode() == sha1("pRocEsSTheLoGinForM"))
			{
				$this->isValidForm = true;
			}

			$this->users->login = substr(StaticDatabase::CleanupTheField($_POST['lost_login']), 0, 200);

			if(!$this->users->LoadUserByLogin($this->users->login))
			{
				$this->fields_array["lost_login"] = "<:=Validation_Lost_Login_Is_Not_Found=:>";
				$this->isValidForm = false;
			}
			else
			{
				$this->isValidForm = $this->isValidForm & true;
			}
			
			return $this->isValidForm;
		}

		private function ValidatePasswordForm()
		{
			$this->isValidForm = false;

			if(Request::GetSCode() != sha1("ProCeSsPaSsWordReSet"))
			{
				$this->isValidForm = false;
				return $this->isValidForm;
			}

			$this->users = new Users();

			if(strlen($this->login) < 1)
			{
				$this->users->login = Session::GetUserLogin();
				$this->isValidForm = true;
			}
			elseif($this->users->LoadUserByLogin($this->login))
			{
				$this->users->login = $this->login;
				$this->isValidForm = true;
			}
			
			$this->users->password = StaticDatabase::CleanupTheField($_POST['password_field']);

			if(!$this->users->ValidateUser($this->users->login, $this->users->password))
			{
				$this->fields_array['password_field'] = "<:=Validation_Incorrect_Password=:>";
				$this->isValidForm = false;

				Session::IncErrorCount();
			}
			else
			{
				$this->isValidForm & true;
			}			

			$newPass = (strlen($_POST['password_new_field']) > 0 && StaticDatabase::CleanupTheField($_POST['password_new_field']) == StaticDatabase::CleanupTheField($_POST['password_confirm_field']));
			if(!$newPass)
			{
				$this->isValidForm = false;
				$this->fields_array['password_new_field'] = "<:=Validation_Incorrect_New_Password=:>";
			}
			else 
			{
				$this->users->password = $newPass;
				$this->isValidForm & true;
			}

			return $this->isValidForm;
		}

		private function SwitchStep()
		{
			switch($this->BlockMode)
			{
				case CHANGE_PASSWORD:
					$this->RenderPassword();
				break;

				case PROFILE_FIELDS:
					$this->RenderProfileFields();
				break;

				case LOGIN_LOST:
					$this->RenderLost();
				break;

				case LOGIN_FORM:
				default:
					$this->RenderLogin();
				break;

				case LOGIN_BUBBLE:
					$this->RenderLoginBubble();
				break;
			}
		}
		
		public function RegistrationLetter($mail2 = '', $usr = null, $action = 0)
		{
			$mailer = new Mailer();
			$mailer->to = $usr->email;
			if(strlen($mail2) > 0)
			{
				$mailer->to .= ",".$mail2;
			}

			$mailer->subject = $this->GetLocalValue('Registration_Letter_Subject');

			// Send letter
			$slink = '<a href=" '.Request::GetRoot().'/User/Registration?__SCode=3D'.(($action > 0) ? sha1("ProCeSsPaSsWordReSet") : sha1("ProCeSsCoNfIrMatIonReGisTraTiOnCoDe")).'&__SCValue=3D'.$usr->GetConfirmationSecurityCode().' ">'.( $action > 0 ? $this->GetLocalValue('Submit_Lost_Password') : $this->GetLocalValue('Submit_Post_Registration')).'</a>';

			$body = '<div style=3D"padding:14px;margin-bottom:4px;-moz-border-radius:5px;-webkit-border-radius:5px;border-radius:5px;" ><br /><img src=3D"http://www.myxata.com/images/mid_top.png" height=3D"65" width=3D"700" /><br />'.sprintf($this->GetLocalValue('Registration_Letter_Body'), $usr->login, $slink).'</div>';
			$mailer->SendHtmlLetter($body);
		}

		private function RenderPassword()
		{
			$this->users = new Users();
			$this->login = (((int)$_POST['sent'] > 0) ? StaticDatabase::CleanupTheField($_POST['second_login']) : $this->login );
			if(!$this->page->isValidPost || $this->page->isValidPost && (Session::GetUserId() > 0 || strlen($this->login) > 0) && !$this->ValidatePasswordForm())
			{
				$formTag = new Form();

				if(!Request::$IsAJAX)
					$this->blocksMarkup .= "<table id='userFields' style='margin-bottom:0px; padding: 10px;'>";

				$this->blocksMarkup .= "<input type='hidden' name='sent' id='sent' value='1'>";
				$this->blocksMarkup .= "<input type='hidden' name='second_login' id='second_login' value='".$this->users->login."'>";
				$this->blocksMarkup .= "<tr><td><:=Old_Password=:></td><td><nobr><input type='password' class='-metrika-nokeys' id='password_field' name='password_field' title='<:=Old_Password=:>' />".$formTag->RenderAsterisks($this->fields_array["password_field"])."</nobr></td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Password=:></td><td><nobr><input type='password' class='-metrika-nokeys'     id='password_new_field' name='password_new_field' title='<:=Password=:>' />".$formTag->RenderAsterisks($this->fields_array['password_new_field'])."</nobr></td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Password_Confirm=:></td><td><input type='password' class='-metrika-nokeys'   id='password_confirm_field' name='password_confirm_field' title='<:=Password_Confirm=:>'></td></tr>";

				if(!Request::$IsAJAX)
				{
					$link = new Anchor();
					$link->SCode = sha1("ProCeSsPaSsWordReSet");//sha1("ChaNgEpaSswoRdCliCk");
					$link->title = "<:=Change_Password=:>";
					$link->hrefAJAX = Request::GetRoot()."/User/Registration/";
					$link->href = Request::$url;
					$link->isTraditionalHref = false;
					$link->refreshElementId = "userFields";
					$link->class = "ajaxLink";
					$link->applyScripts = true;
					$link->getParamValues = true;
					$link->params = array("password_field" => "", "second_login" => "", "password_new_field" => "", "password_confirm_field" => "", "sent" => "");

					$this->blocksMarkup .= "</table><div style='padding-left: 10px;'>".$link->OpenTag()."<:=Change_Password=:>".$link->CloseTag()."</div>";
				}

			}
			elseif($this->page->isValidPost && $this->isValidForm)
			{
				$this->users->SaveUser();
				$this->blocksMarkup .= "<div style='padding: 15px'><:=Update_Password_Succeed=:></div>";
			}
			
		}
		
		private function RenderLost ()
		{
			$this->page->setTitle($this->GetLocalValue('Login_Lost'));
			
			$sent = (float)$_POST['sent'];

			if(!$this->page->isValidPost || $this->page->isValidPost && (Session::GetUserId() == 0) && !$this->ValidateLostForm())
			{
				if(!$this->page->isAjaxRequest)
					$this->blocksMarkup .= "<div style='padding: 15px;'><div class='row'><:=Lost_Login_Title=:>:</div><div id='userFields'><input type='hidden' name='sent' id='sent' value='1'>";
				
				$formTag = new Form();

				$this->blocksMarkup .= "<div class='row' ><div class='title' style='width: 150px;'><:=Login=:></div><div><input type='text' name='lost_login' id='lost_login' value='{$this->users->login}'>".$formTag->RenderAsterisks($this->fields_array["lost_login"])."</div></div></div>";

				if(!$this->page->isAjaxRequest)
				{
					$link = new Anchor();
					$link->SCode = sha1("pRocEsSTheLoGinForM");
					$link->title = "<:=Lost_Login=:>";
					$link->hrefAJAX = Request::$url;
					$link->href = Request::$url;
					$link->isTraditionalHref = false;
					$link->refreshElementId = "userFields";
					$link->applyScripts = true;
					$link->getParamValues = true;
					$link->class = "ajaxLink";
					$link->params = array( "sent" => "", "lost_login" => "");

					$this->blocksMarkup .= "<div class='row' >".$link->OpenTag()."<:=Lost_Login=:>".$link->CloseTag()."</div></div>";
					$this->page->commonScripts = $link->appendClientScript;	
				}
			}
			elseif($this->page->isValidPost && $this->isValidForm)
			{
				if(strlen($this->users->login))
				{
					$this->users->SetConfirmationTime();
					$this->users->SaveUser();
				}

				$this->RegistrationLetter('office@myxata.com', $this->users, 1);

				$this->blocksMarkup .= "<div style='width: 600px; font-size: 17px;'><:=Login_Lost_Successfully_Approved=:></div>";
			}
		}
		
		public function RenderUploadBannerButton()
		{
			$album = new BlockAlbum();
			$album->page = $this->page;
			$album->BlockMode = RENDER_BANNERS;
			
			$res = $album->RenderBannersList(true, 12); //$album->RenderUploadButton(true, 12);//RenderUploadButton(true, 12);
			$this->blocks['UPLOAD_CONTROL'] = $album->blocks['UPLOAD_CONTROL'];
			
			return $res;
		}

		private function RenderProfileFields()
		{
		
			if($this->page->isValidPost && Request::GetSCode() == sha1("mAkeThEpReSeArcHreQuest"))
			{
				$companies = new Companies();

				$result = $companies->LoadCompanies(0, 0, StaticDatabase::CleanupTheField($_POST['companiesInput']));

				while($row = mysql_fetch_array($result))
				{
					$this->blocksMarkup .= "<div onClick='companyClick(\"".Security::EncodeUrlData($row['COMPANIES_ID'])."\", \"".addslashes(StaticDatabase::CleanupTheField($row['NAME']))."\");'>".$row['NAME']."</div>";
				}
			}
			elseif(!$this->page->isValidPost || $this->page->isValidPost && Request::GetSCode() == sha1("SavEpRofIleFilE") && (int)$_POST['sentBack'] == 1 && !$this->ValidateForm()) 
			{
				$formTag = new Form();

				$this->page->SetTitle(Session::GetUserLogin());
			
				$blockSearch = new BlockSearch ();
				$blockSearch->page = $this->page;
				$blockSearch->BlockMode = RENDER_COMPANIES_SEARCH_INPUT;
				$blockSearch->inputId = "companiesInput";
				$blockSearch->inputText = $this->users->companyName;
				$blockSearch->BlockInit();

				$this->blocks['BLOCK_SEARCH'] = $blockSearch;

				if(!$this->isAjaxRequest)
				{
					$this->page->commonScripts .= "</script><script>".BlockSearch::InitScripts(RENDER_COMPANIES_SEARCH_INPUT)."</script>";

					$this->page->commonScripts .= "<script>function companyClick(id, name) { $('div_search').style.display = 'none'; $('companiesId').value = id; Objects.Search.isHalted = true; $('companiesInput').value = name; }</script>";
				}

				//$this->blocksMarkup .= $this->RenderUploadBannerButton();  // For the banners uploading in the future
				if(!$this->page->isAjaxRequest)
					$this->blocksMarkup .= "<div id='userFields' style='margin-bottom:3px'>";
					
				$this->blocksMarkup .= "<input type='hidden' name='sentBack' id='sentBack' value='1'>";

				$this->blocksMarkup .= "<div class='row'><div class='fld'><:=LOGIN_FIELD=:></div><div><input readonly='readonly' type='text' name='login_field' id='login_field' value='".Session::GetUserLogin()."'></div></div>";
				$this->blocksMarkup .= "<div class='row'><div class='fld'><:=Old_Password=:></div><div><nobr><input type='password' class='-metrika-nokeys' id='password_field' name='password_field' title='<:=Old_Password=:>' />".$formTag->RenderAsterisks($this->fields_array["login"])."</nobr></div></div>";
				$this->blocksMarkup .= "<div class='row'><div class='fld'><:=PUBLIC_EMAIL=:></div><div><input type='text' id='public_email_field' name='public_email_field' value='{$this->users->public_email}'>".$formTag->RenderAsterisks($this->fieldsArray['public_email_field'])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div class='fld'><:=PUBLIC_ADDRESS=:></div><div><input type='text' id='public_address' name='public_address' value='{$this->users->public_address}'>".$formTag->RenderAsterisks($this->fields_array["password_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div class='fld'><:=PHONE_NUMBER=:></div><div><input type='text' id='phone_field' name='phone_field' value='{$this->users->phone}'>".$formTag->RenderAsterisks($this->fields_array["password_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div class='fld'><:=PHONE2_NUMBER=:></div><div><input type='text' id='phone2_field' name='phone2_field' value='{$this->users->phone2}'>".$formTag->RenderAsterisks($this->fields_array["password_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row'><div class='fld'><:=COMPANY=:></div><div><input type='hidden' id='companiesId' name='companiesId' value=''><:=BLOCK_SEARCH=:>".$formTag->RenderAsterisks($this->fields_array["password_field"])."</div></div>";
				$this->blocksMarkup .= "<div class='row' style='height: 100px;'><div class='fld'><:=DESCRIPTION=:></div><div><textarea rows='5' cols='30' id='description_field' name='description_field'>{$this->users->description}</textarea></div></div>";

				if(!$this->page->isAjaxRequest)
					$this->blocksMarkup .= "</div>";

				if(!Request::$IsAJAX)
				{
					$link = new Anchor();
					$link->SCode = sha1("SavEpRofIleFilE");
					$link->title = "<:=Save_Profile=:>";
					$link->hrefAJAX = Request::$url;
					$link->href = "/User/";
					$link->isTraditionalHref = false;
					$link->refreshElementId = "userFields";
					$link->applyScripts = false;
					$link->getParamValues = true;
					$link->class = "ajaxLink";
					$link->params = array("public_email_field" => "", "login_field" => "", "password_field" => "", "companiesId" => "", "sentBack" => "", "public_address" => "", "phone_field" => "", "phone2_field" => "", "description_field" => "");

					$this->blocksMarkup .= "<div style='padding-left:390px; padding-bottom: 20px;'>".$link->OpenTag()."<:=Save_Profile=:>".$link->CloseTag()."</div>";
					$this->page->commonScripts .= "<script>".$link->appendClientScript."</script>";
				}

				if($this->page->isValidPost && Request::GetSCode() == sha1("SelEcTComPanY"))
				{
					$this->blocksAjaxMarkup = "<:=BLOCK_SEARCH=:>";
				}
				elseif($this->page->isValidPost && Request::GetSCode() == sha1("SelEcTComPanY"))
				{
					$this->blocksAjaxMarkup = $this->RenderSuppliersList();
				}
			}
			elseif($this->page->isValidPost && Request::GetSCode() == sha1("SavEpRofIleFilE") && (int)$_POST['sentBack'] == 1)
			{
				$this->users->description = substr(StaticDatabase::CleanupTheField($_POST['description_field']), 0, 500);
				$this->users->ip_current = Request::$ip;
				if($this->users->SaveUser((float)$this->users->usersId) > 0)
				{
					$this->blocksAjaxMarkup .= "<:=Save_OK=:>";
				}
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
			
		private function RenderLoginBubble()
		{
			$this->RenderLogin(true);
		}
		
		public static function InitScripts($mode = ALL, $url = "/")
		{
			$result = "";
			if($mode == ALL)
			{
				
			}
			
			$title = GlobalLocals::GetStaticGlobalValue("Login_Title");
			
			if($mode == LOGIN_BUBBLE || $mode == ALL)
			{ 

				$result .= ' 
					function LoginForm(action)
					{
						var parameters = { 	
							__SVar  			: Objects.Security.secureServerVar, 
							__SCode 			: "LoginForm", 
							__ClientVar 		: Objects.Security.createSecureVar(),
							IS_AJAX 			: "TRUE"
							}
							
						if(action == "logout")
						{
							parameters["__SCode"] = "'.sha1("pRocEsSTheLogOut").'";
						}
						else
						{
							parameters["__SCode"] = "'.sha1("pRocEsSTheLoGinForM").'";
						}

						var rq = new Ajax.Request("'.$url.'", { parameters : parameters, 

						onCreate: function () { 
							if(action != "logout")
							{
								Objects.BubbleDiv.height = 150;
								Objects.BubbleDiv.width = 280;
								Objects.BubbleDiv.title = "'.$title.'";
								Objects.BubbleDiv.position = "midtop";
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
									//if(mode == 1)
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
			}
			
			return $result;
		}

		private function RenderLogin($ajaxLogin = false)
		{
			$sent = (float)$_POST['sent'];

			if( !$this->page->isValidPost && Session::GetUserId() == 0 || 
				$this->page->isValidPost  && 
				( !$this->ValidateForm() && $sent || !$this->isValidForm && Request::GetSCode() == sha1("pRocEsSTheLoGinForM") )  
				) 
			{
				$formTag = new Form();		

				if(!$this->page->isAjaxRequest)
				{
					$this->blocksMarkup .= $formTag->RenderTop();
				
					$hdn = new Hidden();
					$hdn->SetName("__SCode");
					$hdn->SetValue(sha1("pRocEsSTheLoGinForM"));
	
					$this->blocksMarkup .= $hdn->OpenTag();
				}
	
				$this->blocksMarkup .= "<input type='hidden' name='sent' id='sent' value='1' />";
				if(!$this->page->isAjaxRequest || !$sent)
					$this->blocksMarkup .= "<table id='loginFields' style='margin:5px;'>";
				
				$this->blocksMarkup .= "<tr><td><:=Login=:></td><td><nobr><input type='text' id='login_field' name='login_field' title='<:=Login=:>' value='".$this->valuesArray["login_field"]."' />&nbsp;".
																									$formTag->RenderAsterisks($this->fields_array["login"])."</nobr></td></tr>";
				$this->blocksMarkup .= "<tr><td><:=Password=:></td><td><input type='password' id='password_field' class='-metrika-nokeys'  name='password_field' value='' title='<:=Password=:>' /></td></tr>";
				
				if(!$sent)
				{
					if($this->page->isAjaxRequest)
					{
						$link = new Anchor();
						$link->SCode = sha1("pRocEsSTheLoGinForM");
						$link->title = "<:=Save_Profile=:>";
						$link->hrefAJAX = Request::$url;
						$link->isTraditionalHref = false;
						$link->refreshElementId = "loginFields";
						$link->applyScripts = true;
						$link->getParamValues = true;
						$link->class = "ajaxLink";
						$link->params = array("login_field" => "", "password_field" => "", "sent" => "");

						$this->blocksMarkup .= "</table><div style='text-align:right; margin-right:70px;'>".$link->OpenTag()."<:=Login_Btn=:>".$link->CloseTag()."</div>";
						$this->page->commonScripts = $link->appendClientScript;
					}
					else
					{
						$this->blocksMarkup .= "<tr><td>".$formTag->RenderSubmitButton("<:=Login_Btn=:>")."</td></tr></table>";
					}
				}
									
				if(!$this->page->isAjaxRequest)
					$this->blocksMarkup .= $formTag->RenderBottom();
			}
			elseif($this->page->isValidPost && $this->isValidForm && Request::GetSCode() == sha1("pRocEsSTheLoGinForM")) 
			{
				// 믰鲲󢠷 㡫Ԥ. 㲲ᮮ㬥 봪򠠢Ԥ뱨򲿠񥱳ߍ
				Session::Authenticate($this->users->login, $this->users->usersId);
				
				$this->page->commonScripts .= "Objects.BubbleDiv.hide(); window.document.location.href='/';";
				
				/*if(!$ajaxLogin)
					$this->blocksMarkup .= "Hello, <b>".$this->users->login."</b>! You are in. <a href='build'>Log out</a>";
				else
					$this->page->commonScripts .= "<script>alert('fdfdfd');</script>";
				*/
			}
			elseif($this->page->isValidPost && Request::GetSCode() == sha1("pRocEsSTheLogOut"))
			{
				Session::LogOut();
				$this->page->commonScripts .= "<script>window.document.location.href='/';</script>";
			}
			elseif(Session::GetUserId() > 0)
			{
				$this->blocksMarkup .= "Hello, <b>".Session::GetUserLogin()."</b>! You are in. <a href='build' onClick='LoginForm(\"logout\"); return false;'>Log out</a>";
			}
			else 
			{
				trigger_error("Unknown Error", E_USER_ERROR);
			}
		}
		
		public function BlockInit()
		{	
			$this->SwitchStep();
		}
	}

?>